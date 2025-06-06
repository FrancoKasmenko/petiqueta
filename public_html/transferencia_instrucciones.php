<?php
session_start();
require '../app/core/config.php';

$id_orden = $_GET['id_orden'] ?? null;
$orden = null;
$detalles = [];

if ($id_orden) {
    $stmt = $pdo->prepare("SELECT * FROM orden WHERE id_orden = ?");
    $stmt->execute([$id_orden]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($orden) {
        $stmt_detalle = $pdo->prepare("SELECT od.*, p.nombre FROM orden_detalle od JOIN producto p ON od.id_producto = p.id_producto WHERE id_orden = ?");
        $stmt_detalle->execute([$id_orden]);
        $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Petiqueta - Instrucciones para Transferencia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap"
    rel="stylesheet" />
  <style>
    body {
      font-family: "Poppins", sans-serif !important;
      background-color: #fcf4e7;
      /* var(--beige) */
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --marron: #7a5c39;
      --caramelo: #c89e6a;
      --beige: #fcf4e7;
      --blanco-caldo: #fffaf3;
    }
  </style>
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans">

<?php include '../templates/navbar.php'; ?>

<main class="max-w-5xl mx-auto p-6 sm:p-12">

<?php if (!$id_orden): ?>
  <h1 class="text-4xl font-extrabold mb-6" style="color: var(--marron);">
    No se especificó la orden.
  </h1>
  <p class="text-[var(--marron)] mb-6">Por favor, accedé a esta página desde el link que recibiste tras realizar tu compra.</p>
  <a href="https://petiqueta.uy" class="inline-block bg-[var(--caramelo)] text-white py-3 px-6 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
    Volver al inicio
  </a>

<?php elseif (!$orden): ?>
  <h1 class="text-4xl font-extrabold mb-6" style="color: var(--marron);">
    Orden no encontrada.
  </h1>
  <p class="text-[var(--marron)] mb-6">Por favor, revisá que el enlace sea correcto o contactá con soporte.</p>
  <a href="https://petiqueta.uy" class="inline-block bg-[var(--caramelo)] text-white py-3 px-6 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
    Volver al inicio
  </a>

<?php else: ?>
  <h1 class="text-4xl font-extrabold mb-6" style="color: var(--marron);">
    Instrucciones para realizar la transferencia bancaria
  </h1>

  <p class="mb-6 text-[var(--marron)] text-lg">
    Gracias por tu compra, <b><?= htmlspecialchars($orden['email']) ?></b>.
  </p>

  <section class="mb-10 bg-[var(--blanco-caldo)] p-6 rounded-xl shadow-md border border-[var(--caramelo)]">
    <h2 class="text-2xl font-semibold mb-4" style="color: var(--caramelo);">
      Datos para la transferencia
    </h2>
    <ul class="list-disc list-inside text-[var(--marron)] text-base space-y-1">
      <li><strong>Banco:</strong> ITAÚ</li>
      <li><strong>Tipo:</strong> Caja de Ahorro $</li>
      <li><strong>N. de cuenta:</strong> 9846490</li>
      <li><strong>Titular:</strong> Franco Kasmenko</li>
    </ul>
  </section>

  <section class="mb-10">
    <h2 class="text-2xl font-semibold mb-4" style="color: var(--marron);">
      Resumen de tu pedido (Orden #<?= htmlspecialchars($id_orden) ?>)
    </h2>

    <table class="w-full border-collapse border border-[var(--caramelo)] rounded-lg shadow-sm overflow-hidden">
      <thead class="bg-[var(--caramelo)] text-white">
        <tr>
          <th class="py-3 px-4 text-left">Producto</th>
          <th class="py-3 px-4 text-center">Cantidad</th>
          <th class="py-3 px-4 text-right">Precio Unitario</th>
          <th class="py-3 px-4 text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($detalles as $item): ?>
          <tr class="even:bg-[var(--blanco-caldo)]">
            <td class="py-2 px-4 text-[var(--marron)]"><?= htmlspecialchars($item['nombre']) ?></td>
            <td class="py-2 px-4 text-center text-[var(--marron)]"><?= $item['cantidad'] ?></td>
            <td class="py-2 px-4 text-right text-[var(--marron)]">$<?= number_format($item['precio_unitario'], 2, ',', '.') ?> UYU</td>
            <td class="py-2 px-4 text-right text-[var(--marron)]">$<?= number_format($item['precio_unitario'] * $item['cantidad'], 2, ',', '.') ?> UYU</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="bg-[var(--caramelo)] text-white font-bold">
          <td colspan="3" class="py-3 px-4 text-right">Total</td>
          <td class="py-3 px-4 text-right">$<?= number_format($orden['total'], 2, ',', '.') ?> UYU</td>
        </tr>
      </tfoot>
    </table>
  </section>

  <section class="mb-12 bg-[var(--blanco-caldo)] p-6 rounded-xl shadow-md border border-[var(--caramelo)] max-w-md mx-auto">
    <h2 class="text-2xl font-semibold mb-4" style="color: var(--caramelo);">
      Sube tu comprobante de transferencia
    </h2>

    <form action="procesar_transferencia" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
      <input type="hidden" name="id_orden" value="<?= htmlspecialchars($id_orden) ?>" />

      <label for="comprobante" class="font-semibold text-[var(--marron)]">Comprobante (imagen o PDF):</label>
      <input type="file" id="comprobante" name="comprobante" accept="image/*,application/pdf" required
             class="border border-[var(--caramelo)] rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[var(--caramelo)]" />

      <button type="submit"
              class="bg-[var(--caramelo)] text-white py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
        Enviar comprobante
      </button>
    </form>
  </section>

  <p class="text-center text-[var(--marron)] text-sm max-w-md mx-auto">
    Una vez que recibamos y verifiquemos tu comprobante, procederemos con la confirmación y envío de tu pedido.
  </p>

<?php endif; ?>

</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
