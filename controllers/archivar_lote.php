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
$nuevo_estado = isset($input['estado']) ? intval($input['estado']) : 0;

if ($id_lote <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de lote no válido']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE lotes SET estado = :estado WHERE id_lote = :id_lote");
    $stmt->execute([
        'estado' => $nuevo_estado,
        'id_lote' => $id_lote
    ]);

    $mensaje = ($nuevo_estado === 0) ? 'Lote archivado correctamente' : 'Lote restaurado correctamente';
    echo json_encode(['success' => true, 'message' => $mensaje]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>