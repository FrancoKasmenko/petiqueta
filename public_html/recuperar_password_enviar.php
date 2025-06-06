<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/config.php';
session_start();

// ----- FUNCION DE MAIL HTML PETIQUETA -----
function enviar_email($to, $subject, $body_html) {
    $headers = "From: Petiqueta <no-reply@petiqueta.uy>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    mail($to, $subject, $body_html, $headers);
}

// --- Recibir email del form
$email = trim($_POST['email'] ?? '');

if (!$email) {
    $_SESSION['error'] = 'Completá tu email.';
    header("Location: recuperar_password");
    exit;
}

$stmt = $pdo->prepare("SELECT id_usuario, nombre FROM usuario WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['error'] = 'No existe una cuenta con ese correo.';
    header("Location: recuperar_password.php");
    exit;
}

// Generar/Actualizar token y vencimiento (24hs)
$token = bin2hex(random_bytes(32));
$expiracion = date('Y-m-d H:i:s', time() + 3600*24);

$stmt = $pdo->prepare("UPDATE usuario SET token_password = ?, token_expiracion = ? WHERE id_usuario = ?");
$stmt->execute([$token, $expiracion, $usuario['id_usuario']]);

$link = 'https://petiqueta.uy/asignar_password?token=' . $token;
$logo = 'https://petiqueta.uy/assets/img/icons/favicon.png';

$asunto = "Recuperá tu contraseña en Petiqueta";

$mensaje_html = <<<EOT
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña - Petiqueta</title>
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; background: #fcf4e7; margin:0; padding:0;">
  <div style="max-width: 450px; margin: 38px auto; background: #fffaf3; border-radius: 20px; box-shadow: 0 2px 16px #e4d4bd5c; padding: 32px 24px 26px 24px;">
    <div style="text-align:center;">
      <img src="$logo" alt="Petiqueta" style="width: 58px; height:58px; border-radius: 16px; margin-bottom: 14px;">
    </div>
    <h2 style="color: #7a5c39; font-size: 1.7rem; margin: 0 0 18px 0; text-align: center; font-weight:700;">
      Recuperar tu contraseña
    </h2>
    <p style="font-size:1.1rem; color: #7a5c39; margin-bottom: 12px;">
      Hola <b>{$usuario['nombre']}</b>,
    </p>
    <p style="color:#7a5c39; margin-bottom:18px;">
      Recibimos una solicitud para restablecer tu contraseña en <b>Petiqueta</b>.
    </p>
    <div style="text-align: center;">
      <a href="$link" style="background:#c89e6a; color:#fff; font-weight:600; font-size:1.1rem; text-decoration:none; padding: 13px 32px; border-radius: 9px; display:inline-block; margin:16px 0 22px 0;">
        Crear nueva contraseña
      </a>
    </div>
    <p style="color:#7a5c39; font-size:.97rem; margin-bottom:8px;">
      El enlace es válido por 24 horas.<br>
      Si no solicitaste el cambio, ignorá este email.
    </p>
    <hr style="border:none; border-top:1.5px solid #e9e2cf; margin:22px 0 12px 0;">
    <div style="text-align: center;">
      <small style="color: #b49c7a; font-size:.98rem;">
        &copy; Petiqueta · <a href="https://petiqueta.uy" style="color:#c89e6a; text-decoration:none;">petiqueta.uy</a>
      </small>
    </div>
  </div>
</body>
</html>
EOT;


enviar_email($email, $asunto, $mensaje_html);

$_SESSION['success'] = 'Te enviamos un enlace para restablecer tu contraseña. Revisá tu correo.';
header("Location: recuperar_password");
exit;
