<?php
class Direccion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Obtener todas las direcciones de un usuario
    public function obtenerDireccionesPorUsuario($idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM direccion WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar una nueva dirección para un usuario
    public function agregarDireccion($idUsuario, $direccion, $departamento, $barrio, $latitud = null, $longitud = null)
    {
        $stmt = $this->pdo->prepare("INSERT INTO direccion (id_usuario, direccion, departamento, barrio, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$idUsuario, $direccion, $departamento, $barrio, $latitud, $longitud]);
    }

    // Obtener una dirección por id
    public function obtenerDireccionPorId($idDireccion)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM direccion WHERE id_direccion = ?");
        $stmt->execute([$idDireccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar dirección
    public function actualizarDireccion($idDireccion, $direccion, $departamento, $barrio, $latitud = null, $longitud = null)
    {
        $stmt = $this->pdo->prepare("UPDATE direccion SET direccion = ?, departamento = ?, barrio = ?, latitud = ?, longitud = ? WHERE id_direccion = ?");
        return $stmt->execute([$direccion, $departamento, $barrio, $latitud, $longitud, $idDireccion]);
    }

    // Eliminar dirección
    public function eliminarDireccion($idDireccion)
    {
        $stmt = $this->pdo->prepare("DELETE FROM direccion WHERE id_direccion = ?");
        return $stmt->execute([$idDireccion]);
    }
}
