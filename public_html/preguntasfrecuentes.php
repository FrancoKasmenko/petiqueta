<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Preguntas Frecuentes | PETIQUETA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../templates/style.css" />
  <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
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
</head>
<body>

<?php include '../templates/navbar.php'; ?>

<main>
  <h1>Preguntas Frecuentes</h1>

  <section class="faq-item">
    <h2>¿Qué es PETIQUETA y cómo funciona?</h2>
    <p class="answer">PETIQUETA es un sistema inteligente de identificación para mascotas mediante medallas con código QR. Registras los datos de tu mascota en nuestra plataforma y cualquier persona puede escanear el código para acceder a su información y contactarte en caso de pérdida.</p>
  </section>

  <section class="faq-item">
    <h2>¿Cuánto tarda el envío de la medalla?</h2>
    <p class="answer">El envío estándar demora entre 1 y 3 días hábiles dentro de Uruguay. Puedes elegir retiro en tienda para obtenerla el mismo día en Montevideo.</p>
  </section>

  <section class="faq-item">
    <h2>¿Puedo actualizar los datos de mi mascota luego de registrar la medalla?</h2>
    <p class="answer">Sí, desde tu perfil en PETIQUETA puedes editar toda la información de tu mascota en cualquier momento, manteniendo siempre la medalla vinculada.</p>
  </section>

  <section class="faq-item">
    <h2>¿Qué pasa si alguien escanea la medalla y no puede contactarme?</h2>
    <p class="answer">PETIQUETA recomienda dejar al menos un contacto válido en el registro. Si tienes problemas con la comunicación, puedes actualizar tus datos o contactarnos para asistencia.</p>
  </section>

  <section class="faq-item">
    <h2>¿Es seguro compartir la información de mi mascota en PETIQUETA?</h2>
    <p class="answer">Sí, toda la información está protegida y solo se comparte lo necesario para la identificación y contacto en caso de emergencia. No compartimos tus datos con terceros sin tu consentimiento.</p>
  </section>

  <section class="faq-item">
    <h2>¿Puedo registrar más de una mascota?</h2>
    <p class="answer">Sí, puedes registrar tantas mascotas como quieras y obtener una medalla personalizada para cada una.</p>
  </section>

  <section class="faq-item">
    <h2>¿Qué formas de pago aceptan?</h2>
    <p class="answer">Aceptamos pagos vía Mercado Pago y transferencia bancaria. Puedes elegir la opción que prefieras durante el proceso de compra.</p>
  </section>

  <section class="faq-item">
  <h2>¿Realizan devoluciones o tienen garantía?</h2>
  <p class="answer">
    En PETIQUETA no realizamos devoluciones de los productos por tratarse de artículos personalizados. Sin embargo, todos nuestros productos cuentan con garantía de fabricación por defectos o fallas de calidad.
  </p>
  <p class="answer">
    La garantía cubre cualquier defecto en materiales o fabricación durante los primeros 3 meses desde la compra. Si tu medalla presenta algún daño o problema cubierto por la garantía, te ofrecemos la reparación o reemplazo sin costo adicional.
  </p>
  <p class="answer">
    Para hacer uso de la garantía, contacta con nuestro soporte y te guiaremos en el proceso de evaluación y solución.
  </p>
</section>

  
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
