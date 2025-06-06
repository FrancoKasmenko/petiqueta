<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

require_once '../app/core/config.php';
require_once '../app/controllers/ProductoController.php';

$userId = $_SESSION['user_id'];
$productoController = new ProductoController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar imagen subida
    $rutaImagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/img/productos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen']['name']);
        $rutaCompleta = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            $rutaImagen = 'assets/img/productos/' . $nombreArchivo;
        }
    }

    $datos = [
        'nombre' => trim($_POST['nombre']),
        'descripcion' => trim($_POST['descripcion']),
        'precio' => floatval($_POST['precio']),
        'imagen_url' => $rutaImagen,
        'stock' => intval($_POST['stock']),
    ];

    $resultado = $productoController->agregarProducto($datos, $userId);

    if (isset($resultado['error'])) {
        $_SESSION['error'] = $resultado['error'];
    } else {
        $_SESSION['success'] = $resultado['success'];
    }

    header('Location: producto_lista');
    exit;
}
