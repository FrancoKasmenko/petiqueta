<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

define('BASE_URL', 'https://petiqueta.uy/');

$idusuario = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_nombre'] ?? '';

$sesion_iniciada = !empty($idusuario) && ctype_digit((string)$idusuario) && (int)$idusuario > 0;

$carritoCount = 0;
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
  foreach ($_SESSION['carrito'] as $item) {
    $carritoCount += $item['cantidad'];
  }
}
?>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    :root {
      --blanco-caldo: #fcf7ef;
      --marron: #7a583a;
      --caramelo: #b2884a;
    }

    .header-mtg {
      background: var(--blanco-caldo);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      position: relative;
      z-index: 50;
    }

    .carrito-container {
      position: relative;
      display: flex;
      align-items: center;
      cursor: pointer;
      color: var(--marron);
      font-weight: 600;
    }

    .carrito-count {
      position: absolute;
      top: -6px;
      right: -10px;
      background: var(--caramelo);
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      font-weight: 700;
      min-width: 20px;
      text-align: center;
    }

    .header-mtg .header-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 18px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-mtg .logo img {
      height: 60px;
    }

    .header-mtg .nav-links,
    .header-mtg .nav-user {
      display: flex;
      gap: 28px;
      font-weight: 600;
      color: var(--marron);
      font-size: 18px;
    }

    .header-mtg .nav-user {
      display: flex;
      align-items: center;
      gap: 28px;
      font-weight: 600;
      color: var(--marron);
      font-size: 18px;
      height: 52px;
    }

    .header-mtg .nav-user a {
      color: var(--marron);
      text-decoration: none;
      transition: color 0.2s;
      padding: 12px 8px;
      display: flex;
      align-items: center;
      height: 100%;
    }

    .header-mtg .nav-links a:hover,
    .header-mtg .nav-user a:hover {
      color: var(--caramelo);
      text-decoration: underline;
    }

    .header-mtg .btn-registro {
      background: var(--caramelo);
      color: #fff;
      padding: 12px 22px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      font-weight: 600;
      font-size: 18px;
      border: none;
      transition: background 0.2s, color 0.2s;
      cursor: pointer;
    }

    .header-mtg .btn-registro:hover {
      background: var(--marron);
      color: #fff;
    }

    .header-mtg .hamburger {
      display: none;
      background: none;
      border: none;
      color: var(--marron);
      font-size: 30px;
      cursor: pointer;
      z-index: 20;
    }

    .header-mtg .nav-mobile {
      display: none;
      flex-direction: column;
      position: fixed;
      left: 0;
      top: 70px;
      width: 100%;
      background: var(--blanco-caldo);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
      padding: 24px 0 12px 0;
      gap: 12px;
      z-index: 30;
    }

    .header-mtg .nav-mobile a,
    .header-mtg .nav-mobile .btn-registro {
      margin: 0 24px 10px 24px;
      text-align: center;
    }

    .header-mtg .nav-links a,
    .header-mtg .nav-links a:link,
    .header-mtg .nav-links a:visited,
    .header-mtg .nav-links a:active,
    .header-mtg .nav-user a,
    .header-mtg .nav-user a:link,
    .header-mtg .nav-user a:visited,
    .header-mtg .nav-user a:active {
      color: var(--marron);
      text-decoration: none;
      transition: color 0.2s;
    }

    .header-mtg .nav-links a:hover,
    .header-mtg .nav-user a:hover {
      color: var(--caramelo);
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      .header-mtg .header-container {
        padding: 12px 10px;
      }

      .header-mtg .nav-links,
      .header-mtg .nav-user {
        gap: 14px;
        font-size: 16px;
      }

      .header-mtg .logo img {
        height: 40px;
      }
    }

    @media (max-width: 700px) {

      .header-mtg .nav-links,
      .header-mtg .nav-user {
        display: none;
      }

      .header-mtg .hamburger {
        display: block;
      }

      .header-mtg .nav-mobile {
        display: flex;
      }

      .header-mtg .nav-mobile.hide {
        display: none !important;
      }
    }
  </style>

  <header class="header-mtg">
    <div class="header-container">
      <!-- Logo -->
      <div class="logo">
        <a href="<?= BASE_URL ?>">
          <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Petiqueta logo" />
        </a>
      </div>

      <!-- Menú desktop -->
      <nav class="nav-links">
        <a href="<?= BASE_URL ?>">Inicio</a>
        <a href="<?= BASE_URL ?>#como-funciona">Cómo Funciona</a>
        <a href="<?= BASE_URL ?>#precios">Precios</a>
        <a href="<?= BASE_URL ?>contacto">Contacto</a>
      </nav>

      <!-- Botones usuario + carrito -->
      <?php if ($sesion_iniciada && isset($_SESSION['user_nombre'])): ?>
        <div class="nav-user" style="gap:20px; align-items: center;">
          <a href="<?= BASE_URL ?>perfil"><?= htmlspecialchars($userName) ?></a>
          <a href="<?= BASE_URL ?>carrito" class="carrito-container" title="Ver carrito">
            <i class="fas fa-shopping-cart" style="width:24px; height:24px; margin-right: 8px; fill: var(--marron);"></i>
            <?php if ($carritoCount > 0): ?>
              <span class="carrito-count"><?= $carritoCount ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= BASE_URL ?>logout" class="btn-registro" style="background:var(--caramelo);">Cerrar sesión</a>
        </div>
      <?php else: ?>
        <div class="nav-user">
            
          <a href="<?= BASE_URL ?>carrito" class="carrito-container" title="Ver carrito">
            <i class="fas fa-shopping-cart" style="width:24px; height:24px; margin-right: 8px; fill: var(--marron);"></i>
            <?php if ($carritoCount > 0): ?>
              <span class="carrito-count"><?= $carritoCount ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= BASE_URL ?>login">Iniciar Sesión</a>
          <a href="<?= BASE_URL ?>registro" class="btn-registro">Registrarse</a>
        </div>
      <?php endif; ?>

      <!-- Botón hamburguesa mobile -->
      <button class="hamburger" id="btnMenu">&#9776;</button>
    </div>

    <!-- Menú mobile -->
    <nav class="nav-mobile hide" id="menuMobile">
      <a href="<?= BASE_URL ?>">Inicio</a>
      <a href="<?= BASE_URL ?>#como-funciona">Cómo Funciona</a>
      <a href="<?= BASE_URL ?>#precios">Precios</a>
      <a href="<?= BASE_URL ?>contacto">Contacto</a>
      <?php if ($sesion_iniciada && isset($_SESSION['user_nombre'])): ?>
        <a href="<?= BASE_URL ?>perfil"><?= htmlspecialchars($userName) ?></a>
        <a href="<?= BASE_URL ?>carrito" class="carrito-container" title="Ver carrito" style="position: relative; display: flex; align-items: center;">
          <i class="fas fa-shopping-cart" style="width:24px; height:24px; margin-right: 8px; fill: var(--marron);"></i>
          <?php if ($carritoCount > 0): ?>
            <span class="carrito-count" style="top: -6px; right: 16px;"><?= $carritoCount ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>logout" class="btn-registro" style="background:var(--caramelo);">Cerrar sesión</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>login">Iniciar Sesión</a>
        <a href="<?= BASE_URL ?>registro" class="btn-registro">Registrarse</a>
        <center>
                <a href="<?= BASE_URL ?>carrito" class="carrito-container" title="Ver carrito" style="position: relative; display: flex; align-items: center;">
          <i class="fas fa-shopping-cart" style="width:24px; height:24px; margin-right: 8px; fill: var(--marron);"></i>
          <?php if ($carritoCount > 0): ?>
            <span class="carrito-count" style="top: -6px; right: 16px;"><?= $carritoCount ?></span>
          <?php endif; ?>
        </a>
        </center>
      <?php endif; ?>
    </nav>
  </header>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const btnMenu = document.getElementById('btnMenu');
      const menuMobile = document.getElementById('menuMobile');
      btnMenu.addEventListener('click', function() {
        menuMobile.classList.toggle('hide');
      });
    });
  </script>
