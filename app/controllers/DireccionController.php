<?php
require_once __DIR__ . '/../models/Direccion.php';

class DireccionController
{
    private $direccionModel;
    private $userId;
    public function __construct($pdo)
    {
        $this->direccionModel = new Direccion($pdo);
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }
        $this->userId = $_SESSION['user_id'];
    }

    public function listar()
    {
        return $this->direccionModel->obtenerDireccionesPorUsuario($this->userId);
    }
    
        public function obtenerDirecciones()
    {
        return $this->direccionModel->obtenerDireccionesPorUsuario($this->userId);
    }

    public function agregar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: perfil');
            exit;
        }

        $direccion = trim($_POST['direccion'] ?? '');
        $departamento = trim($_POST['departamento'] ?? '');
        $barrio = trim($_POST['barrio'] ?? '');
        $latitud = isset($_POST['latitud']) ? floatval($_POST['latitud']) : null;
        $longitud = isset($_POST['longitud']) ? floatval($_POST['longitud']) : null;

        if (empty($direccion)) {
            $_SESSION['error'] = "La dirección es obligatoria.";
            header('Location: perfil');
            exit;
        }

        $exito = $this->direccionModel->agregarDireccion($this->userId, $direccion, $departamento, $barrio, $latitud, $longitud);

        if ($exito) {
            $_SESSION['mensaje'] = "Dirección agregada correctamente.";
        } else {
            $_SESSION['error'] = "Error al agregar la dirección.";
        }
        header('Location: perfil');
        exit;
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: perfil');
            exit;
        }

        $idDireccion = intval($_POST['id_direccion'] ?? 0);
        if ($idDireccion <= 0) {
            $_SESSION['error'] = "Dirección inválida.";
            header('Location: perfil');
            exit;
        }

        $exito = $this->direccionModel->eliminarDireccion($idDireccion);
        if ($exito) {
            $_SESSION['mensaje'] = "Dirección eliminada correctamente.";
        } else {
            $_SESSION['error'] = "Error al eliminar la dirección.";
        }
        header('Location: perfil');
        exit;
    }

}
