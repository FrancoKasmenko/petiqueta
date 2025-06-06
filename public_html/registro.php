<?php
session_start();

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

unset($_SESSION['error'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Registro - Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  <style>
    body {
      font-family: "Poppins", sans-serif !important;
      background-color: #fcf4e7;
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

    /* MOBILE RESPONSIVE */
    @media (max-width: 600px) {
      .max-w-xl {
        max-width: 98vw !important;
        padding: 12px !important;
        margin: 8px !important;
        box-sizing: border-box;
      }

      .grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
      }

      label[style*="grid-column: span 2"] {
        width: 100% !important;
        grid-column: auto !important;
      }

      .bg-\[var\(--blanco-caldo\)\] {
        border-radius: 16px !important;
        padding: 14px !important;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08) !important;
      }

      .text-3xl {
        font-size: 1.5rem !important;
      }
      .p-3, .py-3, .px-3 {
        padding: 9px 12px !important;
      }
    }
  </style>
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans">

  <?php include '../templates/navbar.php'; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <main class="max-w-xl mx-auto p-6 mt-12 mb-12 bg-[var(--blanco-caldo)] rounded-3xl shadow-lg">

    <h1 class="text-3xl font-extrabold text-center mb-8" style="color: var(--marron);">Registro</h1>

    <?php if ($error): ?>
      <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded border border-red-400">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="mb-4 p-3 text-sm text-green-700 bg-green-100 rounded border border-green-400">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form action="registro_procesar.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" novalidate>

      <label class="flex flex-col text-[var(--marron)] font-semibold md:col-span-2" style="grid-column: span 2">
        Nombre completo
        <input type="text" name="nombre" placeholder="Tu nombre completo" required
          value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Correo electrónico
        <input type="email" name="email" placeholder="ejemplo@correo.com" required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Teléfono
        <input type="tel" name="telefono" placeholder="091 234 567" required
          value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Dirección
        <input type="text" name="direccion" placeholder="Tu dirección (opcional)"
          value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Departamento
        <input type="text" name="departamento" placeholder="Departamento (opcional)"
          value="<?= htmlspecialchars($_POST['departamento'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Barrio
        <input type="text" name="barrio" placeholder="Tu barrio (opcional)"
          value="<?= htmlspecialchars($_POST['barrio'] ?? '') ?>"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold" style="grid-column: span 2">
        Código Petiqueta
        <input type="text" name="codigo_mascotag" placeholder="Ejemplo: A1B2C3" required maxlength="6"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" style="grid-column: span 2"/>
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Contraseña
        <input type="password" name="contrasena" placeholder="Tu contraseña" required minlength="8"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="flex flex-col text-[var(--marron)] font-semibold">
        Confirmar contraseña
        <input type="password" name="contrasena_confirmar" placeholder="Repite tu contraseña" required minlength="8"
          class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>
      <button
        type="submit"
        class=" bg-[var(--caramelo)] text-white py-3 p-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition block mx-auto max-w-xs" style="grid-column: span 2;">
        Registrarme
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-[var(--marron)]">
      ¿Ya tenés cuenta? <a href="login" class="text-[var(--caramelo)] hover:underline">Iniciar sesión</a>
    </p>
  </main>

  <?php include '../templates/footer.php'; ?>

</body>
</html>
