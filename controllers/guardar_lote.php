<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $id_sucursal = $_SESSION['id_sucursal'] ?? 1;
    $numero_lote = trim($_POST['numero_lote']);
    $cantidad = intval($_POST['cantidad']);
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $fecha_caducidad = $_POST['fecha_caducidad'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO lotes (id_producto, id_sucursal, numero_lote, cantidad, fecha_ingreso, fecha_caducidad, estado)
            VALUES (:id_producto, :id_sucursal, :numero_lote, :cantidad, :fecha_ingreso, :fecha_caducidad, 1)
        ");
        $stmt->execute([
            'id_producto' => $id_producto,
            'id_sucursal' => $id_sucursal,
            'numero_lote' => $numero_lote,
            'cantidad' => $cantidad,
            'fecha_ingreso' => $fecha_ingreso,
            'fecha_caducidad' => $fecha_caducidad
        ]);

        header("Location: ../views/punto_venta/index.php");
        exit();
    } catch (PDOException $e) {
        die("Error al guardar el lote: " . $e->getMessage());
    }
}
?>