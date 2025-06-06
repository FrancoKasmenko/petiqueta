<?php
session_start();
require_once '../app/controllers/UsuarioController.php';

$usuarioController = new UsuarioController();
$error = $usuarioController->actualizarPerfil();

if ($error) {
    $_SESSION['error_perfil'] = $error;
    header('Location: perfil');
    exit;
} else {
    $_SESSION['mensaje_perfil'] = "Perfil actualizado correctamente.";
    header('Location: perfil');
    exit;
}
