<?php
session_start();
unset($_SESSION['carrito']);

$colorMarron = '#7a5c39';
$colorCaramelo = '#c89e6a';
$colorBeige = '#fcf4e7';
$colorBlancoCaldo = '#fffaf3';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>¡Compra exitosa! | Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="/assets/img/icons/favicon.png">
  <style>
    :root {
      --marron: <?= $colorMarron ?>;
      --caramelo: <?= $colorCaramelo ?>;
      --beige: <?= $colorBeige ?>;
      --blanco-caldo: <?= $colorBlancoCaldo ?>;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--beige);
      color: var(--marron);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .icon-check {
      display: inline-block;
      background: linear-gradient(135deg, var(--caramelo), var(--marron));
      border-radius: 50%;
      width: 90px;
      height: 90px;
      margin-bottom: 1.2rem;
      box-shadow: 0 3px 18px 0 #0001;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon-check svg {
      width: 50px;
      height: 50px;
      color: #fff;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <?php include __DIR__ . '/../templates/navbar.php'; ?>

  <main class="flex-grow flex flex-col items-center justify-center py-10 px-4 bg-[var(--blanco-caldo)]" style="min-height:60vh;">
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-lg p-8 flex flex-col items-center text-center border border-[var(--caramelo)]">
      <span class="icon-check mb-4">
        <svg fill="none" viewBox="0 0 50 50">
          <circle cx="25" cy="25" r="24" stroke="white" stroke-width="2"/>
          <path d="M16 26.5l8 8 10-15" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <h1 class="text-3xl sm:text-4xl font-extrabold mb-3 text-[var(--caramelo)]">¡Compra exitosa!</h1>
      <p class="text-lg mb-3 text-[var(--marron)]">
        ¡Gracias por tu compra!<br>
        En breve recibirás un correo con la confirmación y los detalles de tu pedido.
      </p>
      <p class="text-sm text-gray-600 mb-7">
        Si no ves el correo en unos minutos, revisá la carpeta de <b>spam</b> o <b>promociones</b>.<br>
        Para dudas, escribinos a <a href="mailto:contacto@petiqueta.uy" class="text-[var(--caramelo)] hover:underline">contacto@petiqueta.uy</a>
      </p>
      <a href="https://petiqueta.uy" class="bg-[var(--caramelo)] hover:bg-[var(--marron)] text-white font-semibold px-8 py-3 rounded-lg shadow-md transition mb-2 block w-full sm:w-auto">
        Volver a la tienda
      </a>
    </div>
  </main>

  <?php include __DIR__ . '/../templates/footer.php'; ?>

</body>
</html>
