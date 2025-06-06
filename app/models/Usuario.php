<?php
// app/models/Usuario.php
class Usuario
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function existeEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function registrar($datos)
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO usuario 
        (nombre, apellido, email, telefono, password_hash, fecha_creacion, direccion, departamento, barrio) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)
    ");
        return $stmt->execute([
            $datos['nombre'],
            $datos['apellido'] ?? null,
            $datos['email'],
            $datos['telefono'] ?? null,
            password_hash($datos['password'], PASSWORD_BCRYPT),
            $datos['direccion'] ?? null,
            $datos['departamento'] ?? null,
            $datos['barrio'] ?? null,
        ]);
    }
    public function actualizarPerfilCompleto($id_usuario, $datos)
    {
        try {
            $this->pdo->beginTransaction();

            // Actualizar datos en tabla usuario (solo básicos)
            $stmtUsuario = $this->pdo->prepare("UPDATE usuario SET 
            nombre = ?, 
            email = ?, 
            telefono = ?
            WHERE id_usuario = ?");
            $okUsuario = $stmtUsuario->execute([
                $datos['nombre'],
                $datos['email'],
                $datos['telefono'],
                $id_usuario
            ]);

            // Verificar si ya existe una dirección para ese usuario
            $stmtCheck = $this->pdo->prepare("SELECT id_direccion FROM direccion WHERE id_usuario = ? LIMIT 1");
            $stmtCheck->execute([$id_usuario]);
            $idDireccion = $stmtCheck->fetchColumn();

            if ($idDireccion) {
                // Actualizar esa dirección
                $stmtDireccion = $this->pdo->prepare("UPDATE direccion SET direccion = ?, departamento = ?, barrio = ? WHERE id_direccion = ?");
                $okDireccion = $stmtDireccion->execute([
                    $datos['direccion'],
                    $datos['departamento'],
                    $datos['barrio'],
                    $idDireccion
                ]);
            } else {
                // Insertar una nueva
                $stmtDireccion = $this->pdo->prepare("INSERT INTO direccion (id_usuario, direccion, departamento, barrio) VALUES (?, ?, ?, ?)");
                $okDireccion = $stmtDireccion->execute([
                    $id_usuario,
                    $datos['direccion'],
                    $datos['departamento'],
                    $datos['barrio']
                ]);
            }

            if ($okUsuario && $okDireccion) {
                $this->pdo->commit();
                return true;
            } else {
                $this->pdo->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Aquí podés loguear el error si querés
            return false;
        }
    }




    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
}
