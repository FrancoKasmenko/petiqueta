<?php
class Mascota
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Verificar si un código ya existe en mascota
    public function codigoExiste($codigo)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM mascota WHERE codigo_mascotag = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetchColumn() > 0;
    }

    // Insertar nueva mascota 
    public function agregarMascota(
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
        $idDireccion = null,
        $veterinaria_nombre = null,
        $veterinaria_contacto = null
    ) {
        try {
            $this->pdo->beginTransaction();

            error_log("[Mascota] --- INICIO agregarMascota ---");
            error_log("[Mascota] INSERT: idUsuario=$idUsuario, codigoMascotag=$codigoMascotag, nombre=$nombre, edad=$edad, sexo=$sexo, idDireccion=$idDireccion");

            $sql = "INSERT INTO mascota (
                        id_usuario, codigo_mascotag, nombre, raza, edad, sexo, foto_url,
                        descripcion, alergias, fecha_registro, codigo_qr, id_direccion,
                        veterinaria_nombre, veterinaria_contacto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);
            $okInsert = $stmt->execute([
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
            ]);

            $sql2 = "UPDATE mascotag_codes
                     SET assigned_to_user = ?, used_at = NOW(), status = 'used'
                     WHERE code = ? AND status IN ('available', 'assigned', 'printed')";

            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$idUsuario, $codigoMascotag]);

            if ($stmt2->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // Obtener mascota por código con datos del dueño y dirección
    public function obtenerMascotaPorCodigo($codigo)
    {
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.nombre AS nombre_dueño, u.email, u.telefono,
                   d.direccion, d.barrio, d.departamento
            FROM mascota m
            LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
            LEFT JOIN direccion d ON m.id_direccion = d.id_direccion
            WHERE m.codigo_mascotag = ?
        ");
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
