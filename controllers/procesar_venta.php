<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
    exit();
}

try {
    $pdo->beginTransaction();

    $id_usuario = $_SESSION['usuario_id'];
    $id_sucursal = $_SESSION['id_sucursal'] ?? 1;
    $metodo_pago = $input['metodo_pago'] ?? 'EFECTIVO';
    $total = floatval($input['total']);
    $pago_recibido = floatval($input['pago_recibido'] ?? $total);
    $cambio = floatval($input['cambio'] ?? 0);

    // Si el pago es FIADO, procesar o vincular cliente
    $id_cliente = null;
    if ($metodo_pago === 'FIADO') {
        $cliente_id_input = $input['cliente_id'] ?? 'NUEVO';
        $nombre_cliente = trim($input['cliente_nombre'] ?? '');
        $telefono_cliente = trim($input['cliente_telefono'] ?? '');

        if ($cliente_id_input !== 'NUEVO' && intval($cliente_id_input) > 0) {
            $id_cliente = intval($cliente_id_input);
        } else {
            // Insertar nuevo cliente
            $stmtNuevoCliente = $pdo->prepare("INSERT INTO clientes (nombre, telefono) VALUES (:nombre, :telefono)");
            $stmtNuevoCliente->execute(['nombre' => $nombre_cliente, 'telefono' => $telefono_cliente]);
            $id_cliente = $pdo->lastInsertId();
        }

        // Registrar registro en la tabla fiados
        $stmtFiado = $pdo->prepare("
            INSERT INTO fiados (id_cliente, monto_total, saldo_pendiente, fecha_inicio, estado)
            VALUES (:id_cliente, :monto, :saldo, CURDATE(), 'PENDIENTE')
        ");
        $stmtFiado->execute([
            'id_cliente' => $id_cliente,
            'monto' => $total,
            'saldo' => $total
        ]);
    }

    // Insertar Venta
    $stmtVenta = $pdo->prepare("
        INSERT INTO ventas (id_usuario, id_sucursal, metodo_pago, total, pago_recibido, cambio, fecha)
        VALUES (:id_usuario, :id_sucursal, :metodo_pago, :total, :pago_recibido, :cambio, NOW())
    ");
    $stmtVenta->execute([
        'id_usuario' => $id_usuario,
        'id_sucursal' => $id_sucursal,
        'metodo_pago' => $metodo_pago,
        'total' => $total,
        'pago_recibido' => $pago_recibido,
        'cambio' => $cambio
    ]);
    $id_venta = $pdo->lastInsertId();

    // Detalle de Venta y Descuento de Stock en Lotes
    $stmtDetalle = $pdo->prepare("
        INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio)
        VALUES (:id_venta, :id_producto, :cantidad, :precio)
    ");

    $stmtLotes = $pdo->prepare("
        SELECT id_lote, cantidad 
        FROM lotes 
        WHERE id_producto = :id_producto AND estado = 1 AND fecha_caducidad >= CURDATE()
        ORDER BY fecha_caducidad ASC
    ");

    $stmtUpdateLote = $pdo->prepare("UPDATE lotes SET cantidad = :cantidad WHERE id_lote = :id_lote");

    foreach ($input['items'] as $item) {
        $stmtDetalle->execute([
            'id_venta' => $id_venta,
            'id_producto' => $item['id_producto'],
            'cantidad' => $item['cantidad'],
            'precio' => $item['precio_venta']
        ]);

        $cantPendiente = $item['cantidad'];
        $stmtLotes->execute(['id_producto' => $item['id_producto']]);
        $lotes = $stmtLotes->fetchAll();

        foreach ($lotes as $lote) {
            if ($cantPendiente <= 0) break;

            if ($lote['cantidad'] <= $cantPendiente) {
                $cantPendiente -= $lote['cantidad'];
                $stmtUpdateLote->execute(['cantidad' => 0, 'id_lote' => $lote['id_lote']]);
            } else {
                $nuevoStock = $lote['cantidad'] - $cantPendiente;
                $stmtUpdateLote->execute(['cantidad' => $nuevoStock, 'id_lote' => $lote['id_lote']]);
                $cantPendiente = 0;
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'id_venta' => $id_venta]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>