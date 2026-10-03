<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$sql = "
SELECT
l.*,
p.nombre,
p.precio_venta
FROM lotes l
INNER JOIN productos p
ON l.id_producto = p.id_producto
WHERE l.estado = 1
ORDER BY l.fecha_caducidad ASC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<title>Descuentos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>💲 Gestión de Descuentos</h2>

<table class="table table-bordered">

<thead class="table-dark">

<tr>
<th>Producto</th>
<th>Lote</th>
<th>Precio Original</th>
<th>Precio con descuento</th>
<th>Descuento Final</th>
<th>Caducidad</th>
<th>Acción</th>
</tr>

</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<?php

$precioOriginal = $fila['precio_venta'];
$precioFinal = $precioOriginal;

if($fila['descuento_tipo'] == 'porcentaje'){
    $precioFinal = $precioOriginal - ($precioOriginal * ($fila['descuento'] / 100));
}

if($fila['descuento_tipo'] == 'monto'){
    $precioFinal = $precioOriginal - $fila['descuento_valor'];
}

?>

<tr>

<td><?= $fila['nombre']; ?></td>

<td><?= $fila['numero_lote']; ?></td>

<td>$<?= number_format($precioOriginal,2); ?></td>

<td>

<?php
if($fila['descuento_tipo'] == 'porcentaje'){
    echo $fila['descuento']." %";
}
elseif($fila['descuento_tipo'] == 'monto'){
    echo "$".$fila['descuento_valor'];
}
else{
    echo "Sin descuento";
}
?>

</td>

<td>

<strong class="text-success">
$<?= number_format($precioFinal,2); ?>
</strong>

</td>

<td><?= $fila['fecha_caducidad']; ?></td>

<td>

<a href="aplicar.php?id=<?= $fila['id_lote']; ?>"
class="btn btn-warning btn-sm">

Aplicar Descuento

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>