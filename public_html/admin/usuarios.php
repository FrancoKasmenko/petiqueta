<?php
session_start();
require 'config.php';
require 'auth.php';
requireAdmin();

$action = $_GET['action'] ?? null;
$id_usuario = $_GET['id'] ?? null;
$errors = [];
$success = null;

// Procesar formularios POST para agregar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $rol = $_POST['rol'] ?? 'cliente';
    $password = $_POST['password'] ?? '';

    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($email === '') $errors[] = "El email es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email no válido.";

    if ($action === 'add' && $password === '') {
        $errors[] = "La contraseña es obligatoria para crear un usuario.";
    }

    if (empty($errors)) {
        if ($action === 'edit' && $id_usuario) {
            // Actualizar usuario (sin cambiar contraseña si vacía)
            if ($password !== '') {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, rol = ?, password_hash = ? WHERE id_usuario = ?");
                $stmt->execute([$nombre, $apellido, $email, $telefono, $rol, $password_hash, $id_usuario]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, rol = ? WHERE id_usuario = ?");
                $stmt->execute([$nombre, $apellido, $email, $telefono, $rol, $id_usuario]);
            }
            $success = "Usuario actualizado correctamente.";
        } else {
            // Insert nuevo usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuario (nombre, apellido, email, telefono, rol, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $telefono, $rol, $password_hash]);
            $success = "Usuario creado correctamente.";
        }
        $action = null;
    }
}

// Eliminar usuario
if ($action === 'delete' && $id_usuario) {
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $success = "Usuario eliminado correctamente.";
    $action = null;
}

// Cargar usuario para edición
if ($action === 'edit' && $id_usuario) {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        $errors[] = "Usuario no encontrado.";
        $action = null;
    }
}

// Listar usuarios
$stmt = $pdo->query("SELECT * FROM usuario ORDER BY fecha_creacion DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Backoffice Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="container pt-4">
        <h1>Usuarios</h1>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <a href="usuarios.php" class="btn btn-secondary mb-3">Volver al listado</a>

            <form method="POST" class="mb-5">
                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label>Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Rol</label>
                    <select name="rol" class="form-select">
                        <?php
                        $roles = ['cliente', 'proveedor', 'admin'];
                        $selectedRol = $usuario['rol'] ?? 'cliente';
                        foreach ($roles as $r) {
                            $sel = ($r === $selectedRol) ? 'selected' : '';
                            echo "<option value=\"$r\" $sel>" . ucfirst($r) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label><?= $action === 'edit' ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label>
                    <input type="password" name="password" class="form-control" <?= $action === 'add' ? 'required' : '' ?> />
                </div>

                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Guardar cambios' : 'Agregar usuario' ?></button>
            </form>

        <?php else: ?>

            <a href="usuarios.php?action=add" class="btn btn-success mb-3">Agregar Usuario</a>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Fecha creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= $u['apellido'] ?? "N/A" ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telefono']) ?></td>
                            <td><?= ucfirst($u['rol']) ?></td>
                            <td><?= $u['fecha_creacion'] ?></td>
                            <td>
                                <a href="usuarios.php?action=edit&id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="usuarios.php?action=delete&id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro querés eliminar este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="8" class="text-center">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php endif; ?>

    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
