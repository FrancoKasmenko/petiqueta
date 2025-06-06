<?php
session_start();
require 'config.php';
require 'auth.php';
requireAdmin();

$accion = $_GET['accion'] ?? '';
$idProducto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errores = [];
$mensaje = '';

// Limpieza básica para textos
function limpiarTexto($texto)
{
    return htmlspecialchars(trim($texto));
}

// Función para obtener colores asignados a un producto
function obtenerColoresProducto($pdo, $idProducto)
{
    $stmt = $pdo->prepare("SELECT id_color FROM producto_color WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Función para obtener la forma asignada a un producto
function obtenerFormaProducto($pdo, $idProducto)
{
    $stmt = $pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    return $stmt->fetchColumn(); // devuelve el valor 'forma' o false si no hay
}

// Función para obtener precios escalonados por cantidad y rol para un producto
function obtenerPreciosPorCantidadProducto($pdo, $idProducto)
{
    $stmt = $pdo->prepare("SELECT * FROM producto_precio_por_cantidad WHERE id_producto = ? ORDER BY rol, cantidad_min");
    $stmt->execute([$idProducto]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener precios por tamaño para un producto (tabla producto_tamanio)
function obtenerPreciosPorTamanioProducto($pdo, $idProducto)
{
    $stmt = $pdo->prepare("SELECT * FROM producto_tamanio WHERE id_producto = ? ORDER BY tamanio");
    $stmt->execute([$idProducto]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener todas las formas disponibles para el select (tabla producto_forma)
function obtenerFormasDisponibles($pdo)
{
    $stmt = $pdo->query("SELECT DISTINCT forma FROM producto_forma ORDER BY forma");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Función para obtener todas las formas asignadas a un producto
function obtenerFormasProducto($pdo, $idProducto)
{
    $stmt = $pdo->prepare("SELECT * FROM producto_forma WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$formasProducto = ($idProducto > 0) ? obtenerFormasProducto($pdo, $idProducto) : [];


// --- Procesar formulario nuevo color ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_color'])) {
    $nuevoNombreColor = limpiarTexto($_POST['nombre_color'] ?? '');
    $nuevoCodigoHex = limpiarTexto($_POST['codigo_hex'] ?? '');

    if (!$nuevoNombreColor) {
        $errores[] = "El nombre del color es obligatorio.";
    }
    if (!$nuevoCodigoHex || !preg_match('/^#([a-fA-F0-9]{6})$/', $nuevoCodigoHex)) {
        $errores[] = "El código HEX debe tener formato válido, ejemplo: #a1b2c3.";
    }



    if (empty($errores)) {
        $stmtInsertColor = $pdo->prepare("INSERT INTO color (nombre, codigo_hex) VALUES (?, ?)");
        $stmtInsertColor->execute([$nuevoNombreColor, $nuevoCodigoHex]);
        $mensaje = "Color agregado correctamente.";
        header('Location: productos.php' . ($accion ? "?accion=$accion&id=$idProducto" : ''));
        exit;
    }
}

// --- Manejo de productos, asociación de colores, precios escalonados, precios por tamaño y forma ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['guardar_color'])) {
    $nombre = limpiarTexto($_POST['nombre'] ?? '');
    $descripcion = limpiarTexto($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $precio_proveedor = isset($_POST['precio_proveedor']) && $_POST['precio_proveedor'] !== '' ? floatval($_POST['precio_proveedor']) : null;
    $stock = intval($_POST['stock'] ?? 0);
    $coloresSeleccionados = $_POST['colores'] ?? [];
    $formaSeleccionada = $_POST['forma'] ?? null; // forma seleccionada

    // Precios por cantidad (arrays)
    $precios_rol = $_POST['precios_rol'] ?? [];
    $precios_cantidad_min = $_POST['precios_cantidad_min'] ?? [];
    $precios_cantidad_max = $_POST['precios_cantidad_max'] ?? [];
    $precios_precio = $_POST['precios_precio'] ?? [];

    $nuevaForma = trim($_POST['nueva_forma'] ?? '');

    // Validar nombre nueva forma si no está vacío
    if ($nuevaForma !== '') {
        // Insertar solo si no existe para ese producto
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM producto_forma WHERE id_producto = ? AND forma = ?");
        $stmtCheck->execute([$idProducto, $nuevaForma]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsertForma = $pdo->prepare("INSERT INTO producto_forma (id_producto, forma) VALUES (?, ?)");
            $stmtInsertForma->execute([$idProducto, $nuevaForma]);
        }
    }
    // Precios por tamaño (arrays)
    $precios_tamanio = $_POST['precios_tamanio'] ?? [];
    $precios_tamanio_precio = $_POST['precios_tamanio_precio'] ?? [];
    $precios_tamanio_proveedor = $_POST['precios_tamanio_proveedor'] ?? [];

    // Validaciones
    if (!$nombre) $errores[] = "El nombre es obligatorio";
    if ($precio < 0) $errores[] = "El precio debe ser positivo";
    if ($stock < 0) $errores[] = "El stock debe ser positivo";

    // Validar rangos precios escalonados básicos
    for ($i = 0; $i < count($precios_rol); $i++) {
        if (trim($precios_rol[$i]) === '' || !is_numeric($precios_cantidad_min[$i]) || !is_numeric($precios_cantidad_max[$i]) || !is_numeric($precios_precio[$i])) {
            $errores[] = "Todos los campos de precios por cantidad son obligatorios y deben ser numéricos donde corresponde.";
            break;
        }
        if ((int)$precios_cantidad_min[$i] > (int)$precios_cantidad_max[$i]) {
            $errores[] = "La cantidad mínima no puede ser mayor que la máxima en rangos de precios.";
            break;
        }
        if ((float)$precios_precio[$i] < 0) {
            $errores[] = "El precio en los rangos por cantidad debe ser positivo.";
            break;
        }
    }

    // Validar precios por tamaño
    for ($i = 0; $i < count($precios_tamanio); $i++) {
        if (trim($precios_tamanio[$i]) === '' || !is_numeric($precios_tamanio_precio[$i]) || floatval($precios_tamanio_precio[$i]) < 0) {
            $errores[] = "Todos los campos de precios por tamaño son obligatorios y deben ser válidos.";
            break;
        }
    }


    if (empty($errores)) {
        if ($accion === 'editar' && $idProducto > 0) {
            $stmt = $pdo->prepare("UPDATE producto SET nombre=?, descripcion=?, precio=?, precio_proveedor=?, stock=? WHERE id_producto=?");
            $stmt->execute([$nombre, $descripcion, $precio, $precio_proveedor, $stock, $idProducto]);
        } elseif ($accion === 'agregar') {
            $stmt = $pdo->prepare("INSERT INTO producto (nombre, descripcion, precio, precio_proveedor, stock, imagen_url, fecha_creacion) VALUES (?, ?, ?, ?, ?, '', NOW())");
            $stmt->execute([$nombre, $descripcion, $precio, $precio_proveedor, $stock]);
            $idProducto = $pdo->lastInsertId();
            $accion = 'editar';
        }

        // Actualizar colores
        if ($idProducto > 0) {
            $pdo->prepare("DELETE FROM producto_color WHERE id_producto = ?")->execute([$idProducto]);
            $stmtInsertColorProd = $pdo->prepare("INSERT INTO producto_color (id_producto, id_color) VALUES (?, ?)");
            foreach ($coloresSeleccionados as $idColorSel) {
                $stmtInsertColorProd->execute([$idProducto, $idColorSel]);
            }

            // Actualizar precios por cantidad
            $pdo->prepare("DELETE FROM producto_precio_por_cantidad WHERE id_producto = ?")->execute([$idProducto]);
            $stmtInsertPrecioCantidad = $pdo->prepare("INSERT INTO producto_precio_por_cantidad (id_producto, rol, cantidad_min, cantidad_max, precio) VALUES (?, ?, ?, ?, ?)");
            for ($i = 0; $i < count($precios_rol); $i++) {
                $rolP = trim($precios_rol[$i]);
                $cantMin = (int)$precios_cantidad_min[$i];
                $cantMax = (int)$precios_cantidad_max[$i];
                $precioP = (float)$precios_precio[$i];
                if ($rolP !== '') {
                    $stmtInsertPrecioCantidad->execute([$idProducto, $rolP, $cantMin, $cantMax, $precioP]);
                }
            }

            // Actualizar precios por tamaño
            $pdo->prepare("DELETE FROM producto_tamanio WHERE id_producto = ?")->execute([$idProducto]);
            $stmtInsertPrecioTamanio = $pdo->prepare("INSERT INTO producto_tamanio (id_producto, tamanio, precio, precio_proveedor) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($precios_tamanio); $i++) {
                $tamanioTxt = trim($precios_tamanio[$i]);
                $precioT = (float)$precios_tamanio_precio[$i];
                $precioProvT = isset($precios_tamanio_proveedor[$i]) && $precios_tamanio_proveedor[$i] !== '' ? (float)$precios_tamanio_proveedor[$i] : null;
                if ($tamanioTxt !== '') {
                    $stmtInsertPrecioTamanio->execute([$idProducto, $tamanioTxt, $precioT, $precioProvT]);
                }
            }

            // Actualizar forma (solo 1 por producto, insert o update)
            $stmtCheckForma = $pdo->prepare("SELECT COUNT(*) FROM producto_forma WHERE id_producto = ?");
            $stmtCheckForma->execute([$idProducto]);
            $existeForma = $stmtCheckForma->fetchColumn();

            if ($existeForma) {
                $stmtUpdateForma = $pdo->prepare("UPDATE producto_forma SET forma = ? WHERE id_producto = ?");
                $stmtUpdateForma->execute([$formaSeleccionada, $idProducto]);
            } else {
                $stmtInsertForma = $pdo->prepare("INSERT INTO producto_forma (id_producto, forma) VALUES (?, ?)");
                $stmtInsertForma->execute([$idProducto, $formaSeleccionada]);
            }
        }
        $mensaje = "Producto guardado correctamente.";
    }
}

// Eliminar producto (ya estaba, pero borramos también la forma)
if ($accion === 'eliminar' && $idProducto > 0) {
    $pdo->prepare("DELETE FROM producto_imagenes WHERE id_producto = ?")->execute([$idProducto]);
    $pdo->prepare("DELETE FROM producto_beneficios WHERE id_producto = ?")->execute([$idProducto]);
    $pdo->prepare("DELETE FROM producto_color WHERE id_producto = ?")->execute([$idProducto]);
    $pdo->prepare("DELETE FROM producto_precio_por_cantidad WHERE id_producto = ?")->execute([$idProducto]);
    $pdo->prepare("DELETE FROM producto_tamanio WHERE id_producto = ?")->execute([$idProducto]);
    $pdo->prepare("DELETE FROM producto_forma WHERE id_producto = ?")->execute([$idProducto]); // eliminar forma
    $stmt = $pdo->prepare("DELETE FROM producto WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    header('Location: productos.php');
    exit;
}


if ($accion === 'eliminar_forma' && $idProducto > 0 && isset($_GET['forma'])) {
    $formaEliminar = $_GET['forma'];
    $stmt = $pdo->prepare("DELETE FROM producto_forma WHERE id_producto = ? and forma = ?");
    $stmt->execute([$idProducto, $formaEliminar]);
    header("Location: productos.php?accion=editar&id=$idProducto");
    exit;
}


// Obtener datos para formulario / listado
$productoEditar = null;
$imagenesProducto = [];
$beneficios = [];
$coloresProducto = [];
$preciosPorCantidad = [];
$preciosPorTamanio = [];
$formaProducto = null;

if (($accion === 'editar' || $accion === 'ver_beneficios') && $idProducto > 0) {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    $productoEditar = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM producto_imagenes WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    $imagenesProducto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM producto_beneficios WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    $beneficios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $coloresProducto = obtenerColoresProducto($pdo, $idProducto);
    $preciosPorCantidad = obtenerPreciosPorCantidadProducto($pdo, $idProducto);
    $preciosPorTamanio = obtenerPreciosPorTamanioProducto($pdo, $idProducto);
    $formaProducto = obtenerFormaProducto($pdo, $idProducto);
}

// Obtener todos los colores disponibles para checkbox
$stmtColores = $pdo->query("SELECT * FROM color ORDER BY nombre");
$coloresDisponibles = $stmtColores->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las formas distintas para select
$formasDisponibles = obtenerFormasDisponibles($pdo);

// Listar productos
$stmt = $pdo->query("SELECT * FROM producto ORDER BY fecha_creacion DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Admin Productos - Petiqueta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .imagen-edicion {
            max-height: 100px;
            margin-right: 10px;
            border-radius: 8px;
        }

        .contenedor-imagenes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="container pt-4">
        <h1>Productos</h1>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if ($errores): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($accion === 'agregar' || $accion === 'editar'): ?>
            <h2><?= $accion === 'agregar' ? 'Agregar nuevo Producto' : "Editar Producto #$idProducto" ?></h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="guardar_producto" value="1" />
                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($productoEditar['nombre'] ?? '') ?>" class="form-control" />
                </div>
                <div class="mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($productoEditar['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Precio base (cliente)</label>
                    <input type="number" step="0.01" min="0" name="precio" required value="<?= htmlspecialchars($productoEditar['precio'] ?? '') ?>" class="form-control" />
                </div>
                <div class="mb-3">
                    <label>Precio para Proveedor (opcional)</label>
                    <input type="number" step="0.01" min="0" name="precio_proveedor" value="<?= htmlspecialchars($productoEditar['precio_proveedor'] ?? '') ?>" class="form-control" />
                </div>
                <div class="mb-3">
                    <label>Stock</label>
                    <input type="number" min="0" name="stock" required value="<?= htmlspecialchars($productoEditar['stock'] ?? 0) ?>" class="form-control" />
                </div>

                <div class="mb-3">
                    <label>Colores:</label>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalNuevoColor">
                        + Agregar nuevo color
                    </button>
                    <br /><br />
                    <?php foreach ($coloresDisponibles as $color): ?>
                        <label style="margin-right: 15px; cursor:pointer;">
                            <input
                                type="checkbox"
                                name="colores[]"
                                value="<?= $color['id_color'] ?>"
                                <?= in_array($color['id_color'], $coloresProducto) ? 'checked' : '' ?> />
                            <span style="display:inline-block; width: 20px; height: 20px; background: <?= htmlspecialchars($color['codigo_hex']) ?>; border-radius: 50%; vertical-align: middle; margin-left: 4px; border: 1px solid #ccc;"></span>
                            <?= htmlspecialchars($color['nombre']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Mostrar formas actuales -->
                <div class="mb-3">
                    <label>Formas asignadas</label>
                    <ul>
                        <?php foreach ($formasProducto as $forma): ?>
                            <li>
                                <?= htmlspecialchars($forma['forma']) ?>
                                <a href="productos.php?accion=eliminar_forma&id=<?= $idProducto ?>&forma=<?= urlencode($forma['forma']) ?>"
                                    onclick="return confirm('¿Eliminar esta forma?');" class="btn btn-sm btn-danger ms-2">Eliminar</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Input para agregar forma -->
                <div class="mb-3">
                    <label for="nueva_forma" class="form-label">Agregar nueva forma</label>
                    <input type="text" name="nueva_forma" id="nueva_forma" class="form-control" placeholder="Ejemplo: Huella de pájaro" />
                </div>


                <h3>Precios escalonados por cantidad y rol</h3>
                <table id="tablaPreciosCantidad" class="table table-bordered mb-3">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Cantidad mínima</th>
                            <th>Cantidad máxima</th>
                            <th>Precio</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preciosPorCantidad as $precio): ?>
                            <tr>
                                <td><input type="text" name="precios_rol[]" value="<?= htmlspecialchars($precio['rol']) ?>" required></td>
                                <td><input type="number" name="precios_cantidad_min[]" value="<?= $precio['cantidad_min'] ?>" required></td>
                                <td><input type="number" name="precios_cantidad_max[]" value="<?= $precio['cantidad_max'] ?>" required></td>
                                <td><input type="number" step="0.01" name="precios_precio[]" value="<?= $precio['precio'] ?>" required></td>
                                <td><button type="button" class="btn btn-danger btnEliminarFila">Eliminar</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="btnAgregarFila" class="btn btn-secondary mb-3">Agregar rango</button>

                <h3>Precios por tamaño (texto libre, rangos, etc.)</h3>
                <table id="tablaPreciosTamanio" class="table table-bordered mb-3">
                    <thead>
                        <tr>
                            <th>Tamaño</th>
                            <th>Precio cliente</th>
                            <th>Precio proveedor (opcional)</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preciosPorTamanio as $precioT): ?>
                            <tr>
                                <td><input type="text" name="precios_tamanio[]" value="<?= htmlspecialchars($precioT['tamanio']) ?>" required></td>
                                <td><input type="number" step="0.01" min="0" name="precios_tamanio_precio[]" value="<?= $precioT['precio'] ?>" required></td>
                                <td><input type="number" step="0.01" min="0" name="precios_tamanio_proveedor[]" value="<?= $precioT['precio_proveedor'] ?>"></td>
                                <td><button type="button" class="btn btn-danger btnEliminarFila">Eliminar</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="btnAgregarFilaTamanio" class="btn btn-secondary mb-3">Agregar tamaño</button>

                <div class="mb-3">
                    <label>Imágenes actuales</label>
                    <div class="contenedor-imagenes">
                        <?php if (!empty($imagenesProducto)): ?>
                            <?php foreach ($imagenesProducto as $img): ?>
                                <img src="https://petiqueta.uy/<?= htmlspecialchars($img['imagen_url']) ?>" alt="Imagen producto" class="imagen-edicion" />
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay imágenes cargadas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Subir nuevas imágenes (puedes subir varias)</label>
                    <input type="file" name="imagenes[]" multiple accept="image/*" class="form-control" />
                </div>

                <button type="submit" class="btn btn-primary">Guardar producto</button>
                <a href="productos.php" class="btn btn-secondary">Cancelar</a>
            </form>

            <?php if ($accion === 'editar'): ?>
                <hr />
                <h3>Beneficios del Producto</h3>
                <a href="productos.php?accion=ver_beneficios&id=<?= $idProducto ?>" class="btn btn-info mb-3">Ver Beneficios</a>
            <?php endif; ?>

        <?php elseif ($accion === 'ver_beneficios' && $idProducto > 0): ?>

            <h2>Beneficios para Producto #<?= $idProducto ?>: <?= htmlspecialchars($productoEditar['nombre']) ?></h2>

            <a href="productos.php?accion=editar&id=<?= $idProducto ?>" class="btn btn-secondary mb-3">Volver a Producto</a>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Imagen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($beneficios): ?>
                        <?php foreach ($beneficios as $ben): ?>
                            <tr>
                                <td><?= $ben['id'] ?></td>
                                <td><?= htmlspecialchars($ben['titulo']) ?></td>
                                <td><?= nl2br(htmlspecialchars($ben['descripcion'])) ?></td>
                                <td>
                                    <?php if ($ben['imagen_url']): ?>
                                        <img src="<?= htmlspecialchars($ben['imagen_url']) ?>" alt="Imagen Beneficio" style="max-height: 80px;" />
                                    <?php else: ?>
                                        Sin imagen
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="productos.php?accion=ver_beneficios&id=<?= $idProducto ?>&delete_beneficio=<?= $ben['id'] ?>"
                                        onclick="return confirm('¿Eliminar beneficio?');" class="btn btn-danger btn-sm">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay beneficios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Agregar Beneficio</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="guardar_beneficio" value="1" />
                <div class="mb-3">
                    <label>Título</label>
                    <input type="text" name="titulo" required class="form-control" />
                </div>
                <div class="mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion_beneficio" rows="3" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Imagen (opcional)</label>
                    <input type="file" name="imagen_beneficio" accept="image/*" class="form-control" />
                </div>
                <button type="submit" class="btn btn-success">Agregar Beneficio</button>
            </form>

        <?php else: ?>

            <h2>Lista de Productos</h2>
            <a href="productos.php?accion=agregar" class="btn btn-success mb-3">Agregar Producto</a>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio base</th>
                        <th>Precio proveedor</th>
                        <th>Stock</th>
                        <th>Fecha creación</th>
                        <th>Colores</th>
                        <th>Forma</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT c.nombre, c.codigo_hex
                            FROM color c
                            INNER JOIN producto_color pc ON c.id_color = pc.id_color
                            WHERE pc.id_producto = ?
                        ");
                        $stmt->execute([$p['id_producto']]);
                        $coloresLista = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Obtener forma de este producto para mostrar en la tabla
                        $stmtForma = $pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ?");
                        $stmtForma->execute([$p['id_producto']]);
                        $formaProductoListado = $stmtForma->fetchColumn();
                        ?>
                        <tr>
                            <td><?= $p['id_producto'] ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td>$<?= number_format($p['precio'], 2) ?></td>
                            <td>
                                <?php
                                if ($p['precio_proveedor'] !== NULL) {
                                    echo '$' . number_format($p['precio_proveedor'], 2);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td><?= $p['stock'] ?></td>
                            <td><?= $p['fecha_creacion'] ?></td>
                            <td>
                                <?php foreach ($coloresLista as $color): ?>
                                    <span title="<?= htmlspecialchars($color['nombre']) ?>" style="display:inline-block; width: 20px; height: 20px; background: <?= htmlspecialchars($color['codigo_hex']) ?>; border-radius: 50%; margin-right: 5px; border: 1px solid #ccc;"></span>
                                <?php endforeach; ?>
                            </td>
                            <td><?= htmlspecialchars($formaProductoListado ?: '-') ?></td>
                            <td>
                                <a href="productos.php?accion=editar&id=<?= $p['id_producto'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="productos.php?accion=ver_beneficios&id=<?= $p['id_producto'] ?>" class="btn btn-info btn-sm">Beneficios</a>
                                <a href="productos.php?accion=eliminar&id=<?= $p['id_producto'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este producto?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay productos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </main>

    <!-- Modal para agregar nuevo color -->
    <div class="modal fade" id="modalNuevoColor" tabindex="-1" aria-labelledby="modalNuevoColorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <input type="hidden" name="guardar_color" value="1" />
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoColorLabel">Agregar Nuevo Color</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre_color" class="form-label">Nombre del color</label>
                        <input type="text" id="nombre_color" name="nombre_color" required class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="codigo_hex" class="form-label">Código HEX</label>
                        <input type="text" id="codigo_hex" name="codigo_hex" placeholder="#a1b2c3" pattern="^#([a-fA-F0-9]{6})$" required class="form-control" />
                        <div class="form-text">Ejemplo: #a1b2c3</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar color</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Agregar filas tabla precios por cantidad
        document.getElementById('btnAgregarFila').addEventListener('click', () => {
            const tbody = document.querySelector('#tablaPreciosCantidad tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="precios_rol[]" required></td>
                <td><input type="number" name="precios_cantidad_min[]" required></td>
                <td><input type="number" name="precios_cantidad_max[]" required></td>
                <td><input type="number" step="0.01" name="precios_precio[]" required></td>
                <td><button type="button" class="btn btn-danger btnEliminarFila">Eliminar</button></td>
            `;
            tbody.appendChild(tr);
        });

        // Agregar filas tabla precios por tamaño
        document.getElementById('btnAgregarFilaTamanio').addEventListener('click', () => {
            const tbody = document.querySelector('#tablaPreciosTamanio tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="precios_tamanio[]" required></td>
                <td><input type="number" step="0.01" min="0" name="precios_tamanio_precio[]" required></td>
                <td><input type="number" step="0.01" min="0" name="precios_tamanio_proveedor[]"></td>
                <td><button type="button" class="btn btn-danger btnEliminarFila">Eliminar</button></td>
            `;
            tbody.appendChild(tr);
        });

        // Eliminar fila de tablas (precios)
        document.querySelectorAll('#tablaPreciosCantidad, #tablaPreciosTamanio').forEach(tabla => {
            tabla.addEventListener('click', e => {
                if (e.target.classList.contains('btnEliminarFila')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>

</body>

</html>