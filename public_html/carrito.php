<?php
session_start();
require_once __DIR__ . '/../app/core/config.php';

$colorMarron = '#7a5c39';
$colorCaramelo = '#c89e6a';
$colorBeige = '#fcf4e7';
$colorBlancoCaldo = '#fffaf3';

if (!isset($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

$rolUsuario = $_SESSION['user_rol'] ?? 'cliente';

function obtenerProducto($pdo, $id) {
  $stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = ?");
  $stmt->execute([$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}
function obtenerPrecioSegunTamanio(PDO $pdo, int $idProducto, string $tamanio): float {
  $stmt = $pdo->prepare("SELECT precio FROM producto_tamanio WHERE id_producto = ? AND tamanio = ?");
  $stmt->execute([$idProducto, $tamanio]);
  $precioTamanio = $stmt->fetchColumn();
  if ($precioTamanio !== false) {
    return floatval($precioTamanio);
  }
  $stmtBase = $pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
  $stmtBase->execute([$idProducto]);
  $precioBase = $stmtBase->fetchColumn();
  return $precioBase !== false ? floatval($precioBase) : 0;
}
function obtenerPrecioSegunRolYCantidad(PDO $pdo, int $idProducto, string $rol, int $cantidad): float {
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
$total = 0;
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <title>Carrito - Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
    
  <style>
    :root {
      --marron: <?= $colorMarron ?>;
      --caramelo: <?= $colorCaramelo ?>;
      --beige: <?= $colorBeige ?>;
      --blanco-caldo: <?= $colorBlancoCaldo ?>;
    }
    body { font-family: 'Poppins', sans-serif; background-color: var(--beige); color: var(--marron);}
  </style>

</head>
<body class="min-h-screen flex flex-col">

  <?php include __DIR__ . '/../templates/navbar.php'; ?>

<main class="flex-grow max-w-7xl mx-auto px-2 sm:px-4 py-6 sm:py-12">
  <h1 class="text-3xl sm:text-4xl font-extrabold mb-6 sm:mb-10 text-[var(--caramelo)] text-center sm:text-left">
    Tu carrito de compras
  </h1>

  <?php if (empty($carrito)): ?>
    <p class="text-lg font-semibold text-center text-gray-600">Tu carrito está vacío.</p>
  <?php else: ?>
    <!-- DESKTOP TABLE -->
    <div class="hidden md:block w-full overflow-x-auto rounded-3xl shadow-sm bg-white mb-8">
      <table class="min-w-[700px] w-full text-sm sm:text-base rounded-3xl overflow-hidden">
        <thead class="bg-[var(--marron)] text-white">
          <tr>
            <th class="py-3 px-2 sm:px-4 text-left whitespace-nowrap">Producto</th>
            <th class="py-3 px-2 sm:px-4 text-left whitespace-nowrap">Tamaño</th>
            <th class="py-3 px-2 sm:px-4 text-left whitespace-nowrap">Forma</th>
            <th class="py-3 px-2 sm:px-4 text-left whitespace-nowrap">Color</th>
            <th class="py-3 px-2 sm:px-4 text-right whitespace-nowrap">Precio</th>
            <th class="py-3 px-2 sm:px-4 text-center whitespace-nowrap">Cantidad</th>
            <th class="py-3 px-2 sm:px-4 text-right whitespace-nowrap">Subtotal</th>
            <th class="py-3 px-2 sm:px-4 text-center whitespace-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $index => $item):
            $producto = obtenerProducto($pdo, $item['id_producto']);
            if (!$producto) continue;
            $cantidad = $item['cantidad'] ?? 1;
            if ($rolUsuario === 'proveedor') {
              $precio = obtenerPrecioSegunRolYCantidad($pdo, $item['id_producto'], $rolUsuario, $cantidad);
            } else {
              $tamanio = $item['tamanio'] ?? 'No especificado';
              $precio = obtenerPrecioSegunTamanio($pdo, $item['id_producto'], $tamanio);
            }
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
          ?>
            <tr class="border-b border-[var(--caramelo)]">
              <td class="py-3 px-2 sm:px-4 flex items-center gap-2 sm:gap-4 min-w-[150px]">
                <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-[var(--caramelo)]" />
                <span class="truncate"><?= htmlspecialchars($producto['nombre']) ?></span>
              </td>
              <td class="py-3 px-2 sm:px-4"><?= htmlspecialchars($item['tamanio'] ?? 'No especificado') ?></td>
              <td class="py-3 px-2 sm:px-4"><?= htmlspecialchars($item['forma'] ?? 'No especificado') ?></td>
              <td class="py-3 px-2 sm:px-4 flex items-center gap-1 sm:gap-2">
                <?php if (!empty($item['colores_hex']) && is_array($item['colores_hex'])): ?>
                  <?php foreach ($item['colores_hex'] as $hex): ?>
                    <span style="display:inline-block; width: 16px; height: 16px; background: <?= htmlspecialchars($hex) ?>; border-radius: 50%; border: 1px solid #ccc;"></span>
                  <?php endforeach; ?>
                <?php endif; ?>
                <span><?= htmlspecialchars($item['color'] ?? 'No especificado') ?></span>
              </td>
              <td class="py-3 px-2 sm:px-4 text-right">$<?= number_format($precio, 2) ?></td>
              <td class="py-3 px-2 sm:px-4 text-center">
                  <span class="px-2"><?= $cantidad ?></span>
              </td>
              <td class="py-3 px-2 sm:px-4 text-right font-semibold">$<?= number_format($subtotal, 2) ?></td>
              <td class="py-3 px-2 sm:px-4 text-center">
                <form method="POST" action="productos/carrito_eliminar" class="inline-block">
                  <input type="hidden" name="index" value="<?= $index ?>" />
                  <button type="submit" class="bg-[var(--caramelo)] hover:bg-[var(--marron)] text-white py-1 px-3 rounded-md font-semibold transition">
                    Eliminar
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr class="bg-[var(--caramelo)] text-white font-bold">
            <td colspan="6" class="py-3 px-2 sm:px-4 text-right">Total:</td>
            <td colspan="2" class="py-3 px-2 sm:px-4 text-right">$<?= number_format($total, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="md:hidden flex flex-col gap-5 mb-8">
      <?php foreach ($carrito as $index => $item):
        $producto = obtenerProducto($pdo, $item['id_producto']);
        if (!$producto) continue;
        $cantidad = $item['cantidad'] ?? 1;
        if ($rolUsuario === 'proveedor') {
          $precio = obtenerPrecioSegunRolYCantidad($pdo, $item['id_producto'], $rolUsuario, $cantidad);
        } else {
          $tamanio = $item['tamanio'] ?? 'No especificado';
          $precio = obtenerPrecioSegunTamanio($pdo, $item['id_producto'], $tamanio);
        }
        $subtotal = $precio * $cantidad;
      ?>
        <div class="bg-white rounded-3xl shadow-md p-5 flex flex-col items-center relative">
          <form method="POST" action="productos/carrito_eliminar" class="absolute right-4 top-4">
            <input type="hidden" name="index" value="<?= $index ?>" />
            <button type="submit" class="w-8 h-8 bg-[var(--caramelo)] text-white rounded-full flex items-center justify-center font-bold text-lg shadow hover:bg-[var(--marron)] transition">
              ×
            </button>
          </form>
          <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-24 h-24 object-cover rounded-lg border border-[var(--caramelo)] mb-2" />
          <div class="font-semibold text-center mb-1"><?= htmlspecialchars($producto['nombre']) ?></div>
          <div class="flex flex-wrap justify-center text-xs mb-2 gap-x-2">
            <span><b>Tamaño:</b> <?= htmlspecialchars($item['tamanio'] ?? '-') ?></span>
            <span><b>Forma:</b> <?= htmlspecialchars($item['forma'] ?? '-') ?></span>
            <span><b>Color:</b>
              <?php if (!empty($item['colores_hex']) && is_array($item['colores_hex'])): ?>
                <?php foreach ($item['colores_hex'] as $hex): ?>
                  <span style="display:inline-block; width: 14px; height: 14px; background: <?= htmlspecialchars($hex) ?>; border-radius: 50%; border: 1px solid #ccc; margin-right:2px;"></span>
                <?php endforeach; ?>
              <?php endif; ?>
              <?= htmlspecialchars($item['color'] ?? '-') ?>
            </span>
          </div>
          <div class="flex items-center justify-center gap-4 my-2">
              <span class="px-3 text-lg"><?= $cantidad ?></span>
          </div>
          <div class="text-base font-bold text-[var(--caramelo)]">$<?= number_format($subtotal, 2) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="bg-[var(--caramelo)] text-white font-bold rounded-2xl p-4 text-center text-lg shadow mt-4">
        Total: $<?= number_format($total, 2) ?>
      </div>
    </div>

    <div class="mt-8 flex flex-col items-center sm:items-end">
      <a href="finalizar_compra" class="bg-[var(--caramelo)] hover:bg-[var(--marron)] text-white px-8 py-3 rounded-lg font-semibold transition w-full sm:w-auto text-center">
        Finalizar Compra
      </a>
    </div>
  <?php endif; ?>
</main>


  <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
