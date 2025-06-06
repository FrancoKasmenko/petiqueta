<?php
session_start();
require '../app/core/config.php';

define('COMPROBANTES_DIR', 'uploads/comprobantes/');

if (!is_dir(COMPROBANTES_DIR)) {
    mkdir(COMPROBANTES_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_orden'])) {
    die("Acceso inválido.");
}

$id_orden = $_POST['id_orden'];

// Validar que exista la orden
$stmt = $pdo->prepare("SELECT * FROM orden WHERE id_orden = ?");
$stmt->execute([$id_orden]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("Orden no encontrada.");
}

// Validar archivo subido
if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir el comprobante.");
}

$archivo = $_FILES['comprobante'];

// Validar tipo MIME (solo imagen o pdf)
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$tipoArchivo = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if (!in_array($tipoArchivo, $tiposPermitidos)) {
    die("Tipo de archivo no permitido. Solo imágenes o PDF.");
}

// Generar nombre único para archivo
$ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$nombreArchivo = 'comprobante_' . $id_orden . '_' . time() . '.' . $ext;
$rutaDestino = COMPROBANTES_DIR . $nombreArchivo;

// Mover archivo subido
if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    die("Error al guardar el comprobante.");
}

// Guardar ruta relativa en base de datos (puedes crear columna comprobante en orden o en tabla aparte)
// Aquí asumo que agregaste columna comprobante_transferencia en orden
$stmtUpd = $pdo->prepare("UPDATE orden SET comprobante_transferencia = ?, estado = 'pendiente_confirmacion' WHERE id_orden = ?");
$stmtUpd->execute([$nombreArchivo, $id_orden]);

?>
<?php
session_start();

$colorMarron = '#7a5c39';
$colorCaramelo = '#c89e6a';
$colorBeige = '#fcf4e7';
$colorBlancoCaldo = '#fffaf3';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Compra pendiente | Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="/assets/img/icons/favicon.png">
  <style>
    :root {
      --marron: <?= $colorMarron ?>;
      --caramelo: <?= $colorCaramelo ?>;
      --beige: <?= $colorBeige ?>;
      --blanco-caldo: <?= $colorBlancoCaldo ?>;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--beige);
      color: var(--marron);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .icon-wait {
      display: inline-block;
      background: linear-gradient(135deg, var(--caramelo), var(--marron));
      border-radius: 50%;
      width: 90px;
      height: 90px;
      margin-bottom: 1.2rem;
      box-shadow: 0 3px 18px 0 #0002;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon-wait svg {
      width: 50px;
      height: 50px;
      color: #fff;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <?php include __DIR__ . '/../templates/navbar.php'; ?>

  <main class="flex-grow flex flex-col items-center justify-center py-10 px-4 bg-[var(--blanco-caldo)]" style="min-height:60vh;">
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-lg p-8 flex flex-col items-center text-center border border-[var(--caramelo)]">
      <span class="icon-wait mb-4">
        <svg fill="none" viewBox="0 0 50 50">
          <circle cx="25" cy="25" r="24" stroke="white" stroke-width="2"/>
          <path d="M25 13v12l9 5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <h1 class="text-3xl sm:text-4xl font-extrabold mb-3 text-[var(--caramelo)]">Pago pendiente</h1>
      <p class="text-lg mb-3 text-[var(--marron)]">
        Tu pedido fue recibido, pero el pago está pendiente.<br>
        Apenas se confirme, recibirás un correo de confirmación.
      </p>
      <p class="text-sm text-gray-600 mb-7">
        Si pagaste por transferencia o un método manual, puede demorar algunos minutos.<br>
        Ante cualquier duda, escribinos a <a href="mailto:contacto@petiqueta.uy" class="text-[var(--caramelo)] hover:underline">contacto@petiqueta.uy</a>
      </p>
      <a href="https://petiqueta.uy" class="bg-[var(--caramelo)] hover:bg-[var(--marron)] text-white font-semibold px-8 py-3 rounded-lg shadow-md transition mb-2 block w-full sm:w-auto">
        Volver a la tienda
      </a>
    </div>
  </main>

  <?php include __DIR__ . '/../templates/footer.php'; ?>

</body>
</html>

