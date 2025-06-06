<?php
session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../models/Mascota.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Debes iniciar sesión para registrar una mascota.";
    header('Location: /login.php');
    exit;
}

$codigo = $_POST['codigo_mascotag'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$raza = trim($_POST['raza'] ?? '');
$edad = intval($_POST['edad'] ?? 0);
$sexo = $_POST['sexo'] ?? 'desconocido';
$alergias = trim($_POST['alergias'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if (!$codigo || !$nombre) {
    $_SESSION['error'] = 'Código y nombre son obligatorios.';
    header("Location: /mascota.php?codigo=$codigo");
    exit;
}

$mascotaModel = new Mascota($pdo);


if ($mascotaModel->codigoExiste($codigo)) {
    $_SESSION['error'] = 'El código de la Petiqueta ya está asignado a otra mascota.';
    header("Location: /mascota.php?codigo=$codigo");
    exit;
}

$foto_url = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../https://petiqueta.uy/assets/img/mascotas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['foto']['tmp_name'];
    $fileName = uniqid('mascota_') . '_' . basename($_FILES['foto']['name']);
    $targetFilePath = $uploadDir . $fileName;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($tmpName);
    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['error'] = 'Tipo de archivo no permitido. Solo JPG, PNG y GIF.';
        header("Location: /mascota.php?codigo=$codigo");
        exit;
    }

    if (move_uploaded_file($tmpName, $targetFilePath)) {
        $foto_url = 'assets/img/mascotas/' . $fileName;
    } else {
        $_SESSION['error'] = 'Error al subir la imagen.';
        header("Location: /mascota.php?codigo=$codigo");
        exit;
    }
}

$idUsuario = $_SESSION['user_id'];
$fecha_registro = date('Y-m-d H:i:s');
$codigo_qr = $codigo; 

$exito = $mascotaModel->agregarMascota(
    $idUsuario,
    $codigo,
    $nombre,
    $raza,
    $edad,
    $sexo,
    $foto_url,
    $descripcion,
    $alergias,
    $fecha_registro,
    $codigo_qr
);

if ($exito) {
    $_SESSION['mensaje'] = 'Mascota agregada correctamente.';
    header("Location: /mascota.php?codigo=$codigo");
    exit;
} else {
    $_SESSION['error'] = 'Error al agregar mascota. Intente de nuevo.';
    header("Location: /mascota.php?codigo=$codigo");
    exit;
}
