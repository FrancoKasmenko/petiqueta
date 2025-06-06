<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../app/core/config.php';
require_once '../app/models/Mascota.php';

$mascotaModel = new Mascota($pdo);

error_log("[mascota_agregar] Inicio");

$codigoMascotag = trim($_POST['codigo_mascotag'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$raza = trim($_POST['raza'] ?? '');
$edad = intval($_POST['edad'] ?? 0);
$sexo = $_POST['sexo'] ?? '';
$descripcion = trim($_POST['descripcion'] ?? '');
$alergias = trim($_POST['alergias'] ?? '');
$veterinaria_nombre = trim($_POST['veterinaria_nombre'] ?? '');
$veterinaria_contacto = trim($_POST['veterinaria_contacto'] ?? '');
$idDireccion = !empty($_POST['id_direccion']) ? intval($_POST['id_direccion']) : null;
$direccionNueva = trim($_POST['direccion_nueva'] ?? '');

$dueño_nombre = trim($_POST['dueño_nombre'] ?? '');
$dueño_email = trim($_POST['dueño_email'] ?? '');
$dueño_telefono = trim($_POST['dueño_telefono'] ?? '');

$foto_url = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "assets/img/mascotas/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = uniqid() . '_' . basename($_FILES['foto']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
        $foto_url = $uploadDir . $filename;
    } else {
        error_log("[mascota_agregar] Error al subir imagen");
        $_SESSION['error'] = 'Error al subir la imagen.';
        header('Location: mascota?codigo=' . urlencode($codigoMascotag));
        exit;
    }
}

// Validaciones básicas
if (
    !$codigoMascotag ||
    strlen($codigoMascotag) !== 6 ||
    !$nombre ||
    !$raza ||
    !isset($_POST['edad']) || $_POST['edad'] === '' || $edad < 0 ||
    !$sexo
) {
    error_log("[mascota_agregar] Validación básica falló. nombre: $nombre, raza: $raza, edad: $edad, sexo: $sexo, codigo: $codigoMascotag");
    $_SESSION['error'] = 'Complete todos los campos correctamente.';
    header('Location: mascota?codigo=' . urlencode($codigoMascotag));
    exit;
}

// Validar código válido (en mascotag_codes y status 'printed')
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mascotag_codes WHERE code = ? AND status = 'printed'");
$stmt->execute([$codigoMascotag]);
if ($stmt->fetchColumn() == 0) {
    error_log("[mascota_agregar] Código no válido/printed: $codigoMascotag");
    $_SESSION['error'] = 'El código de la Petiqueta no es válido o no está asignado.';
    header('Location: mascota?codigo=' . urlencode($codigoMascotag));
    exit;
}

// Validar que código no asignado ya a mascota
if ($mascotaModel->codigoExiste($codigoMascotag)) {
    error_log("[mascota_agregar] Código ya usado: $codigoMascotag");
    $_SESSION['error'] = 'El código de la Petiqueta ya está asignado a otra mascota.';
    header('Location: mascota?codigo=' . urlencode($codigoMascotag));
    exit;
}

// Si hay sesión, usamos ese usuario
if (isset($_SESSION['user_id'])) {
    $idUsuario = $_SESSION['user_id'];
    error_log("[mascota_agregar] User logueado. idUsuario=$idUsuario");
} else {
    // Sin sesión, se requiere nombre y email dueño
    if (!$dueño_nombre || !$dueño_email) {
        error_log("[mascota_agregar] Falta datos de dueño");
        $_SESSION['error'] = 'Complete los datos del dueño correctamente.';
        header('Location: mascota?codigo=' . urlencode($codigoMascotag));
        exit;
    }

    // Buscar usuario por email
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$dueño_email]);
    $usuarioExistente = $stmt->fetchColumn();

    if ($usuarioExistente) {
        $idUsuario = $usuarioExistente;
        error_log("[mascota_agregar] Dueño ya existe. idUsuario=$idUsuario");
    } else {
        // Crear token y expiración
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 day'));

        // Insertar nuevo usuario (dueño)
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, telefono, token_password, token_expiracion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$dueño_nombre, $dueño_email, $dueño_telefono, $token, $expiracion]);
        $idUsuario = $pdo->lastInsertId();
        error_log("[mascota_agregar] Nuevo dueño creado. idUsuario=$idUsuario, token=$token");

        // Enviar email con link de asignación de contraseña
        $url = 'https://petiqueta.uy/asignar_password?token=' . urlencode($token);

        $subject = "¡Bienvenido a Petiqueta! Asigna tu contraseña";
        $message = "
        <html>
        <head>
          <title>Asigná tu contraseña en Petiqueta</title>
        </head>
        <body>
          <p>¡Hola <b>$dueño_nombre</b>!<br>
          Se creó una cuenta en Petiqueta asociada a este email.<br>
          Para poder acceder y editar tus mascotas, por favor asigná una contraseña haciendo click en el siguiente botón:</p>
          <p><a href='$url' style='background: #7a5c39; color: white; padding: 12px 22px; text-decoration: none; border-radius: 7px;'>Asignar contraseña</a></p>
          <p>Si no solicitaste este registro, podés ignorar este mensaje.</p>
          <small>El link es válido por 24 horas.</small>
        </body>
        </html>";

        // Enviar correo (reemplazá con PHPMailer si tenés configurado)
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Petiqueta <no-reply@petiqueta.uy>\r\n";

        mail($dueño_email, $subject, $message, $headers);
    }
}

// Si mandaron dirección nueva, crearla
if ($direccionNueva) {
    $stmt = $pdo->prepare("INSERT INTO direccion (id_usuario, direccion) VALUES (?, ?)");
    $stmt->execute([$idUsuario, $direccionNueva]);
    $idDireccion = $pdo->lastInsertId();
    error_log("[mascota_agregar] Nueva dirección agregada. idDireccion=$idDireccion");
}

// Insertar mascota
$fecha_registro = date('Y-m-d H:i:s');
$codigo_qr = $codigoMascotag;

error_log("[mascota_agregar] PRE agregarMascota");
$exito = $mascotaModel->agregarMascota(
    $idUsuario,
    $codigoMascotag,
    $nombre,
    $raza,
    $edad,
    $sexo,
    $foto_url,
    $descripcion,
    $alergias,
    $fecha_registro,
    $codigo_qr,
    $idDireccion,
    $veterinaria_nombre,
    $veterinaria_contacto
);

if ($exito) {
    error_log("[mascota_agregar] Mascota agregada OK!");
    $_SESSION['mensaje'] = 'Mascota agregada correctamente.';
} else {
    error_log("[mascota_agregar] ERROR al agregar mascota");
    $_SESSION['error'] = 'Error al agregar mascota. Intente de nuevo.';
}

header('Location: mascota?codigo=' . urlencode($codigoMascotag));
exit;
