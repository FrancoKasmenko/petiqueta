<?php
require_once '../app/core/config.php'; // Ajusta ruta si tu config está en otro lado

session_start();
$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$usuario = null; // Por si hay que mostrar el form con error y usuario válido

if ($token) {
    $stmt = $pdo->prepare("SELECT id_usuario, token_expiracion FROM usuario WHERE token_password = ?");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && strtotime($usuario['token_expiracion']) > time()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (strlen($password) < 8) {
                $error = "La contraseña debe tener al menos 8 caracteres.";
            } elseif ($password !== $password_confirm) {
                $error = "Las contraseñas no coinciden.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuario SET password_hash = ?, token_password = NULL, token_expiracion = NULL WHERE id_usuario = ?");
                $stmt->execute([$hash, $usuario['id_usuario']]);
                $success = "Contraseña establecida correctamente. Ya podés <a href='login' style='color:#c89e6a; text-decoration:underline;'>iniciar sesión</a>.";
            }
        }
    } else {
        $error = "El link de recuperación no es válido o expiró.";
    }
} else {
    $error = "Token inválido.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar contraseña - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
    <style>
        body {
            background: #fcf4e7;
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .petiqueta-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fffaf3;
            border-radius: 18px;
            padding: 30px 28px;
            box-shadow: 0 2px 20px #e4d4bd5c;
        }
        .petiqueta-container h2 {
            color: #7a5c39;
            font-weight: 700;
            text-align:center;
            font-size: 1.8rem;
            margin-bottom: 22px;
        }
        .petiqueta-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .petiqueta-form label {
            color: #7a5c39;
            font-weight: 500;
            font-size: 1rem;
        }
        .petiqueta-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 7px;
            border-radius: 7px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        .petiqueta-form button {
            background: #c89e6a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 7px;
            font-weight: bold;
            font-size: 1.06rem;
            transition: background .2s;
        }
        .petiqueta-form button:hover {
            background: #7a5c39;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 7px;
            margin-bottom: 15px;
            font-size: 1rem;
            text-align: center;
        }
        .alert-error {
            color: #c00;
            background: #ffe9e9;
        }
        .alert-success {
            color: #257a3e;
            background: #e7ffe9;
        }
        @media (max-width: 500px) {
            .petiqueta-container {
                max-width: 98vw;
                margin: 14vw auto 0 auto;
                padding: 18px 6vw 18px 6vw;
                border-radius: 11px;
            }
            .petiqueta-container h2 {
                font-size: 1.22rem;
                margin-bottom: 15px;
            }
            .petiqueta-form button {
                font-size: 1rem;
                padding: 10px;
            }
            .petiqueta-form label, .alert, .petiqueta-form input {
                font-size: .99rem;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../templates/navbar.php'; ?>
<div class="petiqueta-container">
    <h2>Asignar contraseña</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ((!$success && !$error) || ($error && $usuario)): ?>
        <form method="POST" class="petiqueta-form" autocomplete="off">
            <label>
                Nueva contraseña
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label>
                Confirmar contraseña
                <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
            </label>
            <button type="submit">Asignar contraseña</button>
        </form>
    <?php endif; ?>
    <p style="text-align: center; margin-top: 18px;">
        <a href="login" style="color: #7a5c39;">Iniciar sesión</a>
    </p>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
