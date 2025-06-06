<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Petiqueta - Identifica y protege a tu mascota</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap"
    rel="stylesheet" />
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
  </style>
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans">



<?php
session_start();
include '../templates/navbar.php';
?>
  <section
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 flex flex-col md:flex-row items-center gap-8 md:gap-10">
    <!-- TEXTOS HERO -->
    <div class="flex-1 w-full max-w-xl">
      <h1
        class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight mb-6"
        style="color: var(--marron);">
        La tranquilidad <br />
        que tu mascota <br />
        <span style="color: var(--caramelo)">se merece</span>
      </h1>
      <p class="text-base sm:text-lg mb-8 max-w-full" style="color: #8b7153;">
        ¿Querés que tu mascota esté siempre segura y fácil de identificar?<br />
        Petiqueta es la medalla inteligente con QR: más seguridad, más
        tranquilidad, más amor.
      </p>
      <a
        href="catalogo"
        class="inline-block bg-[var(--caramelo)] text-white px-6 sm:px-8 py-3 sm:py-4 rounded-full text-base sm:text-lg font-bold shadow-lg hover:bg-[var(--marron)] transition mb-4 max-w-max">¡Quiero mi Petiqueta!</a>
    </div>
    <!-- IMAGEN HERO VISUAL -->
    <div class="flex-1 flex flex-col items-center relative w-full max-w-md sm:max-w-lg md:max-w-none">
      <img
        src="assets/img/mascotas.png"
        alt="Perro, gato y conejo con Petiqueta"
        class="w-full rounded-2xl object-cover" />
      <!-- Huellas decorativas -->
      <div
        class="absolute -top-5 -right-5 text-4xl sm:text-5xl opacity-10 select-none pointer-events-none">🐾</div>
      <div
        class="absolute bottom-2 left-2 text-3xl sm:text-4xl opacity-10 select-none pointer-events-none">🐱</div>
    </div>
  </section>

  <!-- SECCIÓN DESTACADA INFORMATIVA -->
  <section
    class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-8 md:gap-12 px-4 sm:px-6 lg:px-8 py-16 relative">
    <!-- Fondo decorativo vectorial: gato abajo izquierda -->
    <svg
      class="absolute left-0 bottom-6 w-20 h-20 sm:w-24 sm:h-24 opacity-20 -z-10"
      fill="none"
      viewBox="0 0 80 80">
      <path
        d="M20 70 Q 10 60 20 40 Q 30 20 50 25 Q 70 30 65 60 Q 60 75 40 70 Q 30 65 20 70"
        stroke="#c89e6a"
        stroke-width="2"
        fill="none" />
      <circle cx="30" cy="55" r="4" fill="#c89e6a" />
      <circle cx="50" cy="55" r="4" fill="#c89e6a" />
      <path
        d="M32 65 Q40 73 48 65"
        stroke="#c89e6a"
        stroke-width="2"
        fill="none" />
    </svg>
    <!-- Fondo decorativo vectorial: ovillo abajo derecha -->
    <svg
      class="absolute right-0 bottom-0 w-24 h-24 sm:w-28 sm:h-28 opacity-10 -z-10"
      fill="none"
      viewBox="0 0 80 80">
      <circle cx="40" cy="40" r="30" stroke="#c89e6a" stroke-width="3" fill="none" />
      <path d="M30 60 Q40 20 50 60" stroke="#c89e6a" stroke-width="2" fill="none" />
      <path d="M20 55 Q45 10 65 60" stroke="#c89e6a" stroke-width="1.5" fill="none" />
    </svg>

    <!-- Imagen principal -->
    <div class="flex-1 flex justify-center w-full max-w-xs sm:max-w-md md:max-w-[500px] lg:max-w-[400px]">
      <img
        src="assets/img/mascota_tranquila.png"
        alt="Mascotas con medallas Petiqueta"
        class="w-full max-w-full"
        style="border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%" />
    </div>


    <!-- Texto destacado -->
    <div class="flex-1 pl-0 sm:pl-4 md:pl-12 w-full max-w-xl">
      <div
        class="uppercase tracking-wide text-[var(--caramelo)] font-bold text-xs sm:text-sm mb-2">
        ¡Seguro, fácil y rápido!
      </div>
      <h2
        class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-5 leading-tight"
        style="color: var(--marron);">
        Tu mascota siempre identificada<br />
        y a salvo
      </h2>
      <div class="flex items-center gap-3 mb-3">
        <span
          class="bg-[var(--caramelo)] text-white text-xl sm:text-2xl font-bold px-5 sm:px-6 py-1.5 sm:py-2 rounded-full shadow-md">+1K</span>
        <span class="text-[var(--marron)] text-lg sm:text-xl font-semibold">Mascotas protegidas</span>
      </div>
      <p class="text-gray-700 mb-4 text-sm sm:text-base leading-relaxed">
        Registrá a tu mascota y gestioná su perfil online. Si se pierde, quien la
        encuentre puede escanear el QR y ver tu contacto al instante.<br />
        <b>¡La forma más inteligente de proteger a quienes más querés!</b>
      </p>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">
        Además, guardá información médica, vacunas y datos importantes siempre
        disponibles en un solo lugar, estés donde estés.
      </p>
      <!-- Decoración huesito arriba derecha -->
      <svg
        class="absolute top-6 right-56 w-14 h-7 sm:w-16 sm:h-8 opacity-10 rotate-12 -z-10"
        fill="none"
        viewBox="0 0 80 30">
        <path
          d="M10 15 Q20 0 30 15 Q40 30 50 15 Q60 0 70 15"
          stroke="#c89e6a"
          stroke-width="3"
          fill="none" />
      </svg>
    </div>
  </section>

  <!-- SECCIÓN CÓMO FUNCIONA -->
  <section
    id="como-funciona"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <h2
      class="text-center text-3xl sm:text-4xl font-black mb-12"
      style="color: var(--marron)">
      ¿Cómo funciona Petiqueta?
    </h2>
    <div
      class="flex flex-col md:flex-row gap-8 sm:gap-10 justify-center items-center text-center">
      <div
        class="flex-1 bg-white rounded-2xl shadow p-6 sm:p-8 border border-[var(--beige)] max-w-sm">
        <img
          src="https://cdn-icons-png.flaticon.com/512/616/616408.png"
          alt="Registrar mascota"
          class="mx-auto mb-4 w-1/3 sm:w-1/4" />
        <h3
          class="font-extrabold text-lg sm:text-xl mb-2"
          style="color: var(--caramelo)">
          1. Registrá
        </h3>
        <p class="text-[var(--marron)] text-sm sm:text-base">
          Cargá los datos de tu mascota y tus contactos de emergencia.
        </p>
      </div>
      <div
        class="flex-1 bg-white rounded-2xl shadow p-6 sm:p-8 border border-[var(--beige)] max-w-sm">
        <img
          src="assets/img/collar_icono.png"
          alt="Colocá la Petiqueta"
          class="mx-auto mb-4 w-1/3 sm:w-1/4" />
        <h3
          class="font-extrabold text-lg sm:text-xl mb-2"
          style="color: var(--caramelo)">
          2. Colocá
        </h3>
        <p class="text-[var(--marron)] text-sm sm:text-base">
          Poné la medalla Petiqueta en su collar ¡No pesa y es súper resistente!
        </p>
      </div>
      <div
        class="flex-1 bg-white rounded-2xl shadow p-6 sm:p-8 border border-[var(--beige)] max-w-sm">
        <img
          src="assets/img/usuario_icono.png"
          alt="¡Listo! Mascota segura"
          class="mx-auto mb-4 w-1/3 sm:w-1/4" />
        <h3
          class="font-extrabold text-lg sm:text-xl mb-2"
          style="color: var(--caramelo)">
          3. ¡Listo!
        </h3>
        <p class="text-[var(--marron)] text-sm sm:text-base">
          Cualquier persona puede escanear el QR y ayudarte a encontrarla!
        </p>
      </div>
    </div>
    <div class="mt-10 flex justify-center">
      <a
        href="<?= BASE_URL ?>catalogo"
        class="bg-[var(--caramelo)] text-white px-8 py-3 rounded-full text-lg font-bold shadow-lg hover:bg-[var(--marron)] transition">
        Quiero proteger a mi mascota
      </a>
    </div>
  </section>

  <section
    class="max-w-7xl mx-auto px-4 py-20 flex flex-col md:flex-row items-center gap-16"
    id="perfil">
    <div class="flex-1 mb-12 md:mb-0 max-w-xl w-full">
      <h2
        class="text-3xl sm:text-4xl md:text-4xl font-extrabold mb-5"
        style="color: var(--marron)">
        Así se ve tu Petiqueta digital
      </h2>
      <p class="text-gray-700 mb-4 text-sm sm:text-base leading-relaxed">
        Cada Petiqueta incluye un perfil digital único. Si alguien escanea el QR,
        podrá ver todos los datos de tu mascota y contactarte al instante. ¡Rápido,
        seguro y privado!
      </p>
      <ul class="text-[var(--marron)] mb-3 list-disc pl-5 text-sm sm:text-base">
        <li>Nombre, foto, raza, edad, alergias, contactos y más.</li>
        <li>¡Actualizá los datos cuando quieras desde tu cuenta!</li>
        <li>La mejor tranquilidad, con tecnología simple.</li>
      </ul>
    </div>
    <div
      class="flex-1 flex flex-col items-center relative max-w-sm w-full mx-auto sm:mx-0">
      <div
        class="bg-[var(--blanco-caldo)] rounded-2xl shadow-xl border-2 border-[var(--caramelo)] p-6 flex flex-col items-center w-full max-w-[320px]">
        <!-- Foto perfil mascota -->
        <img
          src="https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=160&q=80"
          alt="Perrito Petiqueta"
          class="w-28 h-28 sm:w-32 sm:h-32 object-cover rounded-full border-4 border-[var(--caramelo)] mb-3" />
        <div class="text-center mb-2">
          <span class="block text-lg sm:text-xl font-bold" style="color: var(--marron)">Luna</span>
          <span class="block text-xs sm:text-sm text-gray-600">Golden Retriever</span>
        </div>
        <div class="mb-3">
          <span
            class="inline-block bg-[var(--caramelo)] text-white rounded-full px-3 py-1 text-xs font-semibold mb-1">#459712</span>
          <div class="flex justify-center mt-1 gap-2 text-xs sm:text-sm text-gray-500">
            <span>Edad: <b>3 años</b></span>
            <span>Sexo: <b>Hembra</b></span>
          </div>
        </div>
        <!-- Código QR simulado -->
        <div
          class="bg-white border border-[var(--caramelo)] rounded-lg p-2 flex flex-col items-center mb-2">
          <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://petiqueta.uy/mascota?codigo=1"
            alt="QR Petiqueta"
            class="w-20 h-20 mb-1" />
          <span class="text-xs text-[var(--caramelo)] font-bold">Escaneame</span>
        </div>
        <button
          class="mt-3 w-full bg-[var(--caramelo)] text-white font-semibold py-2 rounded-lg shadow hover:bg-[var(--marron)] transition">
          Contacto de emergencia
        </button>
      </div>
      <!-- Animación de huellas decorativas -->
      <div class="absolute top-2 right-4 opacity-10 text-6xl select-none pointer-events-none">
        🐾
      </div>
    </div>
    <!-- Puedes seguir agregando sección de testimonios, planes/precios, preguntas frecuentes, etc. -->
  </section>

  <!-- Banner divisor animado -->
  <div
    class="w-full bg-[var(--marron)] overflow-hidden select-none border-t-4 border-b-4 border-[var(--caramelo)] py-8">
    <div
      class="flex items-center whitespace-nowrap text-[var(--caramelo)] font-extrabold text-3xl animate-marquee gap-8 px-4">
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
      <i class="fas fa-shopping-cart w-10 h-10"></i>
      <span>COMPRÁ TU PETIQUETA ACÁ</span>
    </div>
  </div>

  <style>
    @keyframes marquee {
      0% {
        transform: translateX(0);
      }

      100% {
        transform: translateX(-20%);
      }
    }

    .animate-marquee {
      display: inline-flex;
      animation: marquee 15s linear infinite;
    }
  </style>

  <section
    class="bg-[var(--beige)] text-[var(--marron)] py-24 relative overflow-hidden">
    <div class="mx-auto px-6" style="max-width: 100rem;">
      <div class="flex justify-between items-center mb-14 flex-col sm:flex-row gap-6 sm:gap-0">
        <h2 class="text-4xl sm:text-5xl font-extrabold tracking-wide">
          Beneficios de Petiqueta
        </h2>
        <a
          href="<?= BASE_URL ?>catalogo"
          class="bg-[var(--caramelo)] text-[var(--marron)] font-semibold px-7 py-3 rounded-full shadow-lg hover:brightness-110 transition duration-300 max-w-max">Comprá acá tu Petiqueta</a>
      </div>

      <div
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-12 justify-center">
        <!-- Cada tarjeta -->
        <div
          class="bg-[var(--caramelo)] bg-opacity-30 rounded-3xl p-10 flex flex-col items-center text-center shadow-md hover:scale-105 transform transition-transform duration-300 cursor-default max-w-[380px] mx-auto">
          <div
            class="bg-[var(--marron)] rounded-full p-6 mb-8 inline-flex text-[var(--blanco-caldo)] text-5xl shadow-sm">
            <i class="fas fa-paw"></i>
          </div>
          <h3 class="text-2xl font-bold mb-3">Identificación Rápida</h3>
          <p class="text-base leading-relaxed max-w-sm mx-auto">
            Quien encuentre a tu mascota puede escanear el QR y contactarte al
            instante.
          </p>
        </div>

        <div
          class="bg-[var(--caramelo)] bg-opacity-30 rounded-3xl p-10 flex flex-col items-center text-center shadow-md hover:scale-105 transform transition-transform duration-300 cursor-default max-w-[380px] mx-auto">
          <div
            class="bg-[var(--marron)] rounded-full p-6 mb-8 inline-flex text-[var(--blanco-caldo)] text-5xl shadow-sm">
            <i class="fas fa-lock"></i>
          </div>
          <h3 class="text-2xl font-bold mb-3">Seguridad y Privacidad</h3>
          <p class="text-base leading-relaxed max-w-sm mx-auto">
            Tu información está protegida y solo se comparte con quien realmente
            escanee el QR.
          </p>
        </div>

        <div
          class="bg-[var(--caramelo)] bg-opacity-30 rounded-3xl p-10 flex flex-col items-center text-center shadow-md hover:scale-105 transform transition-transform duration-300 cursor-default max-w-[380px] mx-auto">
          <div
            class="bg-[var(--marron)] rounded-full p-6 mb-8 inline-flex text-[var(--blanco-caldo)] text-5xl shadow-sm">
            <i class="fas fa-sync-alt"></i>
          </div>
          <h3 class="text-2xl font-bold mb-3">Siempre Actualizable</h3>
          <p class="text-base leading-relaxed max-w-sm mx-auto">
            Modificá los datos de tu mascota en cualquier momento sin cambiar el
            QR físico.
          </p>
        </div>

        <div
          class="bg-[var(--caramelo)] bg-opacity-30 rounded-3xl p-10 flex flex-col items-center text-center shadow-md hover:scale-105 transform transition-transform duration-300 cursor-default max-w-[380px] mx-auto">
          <div
            class="bg-[var(--marron)] rounded-full p-6 mb-8 inline-flex text-[var(--blanco-caldo)] text-5xl shadow-sm">
            <i class="fas fa-dumbbell"></i>
          </div>
          <h3 class="text-2xl font-bold mb-3">Durabilidad</h3>
          <p class="text-base leading-relaxed max-w-sm mx-auto">
            La medalla es resistente, ligera y diseñada para durar toda la vida de
            tu mascota.
          </p>
        </div>
      </div>
    </div>
  </section>

  
</body>
<?php include '../templates/footer.php'; ?>
</html>
</html>