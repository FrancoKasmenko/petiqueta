<?php

session_start();
require 'config.php';
require 'auth.php';
requireAdmin();

$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;
$errors = [];
$success = null;

// Función para generar código aleatorio de 6 caracteres
function generarCodigoRandom($length = 6)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < $length; $i++) {
        $codigo .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $codigo;
}

// Acción: Generar PDF con códigos QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'generar_pdf') {
    $cantidad = (int)($_POST['cantidad_imprimir'] ?? 0);
    $tamanos_disponibles = [
        'chico' => 1.6,
        'mediano' => 2.0,
        'grande' => 3.0,
        'extra_grande' => 3.5
    ];
    $tamano_seleccionado = $_POST['tamano_etiqueta'] ?? 'mediano';
    $diametro_cm = $tamanos_disponibles[$tamano_seleccionado] ?? 2.5;
    $margen_interno = 0;
    $lado_qr = $diametro_cm;

    if ($cantidad < 1 || $cantidad > 1000) {
        $errors[] = "Cantidad debe ser entre 1 y 1000.";
    } else {
        try {
            $pdo->beginTransaction();

            // Consultar códigos disponibles con lock para evitar repetición
            $stmt = $pdo->prepare("SELECT id, code FROM mascotag_codes WHERE status = 'available' ORDER BY created_at ASC LIMIT ?");
            $stmt->bindValue(1, $cantidad, PDO::PARAM_INT);
            $stmt->execute();
            $codigosParaPDF = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($codigosParaPDF)) {
                $errors[] = "No hay códigos disponibles para imprimir.";
                $pdo->rollBack();
            } else {
                require_once('../../vendor/fpdf/fpdf.php');
                require_once('../../vendor/phpqrcode/qrlib.php');

                class PDF extends FPDF
                {
                    // Dibuja círculo
                    function Circle($x, $y, $r, $style = 'D')
                    {
                        $this->Ellipse($x, $y, $r, $r, $style);
                    }
                    // Dibuja elipse
                    function Ellipse($x, $y, $rx, $ry, $style = 'D')
                    {
                        if ($style == 'F')
                            $op = 'f';
                        elseif ($style == 'FD' || $style == 'DF')
                            $op = 'B';
                        else
                            $op = 'S';

                        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
                        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;

                        $k = $this->k;
                        $h = $this->h;

                        $this->_out(sprintf('%.2F %.2F m', ($x + $rx) * $k, ($h - ($y)) * $k));
                        $this->_arc($x + $rx, $y - $ly, $x + $lx, $y - $ry, $x, $y - $ry);
                        $this->_arc($x - $lx, $y - $ry, $x - $rx, $y - $ly, $x - $rx, $y);
                        $this->_arc($x - $rx, $y + $ly, $x - $lx, $y + $ry, $x, $y + $ry);
                        $this->_arc($x + $lx, $y + $ry, $x + $rx, $y + $ly, $x + $rx, $y);
                        $this->_out($op);
                    }
                    function _arc($x1, $y1, $x2, $y2, $x3, $y3)
                    {
                        $k = $this->k;
                        $h = $this->h;
                        $this->_out(sprintf(
                            '%.2F %.2F %.2F %.2F %.2F %.2F c',
                            $x1 * $k,
                            ($h - $y1) * $k,
                            $x2 * $k,
                            ($h - $y2) * $k,
                            $x3 * $k,
                            ($h - $y3) * $k
                        ));
                    }
                }

                $pdf = new PDF('P', 'cm', 'A4');
                $pdf->AddPage();

                switch ($tamano_seleccionado) {
                    case "chico":
                        $qr_por_fila = 6;
                        $espacio_horizontal = 3.0;
                        $espacio_vertical = 3.5;
                        break;
                    case "mediano":
                        $qr_por_fila = 6;
                        $espacio_horizontal = 3.0;
                        $espacio_vertical = 3.5;
                        break;
                    case "grande":
                        $qr_por_fila = 5;
                        $espacio_horizontal = 4.0;
                        $espacio_vertical = 4.5;
                        break;
                    case "extra_grande":
                        $qr_por_fila = 4;
                        $espacio_horizontal = 4.0;
                        $espacio_vertical = 4.5;
                        break;
                    default:
                        $qr_por_fila = 4;
                        $espacio_horizontal = 4.0;
                        $espacio_vertical = 4.5;
                        break;
                }
                $inicio_x = 1;
                $inicio_y = 1;

                $contador = 0;

                foreach ($codigosParaPDF as $item) {
    $codigo = $item['code'];
    $col = $contador % $qr_por_fila;
    $row = floor($contador / $qr_por_fila);

    $x = $inicio_x + $col * $espacio_horizontal;
    $y = $inicio_y + $row * $espacio_vertical;

    // ==== AJUSTE DE DIÁMETRO Y QR SEGÚN TAMAÑO ====
    if ($tamano_seleccionado === "chico") {
        $diametro_cm = 1.6;   // Círculo exactamente de 1.6 cm
        $lado_qr = 1.3;       // QR más pequeño para dejar margen
    } elseif ($tamano_seleccionado === "mediano") {
        $diametro_cm = 2;
        $lado_qr = 1.65;       // Margen visual similar (2 mm por lado)
    } elseif ($tamano_seleccionado === "grande") {
        $diametro_cm = 3.0;
        $lado_qr = 2.5;
    } elseif ($tamano_seleccionado === "extra_grande") {
        $diametro_cm = 3.5;
        $lado_qr = 2.9;
    } else {
        // Por defecto mediano
        $diametro_cm = 2.5;
        $lado_qr = 2.1;
    }
    // ===============================

    // Generar QR temporal
    $filename = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    QRcode::png("https://petiqueta.uy/mascota?codigo=$codigo", $filename, QR_ECLEVEL_L, 3, 2);

    // Centrar QR dentro del círculo
    $radio = $diametro_cm / 2;
    $x_qr = $x + $radio - $lado_qr / 2;
    $y_qr = $y + $radio - $lado_qr / 2;

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.05);
    $pdf->Circle($x + $radio, $y + $radio, $radio); // Dibuja el círculo
    $pdf->Image($filename, $x_qr, $y_qr, $lado_qr, $lado_qr); // Inserta el QR

    // Texto debajo (puede ajustar el font size si lo ves grande)
    $pdf->SetXY($x, $y + $diametro_cm + 0.05);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($espacio_horizontal, 0.5, $codigo, 0, 0, 'C');

    unlink($filename);

    $contador++;

    // Salto de página si hace falta
    if ($row >= 10 - 1) {
        $pdf->AddPage();
        $contador = 0;
    }
}


                // Actualizar estado a "printed" para los códigos usados
                $ids_usados = array_column($codigosParaPDF, 'id');
                if ($ids_usados) {
                    $in_placeholders = implode(',', array_fill(0, count($ids_usados), '?'));
                    $stmt_update = $pdo->prepare("UPDATE mascotag_codes SET status = 'available' WHERE id IN ($in_placeholders)");
                    $stmt_update->execute($ids_usados);
                }

                $pdo->commit();

                $pdf->Output();
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error generando PDF: " . $e->getMessage();
        }
    }
}

// Agregar códigos masivos
if ($action === 'bulk_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    if ($cantidad < 1 || $cantidad > 1000) {
        $errors[] = "Cantidad debe ser entre 1 y 1000.";
    } else {
        $inserted = 0;
        $stmt = $pdo->prepare("INSERT INTO mascotag_codes (code, status) VALUES (?, 'available')");
        for ($i = 0; $i < $cantidad; $i++) {
            $nuevoCodigo = generarCodigoRandom();
            try {
                $stmt->execute([$nuevoCodigo]);
                $inserted++;
            } catch (PDOException $e) {
                // En caso de código repetido, generar otro
                $i--;
            }
        }
        $success = "Se generaron $inserted códigos nuevos.";
    }
}

// Agregar código personalizado
if ($action === 'add_custom' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    if (!$codigo || strlen($codigo) !== 6) {
        $errors[] = "El código debe tener exactamente 6 caracteres.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO mascotag_codes (code, status) VALUES (?, 'available')");
            $stmt->execute([$codigo]);
            $success = "Código $codigo agregado correctamente.";
        } catch (PDOException $e) {
            $errors[] = "Error al agregar el código: código duplicado o inválido.";
        }
    }
}

// Eliminar código
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM mascotag_codes WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Código eliminado correctamente.";
}

// Listar códigos (ordenados por estado ASC para mejor visual)
$stmt = $pdo->query("SELECT * FROM mascotag_codes ORDER BY status ASC, created_at DESC");
$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Backoffice Petiqueta Codes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="container pt-4">
        <h1>Petiqueta Codes</h1>

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

        <div class="mb-4">
            <h4>Agregar códigos masivos</h4>
            <form method="POST" action="mascotag_codes.php?action=bulk_add" class="d-flex gap-2 align-items-center">
                <input type="number" name="cantidad" class="form-control w-25" min="1" max="1000" placeholder="Cantidad" required />
                <button type="submit" class="btn btn-primary">Generar</button>
            </form>
        </div>

        <div class="mb-4">
            <h4>Agregar código personalizado</h4>
            <form method="POST" action="mascotag_codes.php?action=add_custom" class="d-flex gap-2 align-items-center">
                <input type="text" name="codigo" class="form-control w-25 text-uppercase" maxlength="6" placeholder="Código 6 caracteres" required />
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>

        <div class="mb-4">
            <h4>Generar PDF con códigos QR</h4>
            <form method="POST" action="mascotag_codes.php?action=generar_pdf" class="d-flex gap-2 align-items-center">
                <input type="number" name="cantidad_imprimir" class="form-control w-25" min="1" max="1000" placeholder="Cantidad códigos a imprimir" required />

                <select name="tamano_etiqueta" class="form-select w-25" required>
                    <option value="chico">Chico (2 cm)</option>
                    <option value="mediano" selected>Mediano (2.5 cm)</option>
                    <option value="grande">Grande (3 cm)</option>
                    <option value="extra_grande">Extra Grande (3.5 cm)</option>
                </select>

                <button type="submit" class="btn btn-primary">Generar PDF con QR</button>
            </form>
        </div>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Asignado a usuario</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Usado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($codes as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['code']) ?></td>
                        <td><?= $c['assigned_to_user'] ?? 'No asignado' ?></td>
                        <td><?= ucfirst($c['status']) ?></td>
                        <td><?= $c['created_at'] ?></td>
                        <td><?= $c['used_at'] ?? '-' ?></td>
                        <td>
                            <a href="mascotag_codes.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar código?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($codes)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay códigos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
