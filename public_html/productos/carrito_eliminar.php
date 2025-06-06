<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['index']) || !is_numeric($_POST['index'])) {
        header('Location: https://petiqueta.uy/carrito');
        exit;
    }

    $index = (int)$_POST['index'];

    if (isset($_SESSION['carrito'][$index])) {
        // Eliminar el producto usando el índice
        unset($_SESSION['carrito'][$index]);
        // Reindexar para evitar huecos
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

header('Location: https://petiqueta.uy/carrito?mensaje=producto-eliminado');
exit;
