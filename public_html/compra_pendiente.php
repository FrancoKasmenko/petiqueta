<?php
session_start();

$colorMarron = '#7a5c39';
$colorCaramelo = '#c89e6a';
$colorBeige = '#fcf4e7';
$colorBlancoCaldo = '#fffaf3';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Compra pendiente | Petiqueta</title>
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
    .icon-wait {
      display: inline-block;
      background: linear-gradient(135deg, var(--caramelo), var(--marron));
      border-radius: 50%;
      width: 90px;
      height: 90px;
      margin-bottom: 1.2rem;
      box-shadow: 0 3px 18px 0 #0002;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon-wait svg {
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
      <span class="icon-wait mb-4">
        <svg fill="none" viewBox="0 0 50 50">
          <circle cx="25" cy="25" r="24" stroke="white" stroke-width="2"/>
          <path d="M25 13v12l9 5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <h1 class="text-3xl sm:text-4xl font-extrabold mb-3 text-[var(--caramelo)]">Pago pendiente</h1>
      <p class="text-lg mb-3 text-[var(--marron)]">
        Tu pedido fue recibido, pero el pago está pendiente.<br>
        Apenas se confirme, recibirás un correo de confirmación.
      </p>
      <p class="text-sm text-gray-600 mb-7">
        Si pagaste por transferencia o un método manual, puede demorar algunos minutos.<br>
        Ante cualquier duda, escribinos a <a href="mailto:contacto@petiqueta.uy" class="text-[var(--caramelo)] hover:underline">contacto@petiqueta.uy</a>
      </p>
      <a href="https://petiqueta.uy" class="bg-[var(--caramelo)] hover:bg-[var(--marron)] text-white font-semibold px-8 py-3 rounded-lg shadow-md transition mb-2 block w-full sm:w-auto">
        Volver a la tienda
      </a>
    </div>
  </main>

  <?php include __DIR__ . '/../templates/footer.php'; ?>

</body>
</html>
