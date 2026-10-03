<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */

$sql = "
SELECT
p.nombre,
l.numero_lote,
l.cantidad,
l.fecha_caducidad,
p.precio_venta
FROM lotes l
INNER JOIN productos p
ON l.id_producto = p.id_producto
WHERE l.estado = 1
ORDER BY l.fecha_caducidad ASC
";

$resultado = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<title>Reportes - ExpiTrack</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>📊 Reporte General de Caducidades</h2>

<hr>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>
<th>Producto</th>
<th>Lote</th>
<th>Cantidad</th>
<th>Caducidad</th>
<th>Precio</th>
<th>Estado</th>
</tr>

</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<?php

$hoy = new DateTime();
$caduca = new DateTime($fila['fecha_caducidad']);

if($caduca < $hoy){
    $estado = "Vencido";
    $clase = "table-danger";
}
else{

    $diferencia = $hoy->diff($caduca);
    $meses = ($diferencia->y * 12) + $diferencia->m;

    if($meses <= 2){
        $estado = "Riesgo";
        $clase = "table-warning";
    }
    else{
        $estado = "Correcto";
        $clase = "table-success";
    }
}

?>

<tr class="<?= $clase; ?>">

<td><?= $fila['nombre']; ?></td>

<td><?= $fila['numero_lote']; ?></td>

<td><?= $fila['cantidad']; ?></td>

<td><?= $fila['fecha_caducidad']; ?></td>

<td>$<?= number_format($fila['precio_venta'],2); ?></td>

<td><?= $estado; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>