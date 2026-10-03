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
    p.nombre AS producto
FROM lotes l
INNER JOIN productos p
    ON l.id_producto = p.id_producto
WHERE l.estado = 1
ORDER BY l.fecha_caducidad ASC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Alertas de Caducidad</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>🚨 Alertas Automáticas</h2>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>
    <th>Producto</th>
    <th>Lote</th>
    <th>Cantidad</th>
    <th>Caducidad</th>
    <th>Estado</th>
    <th>Recomendación</th>
</tr>

</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<?php

$hoy = new DateTime();
$caduca = new DateTime($fila['fecha_caducidad']);

$diferencia = $hoy->diff($caduca);

$meses = ($diferencia->y * 12) + $diferencia->m;

if($caduca < $hoy){

    $estado = "❌ Vencido";
    $recomendacion = "Retirar del inventario";

}
elseif($meses <= 2){

    $estado = "🔴 Riesgo Alto";
    $recomendacion = "Aplicar descuento 20%";

}
elseif($meses <= 7){

    $estado = "🟡 Atención";
    $recomendacion = "Monitorear y preparar promoción";

}
else{

    $estado = "🟢 Correcto";
    $recomendacion = "Sin riesgo";

}

?>

<tr>

<td><?= $fila['producto']; ?></td>

<td><?= $fila['numero_lote']; ?></td>

<td><?= $fila['cantidad']; ?></td>

<td><?= $fila['fecha_caducidad']; ?></td>

<td><?= $estado; ?></td>

<td><?= $recomendacion; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>