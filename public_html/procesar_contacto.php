<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once __DIR__ . '/../vendor/autoload.php'; 

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$nombre || !$email || !$asunto || !$mensaje || !$telefono  || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: contacto?error=1");
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = '';
    $mail->SMTPAuth = true;
    $mail->Username = '';
    $mail->Password = ''; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 12345;

    // Remitente y destinatario
    $mail->setFrom('remitente', 'Formulario PETIQUETA');
    $mail->addAddress('destinatario'); 

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = "Contacto web: $asunto";
    $mail->Body = "
        <h2>Nuevo mensaje desde el formulario de contacto</h2>
        <p><strong>Nombre:</strong> $nombre</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Telefono:</strong> $telefono</p>
        <p><strong>Asunto:</strong> $asunto</p>
        <p><strong>Mensaje:</strong><br>$mensaje</p>
    ";

    $mail->send();

    header("Location: contacto?enviado=1");
    exit;
} catch (Exception $e) {
    header("Location: contacto?error=2");
    exit;
}
?>
