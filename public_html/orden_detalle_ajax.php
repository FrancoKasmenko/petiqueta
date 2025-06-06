<?php
session_start();
require '../app/core/config.php';

header('Content-Type: application/json');

$idOrden = $_GET['id'] ?? null;

if (!$idOrden || !ctype_digit($idOrden)) {
    echo json_encode(['error' => 'ID de orden inválido']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Validar que la orden pertenece al usuario
$stmtOrden = $pdo->prepare("SELECT * FROM orden WHERE id_orden = ? AND id_usuario = ?");
$stmtOrden->execute([$idOrden, $userId]);
$ordenData = $stmtOrden->fetch(PDO::FETCH_ASSOC);

if (!$ordenData) {
    echo json_encode(['error' => 'Orden no encontrada o no pertenece al usuario']);
    exit;
}

$stmtDetalle = $pdo->prepare("
    SELECT od.*, p.nombre FROM orden_detalle od
    LEFT JOIN producto p ON od.id_producto = p.id_producto
    WHERE od.id_orden = ?
");
$stmtDetalle->execute([$idOrden]);
$detalleData = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'id_orden' => $ordenData['id_orden'],
    'fecha_creacion' => $ordenData['fecha_creacion'],
    'total' => (float)$ordenData['total'],
    'estado' => ucfirst($ordenData['estado']),
    'metodo_pago' => ucfirst($ordenData['metodo_pago']),
    'metodo_entrega' => ucfirst($ordenData['metodo_entrega']),
    'detalle' => $detalleData,
]);
