<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$sql = "
SELECT
    p.id_producto,
    p.codigo,
    p.nombre,
    SUM(l.cantidad) AS stock_total,
    MIN(l.fecha_caducidad) AS proxima_caducidad

FROM productos p

INNER JOIN lotes l
    ON p.id_producto = l.id_producto

WHERE p.estado = 1
AND l.estado = 1

GROUP BY p.id_producto
ORDER BY p.nombre ASC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Inventario Inteligente</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card-header{
    font-weight:bold;
}

</style>

</head>

<body>

<div class="container mt-4">

<h2 class="mb-4">
📦 Inventario Inteligente
</h2>

<div class="card shadow">

<div class="card-header bg-dark text-white">
Existencias Generales
</div>

<div class="card-body">

<table class="table table-striped table-hover">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Código</th>
<th>Producto</th>
<th>Stock Total</th>
<th>Próxima Caducidad</th>
<th>Estado</th>
</tr>

</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<?php

$hoy = new DateTime();
$caducidad = new DateTime($fila['proxima_caducidad']);

$diferencia = $hoy->diff($caducidad);

$meses = ($diferencia->y * 12) + $diferencia->m;

if($caducidad < $hoy){
    $estado = "🔴 Vencido";
}
elseif($meses <= 2){
    $estado = "🔴 Riesgo Alto";
}
elseif($meses <= 7){
    $estado = "🟡 Atención";
}
else{
    $estado = "🟢 Correcto";
}

?>

<tr>

<td><?= $fila['id_producto']; ?></td>

<td><?= $fila['codigo']; ?></td>

<td><?= $fila['nombre']; ?></td>

<td>
<strong>
<?= $fila['stock_total']; ?>
</strong>
</td>

<td>
<?= $fila['proxima_caducidad']; ?>
</td>

<td>
<?= $estado; ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>