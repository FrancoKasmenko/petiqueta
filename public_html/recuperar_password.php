<?php
session_start();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  
    <style>
        body {
            background: #fcf4e7;
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .petiqueta-card {
            max-width: 410px;
            margin: 70px auto;
            background: #fffaf3;
            border-radius: 20px;
            padding: 34px 28px 28px 28px;
            box-shadow: 0 2px 20px #e4d4bd5c;
        }
        .petiqueta-title {
            color: #7a5c39;
            font-weight: 700;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 24px;
        }
        .petiqueta-label {
            color: #7a5c39;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }
        .petiqueta-input {
            width: 100%;
            padding: 12px;
            border-radius: 9px;
            border: 1px solid #c89e6a;
            font-size: 1rem;
            outline: none;
            background: #fff;
            margin-bottom: 14px;
            box-sizing: border-box;
            transition: border 0.2s;
        }
        .petiqueta-input:focus {
            border-color: #7a5c39;
            box-shadow: 0 0 0 2px #c89e6a33;
        }
        .petiqueta-btn {
            background: #c89e6a;
            color: #fff;
            border: none;
            padding: 13px;
            border-radius: 9px;
            font-weight: 700;
            font-size: 1.09rem;
            cursor: pointer;
            margin-top: 5px;
            margin-bottom: 2px;
            transition: background 0.2s;
            width: 100%;
            letter-spacing: .5px;
        }
        .petiqueta-btn:hover {
            background: #7a5c39;
        }
        .petiqueta-alert {
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.97rem;
            margin-bottom: 18px;
        }
        .petiqueta-alert-error {
            background: #ffe9e9;
            color: #c00;
            border: 1.5px solid #f5c2c2;
        }
        .petiqueta-alert-success {
            background: #e7ffe9;
            color: #257a3e;
            border: 1.5px solid #87dcb0;
        }
        .petiqueta-link {
            color: #7a5c39;
            text-align: center;
            display: block;
            margin-top: 24px;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: .1px;
        }
        .petiqueta-link:hover {
            text-decoration: underline;
            color: #c89e6a;
        }
        @media (max-width: 600px) {
            .petiqueta-card {
                margin: 20px 8px;
                padding: 22px 6vw 20px 6vw;
                max-width: 98vw;
            }
            .petiqueta-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>
    <div class="petiqueta-card">
        <h2 class="petiqueta-title">Recuperar contraseña</h2>
        <?php if ($error): ?>
            <div class="petiqueta-alert petiqueta-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="petiqueta-alert petiqueta-alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" action="recuperar_password_enviar.php" autocomplete="off">
            <label class="petiqueta-label" for="email">Tu correo electrónico</label>
            <input type="email" name="email" id="email" required class="petiqueta-input" placeholder="ejemplo@correo.com" autocomplete="email">
            <button type="submit" class="petiqueta-btn">Enviar enlace</button>
        </form>
        <a class="petiqueta-link" href="login">Volver al login</a>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
