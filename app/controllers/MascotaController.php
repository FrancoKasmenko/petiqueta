<?php
require_once __DIR__ . '/../models/Mascota.php';

class MascotaController
{
    private $mascotaModel;

    public function __construct($pdo)
    {
        $this->mascotaModel = new Mascota($pdo);
    }

    public function agregar()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }

        $idUsuario = $_SESSION['user_id'];
        $codigoMascotag = trim($_POST['codigo_mascotag'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $raza = trim($_POST['raza'] ?? '');
        $edad = intval($_POST['edad'] ?? 0);
        $sexo = $_POST['sexo'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idDireccion = !empty($_POST['id_direccion']) ? intval($_POST['id_direccion']) : null;
        $veterinaria_nombre = trim($_POST['veterinaria_nombre'] ?? '');
        $veterinaria_contacto = trim($_POST['veterinaria_contacto'] ?? '');

        if (!$codigoMascotag || strlen($codigoMascotag) !== 6 || !$nombre || !$raza || !$edad || !$sexo) {
            $_SESSION['error'] = 'Complete todos los campos correctamente.';
            header('Location: perfil');
            exit;
        }

        $foto_url = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../../https://petiqueta.uy/assets/img/mascotas/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tmpName = $_FILES['foto']['tmp_name'];
            $fileName = uniqid('mascota_') . '_' . basename($_FILES['foto']['name']);
            $targetFilePath = $uploadDir . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($tmpName);
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error'] = 'Tipo de archivo no permitido. Solo JPG, PNG y GIF.';
                header('Location: perfil');
                exit;
            }

            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $foto_url = 'assets/img/mascotas/' . $fileName;
            } else {
                $_SESSION['error'] = 'Error al subir la imagen.';
                header('Location: perfil');
                exit;
            }
        }

        if ($this->mascotaModel->codigoExiste($codigoMascotag)) {
            $_SESSION['error'] = 'El código de la Petiqueta ya está asignado.';
            header('Location: perfil');
            exit;
        }

        $alergias = trim($_POST['alergias'] ?? '');
        $fecha_registro = date('Y-m-d H:i:s');
        $codigo_qr = '';

        $exito = $this->mascotaModel->agregarMascota(
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
            $veterinaria_nombre,
            $veterinaria_contacto
        );


        if ($exito) {
            $_SESSION['mensaje'] = 'Mascota agregada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al agregar mascota. Intente de nuevo.';
        }

        header('Location: perfil');
        exit;
    }
}
