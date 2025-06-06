<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../core/config.php';

class UsuarioController
{
    private $usuarioModel;

    public function __construct()
    {
        global $pdo;
        $this->usuarioModel = new Usuario($pdo);
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $password = $_POST['contrasena'] ?? '';
            $password2 = $_POST['contrasena_confirmar'] ?? '';
            $direccion = trim($_POST['direccion'] ?? '');
            $departamento = trim($_POST['departamento'] ?? '');
            $barrio = trim($_POST['barrio'] ?? '');
            $codigoMascotag = trim($_POST['codigo_mascotag'] ?? '');

            if (!$nombre || !$email || !$telefono || !$password || !$password2 || !$codigoMascotag) {
                return "Completa todos los campos obligatorios.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "El correo electrónico no es válido.";
            }
            if ($password !== $password2) {
                return "Las contraseñas no coinciden.";
            }
            if (strlen($password) < 8) {
                return "La contraseña debe tener al menos 8 caracteres.";
            }
            if ($this->usuarioModel->existeEmail($email)) {
                return "El email ya está registrado.";
            }

            $ok = $this->usuarioModel->registrar([
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'password' => $password,
                'direccion' => $direccion ?: null,
                'departamento' => $departamento ?: null,
                'barrio' => $barrio ?: null,
                'codigo_mascotag' => $codigoMascotag,
            ]);

            return $ok ? null : "Error al registrar usuario.";
        }
    }

    public function actualizarPerfil()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $id_usuario = $_SESSION['user_id'] ?? null;
            if (!$id_usuario) {
                return "No autenticado";
            }

            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'departamento' => trim($_POST['departamento'] ?? ''),
                'barrio' => trim($_POST['barrio'] ?? ''),
            ];

            if (!$datos['nombre'] || !$datos['email']) {
                return "Nombre y email son obligatorios.";
            }
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                return "Email inválido.";
            }

            $ok = $this->usuarioModel->actualizarPerfilCompleto($id_usuario, $datos);

            if ($ok) {
                return null;
            } else {
                return "Error al actualizar el perfil.";
            }
        }
    }




    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->usuarioModel->login($email, $password);

            if ($user) {
                session_start();
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_nombre'] = $user['nombre'];
                header('Location: perfil');
                exit;
            } else {
                return "Usuario o contraseña incorrectos.";
            }
        }
    }
}
