<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once  '../../app/core/config.php';
require_once '../../app/models/Direccion.php';

$direccionModel = new Direccion($pdo);
$userId = $_SESSION['user_id'];

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../perfil');
    exit;
}

// Obtener y limpiar datos del formulario
$direccion = trim($_POST['direccion'] ?? '');
$barrio = trim($_POST['barrio'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
// Puedes agregar latitud y longitud si las tienes
$latitud = null;
$longitud = null;

if (empty($direccion)) {
    $_SESSION['error'] = 'La dirección es obligatoria.';
    header('Location: ../perfil');
    exit;
}

// Insertar dirección en BD
$exito = $direccionModel->agregarDireccion($userId, $direccion, $departamento, $barrio, $latitud, $longitud);

if ($exito) {
    $_SESSION['mensaje'] = 'Dirección agregada correctamente.';
} else {
    $_SESSION['error'] = 'Error al agregar la dirección. Intente nuevamente.';
}

header('Location: ../perfil');
exit;
