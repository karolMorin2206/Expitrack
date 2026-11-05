<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $id_categoria = intval($_POST['id_categoria']);
    $precio_venta = floatval($_POST['precio_venta']);
    $precio_compra = floatval($_POST['precio_compra'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
    $descripcion = trim($_POST['descripcion'] ?? '');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO productos (codigo, nombre, descripcion, id_categoria, precio_compra, precio_venta, stock_minimo, estado)
            VALUES (:codigo, :nombre, :descripcion, :id_categoria, :precio_compra, :precio_venta, :stock_minimo, 1)
        ");
        $stmt->execute([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'id_categoria' => $id_categoria,
            'precio_compra' => $precio_compra,
            'precio_venta' => $precio_venta,
            'stock_minimo' => $stock_minimo
        ]);

        // Redirigir directamente a agregar lote para este producto
        header("Location: ../views/lotes/crear.php?msg=producto_creado");
        exit();
    } catch (PDOException $e) {
        die("Error al guardar producto: " . $e->getMessage());
    }
}
?>