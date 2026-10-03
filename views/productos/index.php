<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$sql = "
SELECT
    p.*,
    c.nombre AS categoria
FROM productos p
INNER JOIN categorias c
    ON p.id_categoria = c.id_categoria
WHERE p.estado = 1
ORDER BY p.id_producto DESC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Productos - ExpiTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Gestión de Productos</h2>

        <a href="crear.php" class="btn btn-success">
            + Nuevo Producto
        </a>
    </div>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio Compra</th>
                <th>Precio Venta</th>
                <th>Stock Mínimo</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

            <tr>

                <td><?= $fila['id_producto']; ?></td>

                <td><?= $fila['codigo']; ?></td>

                <td><?= $fila['nombre']; ?></td>

                <td><?= $fila['categoria']; ?></td>

                <td>$<?= number_format($fila['precio_compra'], 2); ?></td>

                <td>$<?= number_format($fila['precio_venta'], 2); ?></td>

                <td><?= $fila['stock_minimo']; ?></td>

                <td>

                    <a
                        href="editar.php?id=<?= $fila['id_producto']; ?>"
                        class="btn btn-warning btn-sm">
                        ✏ Editar
                    </a>

                    <a
                        href="eliminar.php?id=<?= $fila['id_producto']; ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('¿Deseas eliminar este producto?');">
                        🗑 Eliminar
                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</body>
</html>