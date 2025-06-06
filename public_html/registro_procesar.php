<?php
session_start();
require_once '../app/controllers/UsuarioController.php';

$controller = new UsuarioController();

$error = $controller->registrar();

if ($error) {
    $_SESSION['error'] = $error;
    header('Location: registro');
    exit;
} else {
    $_SESSION['success'] = 'Registro exitoso. Ya podés iniciar sesión.';
    header('Location: registro');
    exit;
}
