<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$id_lote = intval($input['id_lote'] ?? $_POST['id_lote'] ?? 0);
$descuento_tipo = $input['descuento_tipo'] ?? $_POST['descuento_tipo'] ?? 'porcentaje';
$descuento_valor = floatval($input['descuento_valor'] ?? $_POST['descuento_valor'] ?? 0);

if ($id_lote <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de lote no válido']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE lotes 
        SET descuento_tipo = :tipo, descuento_valor = :valor
        WHERE id_lote = :id_lote
    ");
    $stmt->execute([
        'tipo' => $descuento_tipo,
        'valor' => $descuento_valor,
        'id_lote' => $id_lote
    ]);

    echo json_encode(['success' => true, 'message' => 'Descuento guardado correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>