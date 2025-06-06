<?php
require_once '../core/config.php'; 

function generarCodigo($longitud = 6) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

for ($i = 0; $i < 100; $i++) {
    $codigo = generarCodigo();
    $stmt = $pdo->prepare("INSERT IGNORE INTO mascotag_codes (code) VALUES (?)");
    $stmt->execute([$codigo]);
}

echo "Códigos generados.";
