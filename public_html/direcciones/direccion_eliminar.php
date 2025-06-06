<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once  '../../app/core/config.php';
require_once '../../app/models/Direccion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDireccion = $_POST['id_direccion'] ?? null;
    if (!$idDireccion) {
        $_SESSION['error'] = 'ID de dirección no especificado.';
        header('Location: ../perfil.php');
        exit;
    }

    $direccionModel = new Direccion($pdo);

    // Opcional: verificar que la dirección pertenece al usuario logueado para seguridad
    $direccion = $direccionModel->obtenerDireccionPorId($idDireccion);
    if (!$direccion || $direccion['id_usuario'] != $_SESSION['user_id']) {
        $_SESSION['error'] = 'No tienes permiso para eliminar esta dirección.';
        header('Location: ../perfil.php');
        exit;
    }

    $exito = $direccionModel->eliminarDireccion($idDireccion);

    if ($exito) {
        $_SESSION['mensaje'] = 'Dirección eliminada correctamente.';
    } else {
        $_SESSION['error'] = 'Error al eliminar la dirección.';
    }
}

header('Location: ../perfil.php');
exit;
