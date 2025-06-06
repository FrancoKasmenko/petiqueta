<?php
$host = 'localhost';
$dbname = 'u460153099_petiqueta';
$user = 'u460153099_franco';
$pass = 'Fiona1989_';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
