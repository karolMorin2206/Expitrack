<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

// Crear tabla de facturas en automático si no existe
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS facturas (
            id_factura INT AUTO_INCREMENT PRIMARY KEY,
            id_venta INT NOT NULL,
            rfc VARCHAR(20) NOT NULL,
            razon_social VARCHAR(150) NOT NULL,
            correo VARCHAR(100) NOT NULL,
            uso_cfdi VARCHAR(20) DEFAULT 'G03',
            regimen_fiscal VARCHAR(20) DEFAULT '616',
            fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {}

$input = json_decode(file_get_contents('php://input'), true);

$id_venta = intval($input['id_venta'] ?? 0);
$rfc = strtoupper(trim($input['rfc'] ?? ''));
$razon_social = trim($input['razon_social'] ?? '');
$correo = trim($input['correo'] ?? '');
$uso_cfdi = $input['uso_cfdi'] ?? 'G03';
$regimen_fiscal = $input['regimen_fiscal'] ?? '616';

if ($id_venta <= 0 || empty($rfc) || empty($razon_social)) {
    echo json_encode(['success' => false, 'message' => 'Por favor completa los campos requeridos']);
    exit();
}

try {
    // Validar que la venta exista
    $stmtVenta = $pdo->prepare("SELECT id_venta FROM ventas WHERE id_venta = :id_venta LIMIT 1");
    $stmtVenta->execute(['id_venta' => $id_venta]);
    if (!$stmtVenta->fetch()) {
        echo json_encode(['success' => false, 'message' => 'No se encontró ninguna venta registrada con el ticket #' . $id_venta]);
        exit();
    }

    // Insertar Factura
    $stmtFactura = $pdo->prepare("
        INSERT INTO facturas (id_venta, rfc, razon_social, correo, uso_cfdi, regimen_fiscal, fecha_emision)
        VALUES (:id_venta, :rfc, :razon_social, :correo, :uso_cfdi, :regimen_fiscal, NOW())
    ");
    $stmtFactura->execute([
        'id_venta' => $id_venta,
        'rfc' => $rfc,
        'razon_social' => $razon_social,
        'correo' => $correo,
        'uso_cfdi' => $uso_cfdi,
        'regimen_fiscal' => $regimen_fiscal
    ]);

    echo json_encode(['success' => true, 'message' => 'Factura generada exitosamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>