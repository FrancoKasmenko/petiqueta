<?php
define('BASE_URL', 'https://petiqueta.uy/');
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_nombre'] ?? null;
?>

<header class="bg-[var(--blanco-caldo)] shadow-sm relative z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <a href="<?= BASE_URL ?>index.php">
        <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Petiqueta logo" />
      </a>
    </div>

    <nav class="hidden md:flex gap-8 font-semibold text-[var(--marron)] text-base lg:text-lg">
      <a href="#" class="hover:text-[var(--caramelo)] transition">Inicio</a>
      <a href="#como-funciona" class="hover:text-[var(--caramelo)] transition">Cómo Funciona</a>
      <a href="#precios" class="hover:text-[var(--caramelo)] transition">Precios</a>
      <a href="#contacto" class="hover:text-[var(--caramelo)] transition">Contacto</a>
    </nav>

    <?php if ($userId): ?>
      <div class="hidden md:flex items-center gap-4">
        <a href="<?= BASE_URL ?>perfil.php" class="text-[var(--marron)] font-semibold hover:underline"><?= htmlspecialchars($userName) ?></a>
        <a href="<?= BASE_URL ?>logout.php" class="bg-[var(--caramelo)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--marron)] transition font-semibold text-base sm:text-lg">Cerrar sesión</a>
      </div>
    <?php else: ?>
      <div class="hidden md:flex items-center gap-4">
        <a href="<?= BASE_URL ?>login.php" class="text-[var(--marron)] font-semibold hover:underline">Iniciar Sesión</a>
        <a href="<?= BASE_URL ?>registro.php" class="bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg shadow hover:bg-[var(--marron)] transition font-semibold text-base sm:text-lg">Registrarse</a>
      </div>
    <?php endif; ?>

    <button id="btnMenu" aria-label="Abrir menú" class="md:hidden flex items-center text-[var(--marron)] focus:outline-none z-20" type="button">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
  </div>

  <nav id="menuMobile" class="fixed left-0 w-full bg-[var(--blanco-caldo)] shadow-lg overflow-hidden max-h-0 transition-all duration-300 ease-in-out md:hidden z-10" style="top: 80px; max-height: 0;">
    <div class="flex flex-col p-4 space-y-4 font-semibold text-[var(--marron)]">
      <a href="#" class="block hover:text-[var(--caramelo)] transition">Inicio</a>
      <a href="#como-funciona" class="block hover:text-[var(--caramelo)] transition">Cómo Funciona</a>
      <a href="#precios" class="block hover:text-[var(--caramelo)] transition">Precios</a>
      <a href="#contacto" class="block hover:text-[var(--caramelo)] transition">Contacto</a>

      <?php if ($userId): ?>
        <div class="border-t pt-4">
          <a href="<?= BASE_URL ?>perfil.php" class="block mb-3 text-[var(--marron)] font-semibold hover:underline"><?= htmlspecialchars($userName) ?></a>
          <a href="<?= BASE_URL ?>logout.php" class="block bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg shadow hover:bg-[var(--marron)] transition font-semibold text-center">Cerrar sesión</a>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>login.php" class="block mt-4 text-center text-[var(--marron)] font-semibold hover:underline">Iniciar Sesión</a>
        <a href="<?= BASE_URL ?>registro.php" class="mt-2 bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg shadow hover:bg-[var(--marron)] transition font-semibold text-center">Registrarse</a>
      <?php endif; ?>
    </div>
  </nav>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btnMenu = document.getElementById('btnMenu');
      const menuMobile = document.getElementById('menuMobile');
      const header = document.querySelector('header');

      const headerHeight = header.offsetHeight;
      menuMobile.style.top = headerHeight + 'px';

      btnMenu.addEventListener('click', () => {
        if (menuMobile.classList.contains('max-h-0')) {
          menuMobile.classList.remove('max-h-0');
          menuMobile.style.maxHeight = menuMobile.scrollHeight + 'px';
        } else {
          menuMobile.classList.add('max-h-0');
          menuMobile.style.maxHeight = null;
        }
      });
    });
  </script>
</header>
