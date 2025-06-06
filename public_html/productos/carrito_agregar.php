<?php
session_start();
require_once __DIR__ . '/../../app/core/config.php'; // Ajusta ruta

define('BASE_URL', 'https://petiqueta.uy/');

$rolUsuario = $_SESSION['user_rol'] ?? 'cliente';

$idProducto = $_POST['id_producto'] ?? null;
$tamanio = $_POST['tamanio'] ?? 'No especificado';
$coloresIds = $_POST['colores'] ?? [];
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$formaSeleccionada = $_POST['forma'] ?? '';

$formaNombre = '';
if ($formaSeleccionada) {
    $stmtForma = $pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ? AND forma = ?");
    $stmtForma->execute([$idProducto, $formaSeleccionada]);
    $formaNombre = $stmtForma->fetchColumn() ?: '';
}

// Validaciones básicas
if (!$idProducto || !is_numeric($idProducto) || $cantidad < 1 || empty($coloresIds)) {
    header('Location: ' . BASE_URL . 'productos/producto?id=' . $idProducto);
    exit;
}

// Obtener producto base
$stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = ?");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header('Location: ' . BASE_URL . 'productos');
    exit;
}

// Función para precio proveedores según cantidad (con descuentos por cantidad)
function obtenerPrecioSegunRolYCantidad(PDO $pdo, int $idProducto, string $rol, int $cantidad): float
{
    $stmt = $pdo->prepare("SELECT precio, precio_proveedor FROM producto WHERE id_producto = ?");
    $stmt->execute([$idProducto]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) return 0;

    $precioCliente = floatval($prod['precio']);
    $precioProveedorBase = floatval($prod['precio_proveedor']);

    if ($rol !== 'proveedor' || $precioProveedorBase <= 0) {
        return $precioCliente;
    }

    $stmt = $pdo->prepare("SELECT precio FROM producto_precio_por_cantidad WHERE id_producto = ? AND cantidad_min <= ? AND cantidad_max >= ? ORDER BY cantidad_min DESC LIMIT 1");
    $stmt->execute([$idProducto, $cantidad, $cantidad]);
    $descuento = $stmt->fetchColumn();

    if ($descuento !== false && floatval($descuento) > 0) {
        return floatval($descuento);
    }

    return $precioProveedorBase;
}

// Nueva función para clientes según tamaño, con tabla producto_tamanio
function obtenerPrecioSegunTamanio(PDO $pdo, int $idProducto, string $tamanio): float
{
    // Buscar precio específico para tamaño
    $stmt = $pdo->prepare("SELECT precio FROM producto_tamanio WHERE id_producto = ? AND tamanio = ?");
    $stmt->execute([$idProducto, $tamanio]);
    $precio = $stmt->fetchColumn();

    if ($precio !== false) {
        return floatval($precio);
    }

    // Si no hay precio por tamaño, regresar precio base producto
    $stmtBase = $pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
    $stmtBase->execute([$idProducto]);
    $precioBase = $stmtBase->fetchColumn();

    return $precioBase !== false ? floatval($precioBase) : 0;
}

// Según rol, obtener precio final
if ($rolUsuario === 'proveedor') {
    $precio = obtenerPrecioSegunRolYCantidad($pdo, (int)$idProducto, $rolUsuario, $cantidad);
} else {
    $precio = obtenerPrecioSegunTamanio($pdo, (int)$idProducto, $tamanio);
}

// Obtener nombres y códigos HEX de colores seleccionados
$coloresNombre = [];
$coloresHex = [];
$stmtColor = $pdo->prepare("SELECT nombre, codigo_hex FROM color WHERE id_color = ?");
foreach ($coloresIds as $idColor) {
    if (!is_numeric($idColor)) continue;
    $stmtColor->execute([$idColor]);
    $color = $stmtColor->fetch(PDO::FETCH_ASSOC);
    if ($color) {
        $coloresNombre[] = $color['nombre'];
        $coloresHex[] = $color['codigo_hex'];
    }
}
$colorStr = implode(', ', $coloresNombre);

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Buscar ítem igual (mismo producto + tamaño + colores)
$encontro = false;
foreach ($_SESSION['carrito'] as &$item) {
    if (
        $item['id_producto'] == $idProducto &&
        $item['tamanio'] === $tamanio &&
        $item['color'] === $colorStr &&
        $item['forma'] === $formaNombre // o $formasStr
    ) {
        $item['cantidad'] += $cantidad;
        $encontro = true;
        break;
    }
}
unset($item);

if (!$encontro) {
    $_SESSION['carrito'][] = [
        'id_producto' => (int)$idProducto,
        'tamanio' => $tamanio,
        'color' => $colorStr,
        'colores_hex' => $coloresHex,
        'cantidad' => $cantidad,
        'nombre' => $producto['nombre'],
        'precio' => $precio,
        'imagen_url' => $producto['imagen_url'],
        'forma' => $formaNombre, // o $formasStr si es array
    ];
}

// Redirigir a carrito
header('Location: ' . BASE_URL . 'carrito');
exit;

exit;
