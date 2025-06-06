<?php
// contacto.php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Contacto | PETIQUETA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
</head>
  <style>
    :root {
      --marron: #7a583a;
      --caramelo: #b2884a;
      --beige: #fcf4e7;
      --blanco-caldo: #fffaf3;
    }
    body {
      font-family: 'Poppins', sans-serif !important;
      background-color: var(--beige);
      color: var(--marron);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    main {
      max-width: 900px;
      margin: 2rem auto 5rem auto;
      padding: 0 1rem;
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--caramelo);
      text-align: center;
    }
    section.faq-item {
      margin-bottom: 2rem;
      border-bottom: 1px solid var(--caramelo);
      padding-bottom: 1rem;
    }
    section.faq-item:last-child {
      border-bottom: none;
    }
    h2 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      cursor: pointer;
    }
    p.answer {
      font-size: 1.1rem;
      line-height: 1.5;
      margin-left: 1rem;
      color: #444;
    }
    h2:hover {
      color: var(--marron);
      text-decoration: underline;
    }
  </style>
<body class="bg-[var(--beige)] min-h-screen font-sans flex flex-col">

  <?php include '../templates/navbar.php'; ?>
<?php if (isset($_GET['enviado'])): ?>
  <div style="background:#b89154;color:white;padding:1em;text-align:center;border-radius:10px;margin-bottom:2em;">
    ¡Gracias! Tu mensaje fue enviado correctamente.
  </div>
<?php elseif (isset($_GET['error'])): ?>
  <div style="background:#a33;color:white;padding:1em;text-align:center;border-radius:10px;margin-bottom:2em;">
    Ocurrió un error al enviar tu mensaje. Por favor, intentá de nuevo.
  </div>
<?php endif; ?>

  <main class="flex-grow max-w-4xl mx-auto p-8 bg-[var(--blanco-caldo)] rounded-3xl shadow-md mt-12 mb-12">

    <h1 class="text-4xl font-extrabold mb-8 text-[var(--marron)] text-center">Contacto</h1>

    <p class="mb-8 text-center text-[var(--marron)]">
      ¿Tenés alguna duda, consulta o querés contactarnos? Completá el formulario y te responderemos a la brevedad.
    </p>

    <form action="procesar_contacto.php" method="POST" class="space-y-6 max-w-xl mx-auto">

      <label class="block">
        <span class="text-[var(--marron)] font-semibold">Nombre</span>
        <input type="text" name="nombre" required placeholder="Tu nombre" 
          class="w-full p-3 mt-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="block">
        <span class="text-[var(--marron)] font-semibold">Correo electrónico</span>
        <input type="email" name="email" required placeholder="Tu correo electrónico"
          class="w-full p-3 mt-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>
      
      <label class="block">
        <span class="text-[var(--marron)] font-semibold">Teléfono (opcional)</span>
        <input type="tel" name="telefono" placeholder="Tu teléfono"
          class="w-full p-3 mt-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="block">
        <span class="text-[var(--marron)] font-semibold">Asunto</span>
        <input type="text" name="asunto" required placeholder="Asunto de tu mensaje"
          class="w-full p-3 mt-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
      </label>

      <label class="block">
        <span class="text-[var(--marron)] font-semibold">Mensaje</span>
        <textarea name="mensaje" rows="5" required placeholder="Escribí tu mensaje aquí"
          class="w-full p-3 mt-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none"></textarea>
      </label>

      <button type="submit" 
        class="w-full bg-[var(--caramelo)] text-white py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
        Enviar mensaje
      </button>

    </form>

  </main>

  <?php include '../templates/footer.php'; ?>

</body>

</html>
