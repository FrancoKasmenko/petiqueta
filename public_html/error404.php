<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Error 404 - Página no encontrada | Petiqueta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
      <link rel="stylesheet" href="https://petiqueta.uy/assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
  <style>
    :root {
      --marron: #7a5c39;
      --caramelo: #c89e6a;
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
      flex-grow: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }
    h1 {
      font-size: 5rem;
      font-weight: 700;
      color: var(--caramelo);
    }
    p {
      font-size: 1.5rem;
      margin-top: 1rem;
      margin-bottom: 2rem;
    }
    a.btn-primary {
      background-color: var(--caramelo);
      border-color: var(--caramelo);
      font-weight: 700;
      padding: 0.75rem 2rem;
      border-radius: 1rem;
      transition: background-color 0.3s;
      text-decoration: none;
      color: white;
    }
    a.btn-primary:hover {
      background-color: var(--marron);
      border-color: var(--marron);
      color: white;
    }
    footer {
      text-align: center;
      padding: 1rem;
      font-size: 0.9rem;
      color: #999;
    }
  </style>
</head>
<body class="bg-[var(--beige)] min-h-screen font-sans flex flex-col">
  <?php include '../templates/navbar.php'; ?>

  <main>
    <div>
      <h1>404</h1>
      <p>Lo sentimos, la página que buscas no existe.</p>
      <a href="https://petiqueta.uy/" class="btn btn-primary">Volver al inicio</a>
    </div>
  </main>

  <?php include '../templates/footer.php'; ?>
</body>
</html>
