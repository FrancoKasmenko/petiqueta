<?php
session_start();
require 'config.php';
require 'auth.php';
requireAdmin();

// Procesar acciones: agregar, editar, eliminar
$action = $_GET['action'] ?? null;
$id_mascota = $_GET['id'] ?? null;
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entrada
    $nombre = trim($_POST['nombre'] ?? '');
    $raza = trim($_POST['raza'] ?? '');
    $edad = (int)($_POST['edad'] ?? 0);
    $sexo = $_POST['sexo'] ?? 'desconocido';
    $alergias = trim($_POST['alergias'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $veterinaria_nombre = trim($_POST['veterinaria_nombre'] ?? '');
    $veterinaria_contacto = trim($_POST['veterinaria_contacto'] ?? '');
    $codigo_mascotag = trim($_POST['codigo_mascotag'] ?? '');
    $codigo_qr = trim($_POST['codigo_qr'] ?? '');

    // Validar datos mínimos
    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }
    if ($codigo_mascotag === '') {
        $errors[] = 'El código es obligatorio.';
    }
    if ($codigo_qr === '') {
        $errors[] = 'El código QR es obligatorio.';
    }

    if (empty($errors)) {
        if ($action === 'edit' && $id_mascota) {
            // Update mascota
            $stmt = $pdo->prepare("UPDATE mascota SET nombre = ?, raza = ?, edad = ?, sexo = ?, alergias = ?, descripcion = ?, veterinaria_nombre = ?, veterinaria_contacto = ?, codigo_mascotag = ?, codigo_qr = ? WHERE id_mascota = ?");
            $stmt->execute([$nombre, $raza, $edad, $sexo, $alergias, $descripcion, $veterinaria_nombre, $veterinaria_contacto, $codigo_mascotag, $codigo_qr, $id_mascota]);
            $success = "Mascota actualizada correctamente.";
        } else {
            // Insert nueva mascota
            // Para simplificar asignamos id_usuario = NULL (o puedes cambiar según admin)
            $stmt = $pdo->prepare("INSERT INTO mascota (id_usuario, nombre, raza, edad, sexo, alergias, descripcion, veterinaria_nombre, veterinaria_contacto, codigo_mascotag, codigo_qr) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $raza, $edad, $sexo, $alergias, $descripcion, $veterinaria_nombre, $veterinaria_contacto, $codigo_mascotag, $codigo_qr]);
            $success = "Mascota creada correctamente.";
        }
        $action = null; // Limpiar acción para mostrar lista
    }
}

if ($action === 'delete' && $id_mascota) {
    // Eliminar mascota
    $stmt = $pdo->prepare("DELETE FROM mascota WHERE id_mascota = ?");
    $stmt->execute([$id_mascota]);
    $success = "Mascota eliminada correctamente.";
    $action = null;
}

// Para edición, cargar datos de mascota
if ($action === 'edit' && $id_mascota) {
    $stmt = $pdo->prepare("SELECT * FROM mascota WHERE id_mascota = ?");
    $stmt->execute([$id_mascota]);
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mascota) {
        $errors[] = "Mascota no encontrada.";
        $action = null;
    }
}

// Listar mascotas (simple paginación)
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM mascota");
$total = $totalStmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM mascota ORDER BY fecha_registro DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Backoffice Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="container pt-4">
        <h1>Mascotas</h1>

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
            <a href="mascotas.php" class="btn btn-secondary mb-3">Volver al listado</a>

            <form method="POST" class="mb-5">
                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label>Raza</label>
                    <input type="text" name="raza" class="form-control" value="<?= htmlspecialchars($mascota['raza'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Edad</label>
                    <input type="number" name="edad" class="form-control" value="<?= htmlspecialchars($mascota['edad'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Sexo</label>
                    <select name="sexo" class="form-select">
                        <?php
                        $sexos = ['macho', 'hembra', 'desconocido'];
                        $selectedSexo = $mascota['sexo'] ?? 'desconocido';
                        foreach ($sexos as $s) {
                            $sel = ($s === $selectedSexo) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>" . ucfirst($s) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Alergias</label>
                    <textarea name="alergias" class="form-control"><?= htmlspecialchars($mascota['alergias'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($mascota['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Veterinaria Nombre</label>
                    <input type="text" name="veterinaria_nombre" class="form-control" value="<?= htmlspecialchars($mascota['veterinaria_nombre'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Veterinaria Contacto</label>
                    <input type="text" name="veterinaria_contacto" class="form-control" value="<?= htmlspecialchars($mascota['veterinaria_contacto'] ?? '') ?>" />
                </div>
                <div class="mb-3">
                    <label>Código Petiqueta</label>
                    <input type="text" name="codigo_mascotag" class="form-control" value="<?= htmlspecialchars($mascota['codigo_mascotag'] ?? '') ?>" required />
                </div>
                <div class="mb-3">
                    <label>Código QR</label>
                    <input type="text" name="codigo_qr" class="form-control" value="<?= htmlspecialchars($mascota['codigo_qr'] ?? '') ?>" required />
                </div>

                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Guardar cambios' : 'Agregar mascota' ?></button>
            </form>

        <?php else: ?>

            <a href="mascotas.php?action=add" class="btn btn-success mb-3">Agregar Mascota</a>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Raza</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Código Petiqueta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mascotas as $m): ?>
                        <tr>
                            <td><?= $m['id_mascota'] ?></td>
                            <td><?= htmlspecialchars($m['nombre']) ?></td>
                            <td><?= htmlspecialchars($m['raza']) ?></td>
                            <td><?= htmlspecialchars($m['edad']) ?></td>
                            <td><?= ucfirst($m['sexo']) ?></td>
                            <td><?= htmlspecialchars($m['codigo_mascotag']) ?></td>
                            <td>
                                <a href="mascotas.php?action=edit&id=<?= $m['id_mascota'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="mascotas.php?action=delete&id=<?= $m['id_mascota'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro querés eliminar esta mascota?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($mascotas)): ?>
                        <tr><td colspan="7" class="text-center">No hay mascotas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación simple -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
