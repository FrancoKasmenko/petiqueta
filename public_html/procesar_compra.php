<?php
session_start();
require "../app/core/config.php";
require '../vendor/autoload.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($_SESSION['carrito'])) {
    header('Location: carrito');
    exit;
}

$metodo_pago = $_POST['metodo_pago'] ?? 'mercadopago';

if (
    !isset($_POST['nombre'], $_POST['apellido'], $_POST['email'], $_POST['metodo_entrega'], $_POST['telefono'])
    || trim($_POST['nombre']) === '' || trim($_POST['apellido']) === ''
    || ($_POST['metodo_entrega'] === 'envio' && (!isset($_POST['direccion'], $_POST['ciudad'], $_POST['region']) || trim($_POST['direccion']) === '' || trim($_POST['ciudad']) === '' || trim($_POST['region']) === ''))
) {
    die("Faltan datos obligatorios para procesar la orden.");
}

$carrito = $_SESSION['carrito'];
$costo_envio = isset($_POST['costo_envio']) ? floatval($_POST['costo_envio']) : 0;

// Calcula subtotal según el carrito
function calcularSubtotal($carrito)
{
    $subtotal = 0;
    foreach ($carrito as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    return $subtotal;
}

$subtotal = calcularSubtotal($carrito);
$total = $subtotal + $costo_envio;

// Recoge datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$metodo_entrega = $_POST['metodo_entrega'];
$telefono = trim($_POST['telefono']);
$direccion_envio = $metodo_entrega === 'envio' ? trim($_POST['direccion']) : null;
$ciudad_envio = $metodo_entrega === 'envio' ? trim($_POST['ciudad']) : null;
$region_envio = $metodo_entrega === 'envio' ? trim($_POST['region']) : null;
$comentarios_adicionales = trim($_POST['comentarios_adicionales'] ?? '');
$fact_nombre = $_POST['fact_nombre'] ?? null;
$fact_apellido = $_POST['fact_apellido'] ?? null;
$fact_direccion = $_POST['fact_direccion'] ?? null;
$fact_codigo_postal = $_POST['fact_codigo_postal'] ?? null;
$fact_ciudad = $_POST['fact_ciudad'] ?? null;
$fact_region = $_POST['fact_region'] ?? null;

// Datos empresa para factura con RUT
$rut = trim($_POST['rut'] ?? '');
$razon_social = trim($_POST['razon_social'] ?? '');
$direccion_fiscal = trim($_POST['direccion_fiscal'] ?? '');

try {
    $pdo->beginTransaction();

    // Usuario logueado o no
    if (!isset($_SESSION['user_id'])) {
        $stmtBuscar = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmtBuscar->execute([$email]);
        $usuarioExistente = $stmtBuscar->fetchColumn();

        if ($usuarioExistente) {
            $id_usuario = $usuarioExistente;
        } else {
            $stmtCrear = $pdo->prepare("INSERT INTO usuario (nombre, apellido, email, telefono) VALUES (?, ?, ?, ?)");
            $stmtCrear->execute([$nombre, $apellido, $email, $telefono]);
            $id_usuario = $pdo->lastInsertId();
        }
    } else {
        $id_usuario = $_SESSION['user_id'];
    }

    // Manejar empresa con RUT si se indicó
    $id_empresa = null;
    if (!empty($rut)) {
        $stmtEmpresa = $pdo->prepare("SELECT id_empresa FROM empresa WHERE rut = ?");
        $stmtEmpresa->execute([$rut]);
        $id_empresa = $stmtEmpresa->fetchColumn();

        if (!$id_empresa) {
            $stmtInsertEmpresa = $pdo->prepare("INSERT INTO empresa (rut, razon_social, direccion_fiscal) VALUES (?, ?, ?)");
            $stmtInsertEmpresa->execute([$rut, $razon_social, $direccion_fiscal]);
            $id_empresa = $pdo->lastInsertId();
        }
    }

    // Insertar orden con id_empresa
    $stmt = $pdo->prepare("
        INSERT INTO orden (
            id_usuario, total, estado, fecha_creacion,
            email, metodo_pago, metodo_entrega,
            direccion_envio, ciudad_envio, region_envio, telefono,
            fact_nombre, fact_apellido, fact_direccion, fact_codigo_postal, fact_ciudad, fact_region,
            comentarios_adicionales,
            id_empresa
        ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $estado_inicial = 'pendiente';

    $stmt->execute([
        $id_usuario,               
        $total,                   
        $estado_inicial,          
        $email,                   
        $metodo_pago,             
        $metodo_entrega,          
        $direccion_envio,         
        $ciudad_envio,            
        $region_envio,            
        $telefono,               
        $fact_nombre,             
        $fact_apellido,           
        $fact_direccion,          
        $fact_codigo_postal,      
        $fact_ciudad,   
        $fact_region,             
        $comentarios_adicionales, 
        $id_empresa               
    ]);

    $id_orden = $pdo->lastInsertId();

    // Insertar detalles orden
    $stmt_detalle = $pdo->prepare("INSERT INTO orden_detalle (id_orden, id_producto, cantidad, precio_unitario, tamanio, forma, colores) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($carrito as $item) {
        $id_producto = $item['id_producto'] ?? 0;
        $cantidad = $item['cantidad'];
        $precio_unitario = $item['precio'];

        if (isset($item['colores']) && is_array($item['colores'])) {
            $colores_str = implode(", ", $item['colores']);
        } elseif (isset($item['color'])) {
            $colores_str = $item['color'];
        } else {
            $colores_str = '';
        }

        $tamanio = $item['tamanio'] ?? '';
        $forma = $item['forma'] ?? '';

        $stmt_detalle->execute([$id_orden, $id_producto, $cantidad, $precio_unitario, $tamanio, $forma, $colores_str]);
    }

    if ($costo_envio > 0) {
        $id_producto_envio = 2; 
        $stmt_detalle->execute([$id_orden, $id_producto_envio, 1, $costo_envio, '', '', '']);
    }

    $pdo->commit();

    unset($_SESSION['carrito']);

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = '';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('', 'Petiqueta');
        $mail->addAddress($email, $nombre . ' ' . $apellido);
        $mail->addReplyTo('', 'Petiqueta');
        $mail->isHTML(true);
        $mail->Subject = "¡Compra realizada con exito en Petiqueta!";

        // Detalles de la compra para el email
        $detalleProductos = '';
        foreach ($carrito as $prod) {
            $detalleProductos .= "<li><b>{$prod['nombre']}</b> x{$prod['cantidad']} - US$" . number_format($prod['precio'],2) . "</li>";
        }
        if ($costo_envio > 0) {
            $detalleProductos .= "<li><b>Envío</b> - US$" . number_format($costo_envio,2) . "</li>";
        }

        $mail->Body = "
            <h2 style='color:#7a5c39;margin-top:0'>¡Gracias por tu compra en Petiqueta!</h2>
            <p>Hola <b>{$nombre}</b>, tu pedido fue recibido correctamente. <br>En breve comenzaremos a prepararlo.</p>
            <p><b>Resumen de tu pedido:</b></p>
            <ul style='font-size:16px;line-height:1.5;padding-left:16px;'>$detalleProductos</ul>
            <p><b>Total pagado:</b> US$" . number_format($total,2) . "</p>
            <p>Estado actual: <b>Pendiente de pago</b></p>
            <p>Te enviaremos una confirmación adicional cuando se acredite el pago.<br>Por cualquier consulta escribinos a <a href='mailto:contacto@petiqueta.uy'>contacto@petiqueta.uy</a>.</p>
            <hr>
            <p style='color:#888;font-size:13px;'>Petiqueta - ¡Gracias por confiar en nosotros!</p>
        ";
        $mail->AltBody = "Gracias por tu compra en Petiqueta. Tu pedido fue recibido correctamente. Total: US$".number_format($total,2);
        $mail->send();
    } catch (Exception $e) {
    }

    // Lógica de pagos:
    if ($metodo_pago === 'mercadopago') {
        MercadoPagoConfig::setAccessToken('token');

        $items_mp = [];
        foreach ($carrito as $prod) {
            $items_mp[] = [
                "id" => uniqid(),
                "title" => $prod['nombre'],
                "quantity" => (int)$prod['cantidad'],
                "unit_price" => (float)$prod['precio'],
            ];
        }

        if ($costo_envio > 0) {
            $items_mp[] = [
                "id" => "envio",
                "title" => "Costo de envío",
                "quantity" => 1,
                "unit_price" => $costo_envio,
            ];
        }

        $client = new PreferenceClient();

        try {
            $preference = $client->create([
                "items" => $items_mp,
                "statement_descriptor" => "Petiqueta",
                "external_reference" => $id_orden,
                "back_urls" => [
                    "success" => "https://petiqueta.uy/compra_exitosa",
                    "failure" => "https://petiqueta.uy/compra_fallida",
                    "pending" => "https://petiqueta.uy/compra_pendiente"
                ],
                "auto_return" => "approved",
            ]);
        } catch (Exception $e) {
            die("Error al crear la preferencia: " . $e->getMessage());
        }

        header('Location: ' . $preference->init_point);
        exit;
    } elseif ($metodo_pago === 'transferencia') {
        header('Location: transferencia_instrucciones?id_orden=' . $id_orden);
        exit;
    }
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error procesando la orden: " . $e->getMessage());
}
