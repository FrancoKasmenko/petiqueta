<?php
// sidebar.php
?>

<style>
    body {
        display: flex;
        min-height: 100vh;
        margin: 0;
        font-family: Arial, sans-serif;
    }
    .sidebar {
        width: 250px;
        background-color: #2c3e50;
        color: white;
        padding: 20px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
    }
    .sidebar h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .sidebar a {
        color: #ecf0f1;
        text-decoration: none;
        padding: 10px 0;
        display: block;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }
    .sidebar a:hover, .sidebar a.active {
        background-color: #34495e;
    }
    main.container {
        flex-grow: 1;
        padding: 2rem;
        background-color: #f4f6f8;
    }
</style>

<div class="sidebar">
    <h2>Backoffice</h2>
    <nav>
        <a href="usuarios.php" <?php if(basename($_SERVER['PHP_SELF']) === 'usuarios.php') echo 'class="active"'; ?>>Usuarios</a>
        <a href="mascotas.php" <?php if(basename($_SERVER['PHP_SELF']) === 'mascotas.php') echo 'class="active"'; ?>>Mascotas</a>
        <a href="mascotag_codes.php" <?php if(basename($_SERVER['PHP_SELF']) === 'mascotag_codes.php') echo 'class="active"'; ?>>Mascotag Codes</a>
        <a href="ordenes.php" <?php if(basename($_SERVER['PHP_SELF']) === 'ordenes.php') echo 'class="active"'; ?>>Órdenes</a>
        <a href="productos.php" <?php if(basename($_SERVER['PHP_SELF']) === 'productos.php') echo 'class="active"'; ?>>Productos</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</div>
