

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <title>Sobre Nosotros | Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  <style>
    :root {
      --marron: #7a583a;
      --caramelo: #b2884a;
      --beige: #fcf4e7;
      --blanco-caldo: #fffaf3;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--beige);
      color: var(--marron);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <?php include '../templates/navbar.php'; ?>

  <main class="flex-grow max-w-6xl mx-auto px-6 py-12">

    <h1 class="text-5xl font-extrabold text-[var(--caramelo)] mb-8 text-center">Sobre Nosotros</h1>

    <section class="space-y-8 max-w-4xl mx-auto text-lg leading-relaxed">

      <p>
        En <strong>Petiqueta</strong> nos apasiona la tecnología y el bienestar de tu mascota. Nuestra misión es ofrecer la manera más inteligente y segura para proteger a tu compañero con soluciones innovadoras.
      </p>

      <p>
        Nuestro equipo está formado por profesionales con experiencia en tecnología, diseño y cuidado animal, comprometidos en crear productos de alta calidad y fácil uso para ti y tu mascota.
      </p>

      <p>
        Creemos que la tranquilidad de saber que tu mascota siempre estará identificada y protegida es fundamental. Por eso, desarrollamos tags inteligentes que integran códigos QR con información esencial accesible en todo momento.
      </p>

      <p>
        Gracias por confiar en Petiqueta. Estamos aquí para acompañarte en cada paso del camino junto a tu mascota.
      </p>

    </section>

  </main>

  <?php include '../templates/footer.php'; ?>

</body>
</html>
/html>
