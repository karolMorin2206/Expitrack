<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['efectivo' => 0, 'tarjeta' => 0, 'fiado' => 0, 'total_caja' => 0]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN metodo_pago = 'EFECTIVO' THEN total ELSE 0 END), 0) AS efectivo,
            COALESCE(SUM(CASE WHEN metodo_pago = 'TARJETA' THEN total ELSE 0 END), 0) AS tarjeta,
            COALESCE(SUM(CASE WHEN metodo_pago = 'FIADO' THEN total ELSE 0 END), 0) AS fiado
        FROM ventas
        WHERE DATE(fecha) = CURDATE()
    ");
    $stmt->execute();
    $totales = $stmt->fetch(PDO::FETCH_ASSOC);

    $efectivo = floatval($totales['efectivo']);
    $tarjeta = floatval($totales['tarjeta']);
    $fiado = floatval($totales['fiado']);

    // Total cobrado en caja (Efectivo + Tarjeta, SIN incluir Fiado)
    $total_caja = $efectivo + $tarjeta;

    echo json_encode([
        'efectivo' => $efectivo,
        'tarjeta' => $tarjeta,
        'fiado' => $fiado,
        'total_caja' => $total_caja
    ]);
} catch (PDOException $e) {
    echo json_encode(['efectivo' => 0, 'tarjeta' => 0, 'fiado' => 0, 'total_caja' => 0]);
}
?>