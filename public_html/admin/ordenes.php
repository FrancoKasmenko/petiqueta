<?php
session_start();
require 'config.php';
require 'auth.php';

// PHPMailer
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función para enviar mails
function enviarCorreo($to, $subject, $body, $bcc = null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contacto@petiqueta.uy';
        $mail->Password = 'Fiona1989_';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('contacto@petiqueta.uy', 'Petiqueta');
        $mail->addAddress($to);
        if ($bcc) $mail->addBCC($bcc);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Podés loguear el error si querés
        return false;
    }
}

$action = $_GET['action'] ?? null;
$id_orden = $_GET['id'] ?? null;
$errors = [];
$success = null;

$estados_validos = ['pendiente', 'pagado', 'enviado', 'cancelado', 'pendiente_confirmacion'];

// Cambiar estado orden
if ($action === 'change_status' && $id_orden && isset($_GET['status'])) {
    $nuevo_estado = $_GET['status'];
    if (in_array($nuevo_estado, $estados_validos)) {
        $stmt = $pdo->prepare("UPDATE orden SET estado = ?, fecha_actualizacion = NOW() WHERE id_orden = ?");
        $stmt->execute([$nuevo_estado, $id_orden]);
        $success = "Estado actualizado a '" . htmlspecialchars($nuevo_estado) . "'.";

        // Traer datos orden + cliente
        $stmtOrden = $pdo->prepare("SELECT o.*, COALESCE(u.email, o.email) AS email_cliente, COALESCE(u.nombre, o.fact_nombre, 'Cliente') AS nombre_cliente, COALESCE(u.apellido, o.fact_apellido, '') AS apellido_cliente FROM orden o LEFT JOIN usuario u ON o.id_usuario = u.id_usuario WHERE o.id_orden = ?");
        $stmtOrden->execute([$id_orden]);
        $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC);

        $stmtDetalle = $pdo->prepare("SELECT od.*, p.nombre FROM orden_detalle od LEFT JOIN producto p ON od.id_producto = p.id_producto WHERE od.id_orden = ?");
        $stmtDetalle->execute([$id_orden]);
        $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        // ----- MAILS según el estado -----
        if ($nuevo_estado === 'pagado') {
            // Mail al cliente
            $bodyCliente = "<h2>¡Gracias por tu compra en Petiqueta!</h2>
            <p>Hola <b>{$orden['nombre_cliente']} {$orden['apellido_cliente']}</b>,<br>
            Tu pago fue confirmado. En breve comenzaremos a preparar tu pedido.<br><br>
            <b>Detalle de tu compra:</b></p>
            <ul>";
            foreach ($detalle as $prod) {
                $bodyCliente .= "<li>{$prod['nombre']} x{$prod['cantidad']} ({$prod['tamanio']} - {$prod['forma']})</li>";
            }
            $bodyCliente .= "</ul>
            <p>Total: <b>$" . number_format($orden['total'], 2) . " UYU</b></p>
            <p>Te avisaremos cuando tu pedido sea enviado.<br><br>
            ¡Gracias por confiar en Petiqueta!</p>";
            enviarCorreo($orden['email_cliente'], 'Pago confirmado en Petiqueta', $bodyCliente);

            // Mail a administrador
            $bodyAdmin = "<h2>Nueva orden PAGADA en Petiqueta</h2>
            <p>Cliente: <b>{$orden['nombre_cliente']} {$orden['apellido_cliente']}</b><br>
            Email: {$orden['email_cliente']}<br>
            Teléfono: " . htmlspecialchars($orden['telefono']) . "<br>
            Monto: $" . number_format($orden['total'], 2) . " UYU</p>
            <b>Productos:</b><ul>";
            foreach ($detalle as $prod) {
                $bodyAdmin .= "<li>{$prod['nombre']} x{$prod['cantidad']} ({$prod['tamanio']} - {$prod['forma']})</li>";
            }
            $bodyAdmin .= "</ul>";
            enviarCorreo('fkasmenko@gmail.com', 'Nueva orden PAGADA en Petiqueta', $bodyAdmin);

        } elseif ($nuevo_estado === 'enviado') {
            // Mail al cliente
            $bodyEnvio = "<h2>¡Tu pedido fue enviado!</h2>
            <p>Hola <b>{$orden['nombre_cliente']} {$orden['apellido_cliente']}</b>,<br>
            Te avisamos que tu pedido ya fue despachado.<br>
            <b>Pronto estará en camino a la dirección indicada.</b></p>
            <p>¡Gracias por elegir Petiqueta!</p>";
            enviarCorreo($orden['email_cliente'], '¡Tu pedido fue enviado! 🚚', $bodyEnvio);
        }

    } else {
        $errors[] = "Estado inválido.";
    }
}

// Eliminar orden
if ($action === 'delete' && $id_orden) {
    $stmt = $pdo->prepare("DELETE FROM orden WHERE id_orden = ?");
    $stmt->execute([$id_orden]);
    $success = "Orden eliminada correctamente.";
}

// Ver detalle orden
$detalle = null;
if ($action === 'view' && $id_orden) {
    $stmtOrden = $pdo->prepare("
        SELECT o.*, 
           u.nombre, u.apellido, u.email AS user_email, u.telefono AS user_telefono
        FROM orden o
        LEFT JOIN usuario u ON o.id_usuario = u.id_usuario
        WHERE o.id_orden = ?
    ");
    $stmtOrden->execute([$id_orden]);
    $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
    SELECT od.*, p.nombre 
    FROM orden_detalle od
    LEFT JOIN producto p ON od.id_producto = p.id_producto
    WHERE od.id_orden = ?
    ");
    $stmt->execute([$id_orden]);
    $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Listar órdenes con nombre completo y email del cliente priorizando usuario registrado, sino datos de orden
$stmt = $pdo->query("
    SELECT o.*, 
        COALESCE(CONCAT(u.nombre, ' ', u.apellido), CONCAT(o.fact_nombre, ' ', o.fact_apellido)) AS cliente_nombre,
        COALESCE(u.email, o.email) AS cliente_email
    FROM orden o
    LEFT JOIN usuario u ON o.id_usuario = u.id_usuario
    ORDER BY o.fecha_creacion DESC
");
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Backoffice Órdenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="container pt-4">
        <h1>Órdenes</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($action === 'view' && $id_orden && $detalle): ?>

            <a href="ordenes.php" class="btn btn-secondary mb-3">Volver al listado</a>

            <h2>Detalle Orden #<?= $id_orden ?></h2>

            <div class="row g-4">
                <!-- Datos Usuario Registrado -->
                <div class="col-md-4">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">
                            <strong>Datos del Usuario Registrado</strong>
                        </div>
                        <div class="card-body">
                            <?php if ($orden['id_usuario']): ?>
                                <p><strong>Nombre:</strong> <?= htmlspecialchars($orden['nombre'] ?? '-') ?> <?= htmlspecialchars($orden['apellido'] ?? '-') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($orden['user_email'] ?? '-') ?></p>
                                <p><strong>Teléfono:</strong> <?= htmlspecialchars($orden['user_telefono'] ?? '-') ?></p>
                            <?php else: ?>
                                <p>Orden realizada sin usuario registrado.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Datos de Contacto -->
                <div class="col-md-4">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white">
                            <strong>Datos de Contacto</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Email:</strong> <?= htmlspecialchars($orden['email']) ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($orden['telefono']) ?></p>
                        </div>
                    </div>
                </div>
                <!-- Datos de Facturación -->
                <div class="col-md-4">
                    <div class="card border-info h-100">
                        <div class="card-header bg-info text-white">
                            <strong>Datos de Facturación</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($orden['fact_nombre']) ?></p>
                            <p><strong>Apellido:</strong> <?= htmlspecialchars($orden['fact_apellido']) ?></p>
                            <p><strong>Dirección:</strong> <?= htmlspecialchars($orden['fact_direccion']) ?></p>
                            <p><strong>Código postal:</strong> <?= htmlspecialchars($orden['fact_codigo_postal']) ?></p>
                            <p><strong>Ciudad:</strong> <?= htmlspecialchars($orden['fact_ciudad']) ?></p>
                            <p><strong>Región:</strong> <?= htmlspecialchars($orden['fact_region']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Datos Factura con RUT -->
            <?php if (!empty($orden['id_empresa'])):
                $stmtEmpresa = $pdo->prepare("SELECT * FROM empresa WHERE id_empresa = ?");
                $stmtEmpresa->execute([$orden['id_empresa']]);
                $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);
            ?>
                <div class="card border-warning mt-4">
                    <div class="card-header bg-warning text-dark">
                        <strong>Datos Factura con RUT</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>RUT:</strong> <?= htmlspecialchars($empresa['rut']) ?></p>
                        <p><strong>Razón Social:</strong> <?= htmlspecialchars($empresa['razon_social']) ?></p>
                        <p><strong>Dirección Fiscal:</strong> <?= htmlspecialchars($empresa['direccion_fiscal']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Otros datos generales -->
            <div class="row mt-4 g-3">
                <div class="col-md-6">
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <strong>Información de la Orden</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Método de Pago:</strong> <?= ucfirst(htmlspecialchars($orden['metodo_pago'])) ?></p>
                            <p><strong>Estado:</strong> <?= ucfirst(htmlspecialchars($orden['estado'])) ?></p>
                            <p><strong>Fecha creación:</strong> <?= $orden['fecha_creacion'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <strong>Entrega</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Método entrega:</strong> <?= ucfirst(htmlspecialchars($orden['metodo_entrega'])) ?></p>
                            <?php if ($orden['metodo_entrega'] === 'envio'): ?>
                                <p><strong>Dirección de envío:</strong> <?= htmlspecialchars($orden['direccion_envio']) ?>, <?= htmlspecialchars($orden['ciudad_envio']) ?>, <?= htmlspecialchars($orden['region_envio']) ?></p>
                            <?php else: ?>
                                <p><strong>Retira en tienda</strong></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comentarios adicionales -->
            <?php if (!empty($orden['comentarios_adicionales'])): ?>
                <div class="card border-light mt-4">
                    <div class="card-header bg-light text-dark">
                        <strong>Comentarios adicionales</strong>
                    </div>
                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($orden['comentarios_adicionales'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comprobante transferencia -->
            <?php if ($orden['metodo_pago'] === 'transferencia' && !empty($orden['comprobante_transferencia'])): ?>
                <div class="mt-4">
                    <p><strong>Comprobante de transferencia:</strong></p>
                    <a href="../https://petiqueta.uy/uploads/comprobantes/<?= urlencode($orden['comprobante_transferencia']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        Ver / Descargar
                    </a>
                </div>
            <?php endif; ?>

            <!-- Tabla detalle productos -->
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Tamaño</th>
                        <th>Forma</th>
                        <th>Colores</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td><?= htmlspecialchars($item['tamanio'] ?? 'No especificado') ?></td>
                            <td><?= htmlspecialchars($item['forma'] ?? 'No especificado') ?></td>
                            <td><?= htmlspecialchars($item['colores'] ?? 'No especificado') ?></td>
                            <td><?= $item['cantidad'] ?></td>
                            <td>$<?= number_format($item['precio_unitario'], 2) ?> UYU</td>
                            <td>$<?= number_format($item['cantidad'] * $item['precio_unitario'], 2) ?> UYU</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Estado</th>
                        <th>Fecha creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $o): ?>
                        <tr>
                            <td><?= $o['id_orden'] ?></td>
                            <td><?= htmlspecialchars($o['cliente_nombre'] ?: 'Anónimo') ?></td>
                            <td><?= htmlspecialchars($o['cliente_email']) ?></td>
                            <td>$<?= number_format($o['total'], 2) ?> UYU</td>
                            <td><?= ucfirst(htmlspecialchars($o['metodo_pago'] ?? '')) ?></td>
                            <?php
                            $estado = strtolower($o['estado']);
                            $badgeClass = 'bg-secondary';
                            if ($estado === 'pendiente') $badgeClass = 'bg-warning';
                            elseif ($estado === 'pagado') $badgeClass = 'bg-success';
                            elseif ($estado === 'enviado') $badgeClass = 'bg-info';
                            elseif ($estado === 'cancelado') $badgeClass = 'bg-danger';
                            elseif ($estado === 'pendiente_confirmacion') $badgeClass = 'bg-primary';
                            ?>
                            <td><span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($o['estado'])) ?></span></td>
                            <td><?= $o['fecha_creacion'] ?></td>
                            <td>
                                <a href="ordenes.php?action=view&id=<?= $o['id_orden'] ?>" class="btn btn-sm btn-info">Ver detalle</a>
                                <a href="ordenes.php?action=delete&id=<?= $o['id_orden'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta orden?');">Eliminar</a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Cambiar estado
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($estados_validos as $estado): ?>
                                            <li>
                                                <a class="dropdown-item" href="ordenes.php?action=change_status&id=<?= $o['id_orden'] ?>&status=<?= $estado ?>">
                                                    <?= ucfirst($estado) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ordenes)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay órdenes registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
