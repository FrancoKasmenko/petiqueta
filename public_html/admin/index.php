<?php
session_start();
require 'config.php';
require 'auth.php';

if (isset($_POST['usuario'], $_POST['password'])) {
    if (loginUser($_POST['usuario'], $_POST['password'])) {
        header('Location: usuarios.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Backoffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <form method="POST" class="bg-white p-4 rounded shadow" style="width:320px;">
        <h3 class="mb-4">Backoffice Petiqueta</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="mb-3">
            <input type="text" name="usuario" class="form-control" placeholder="Usuario" required autofocus>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
</body>
</html>
