<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$sql = "
SELECT
v.id_venta,
v.fecha_venta,
v.total,
u.nombre AS usuario,
s.nombre AS sucursal
FROM ventas v
INNER JOIN usuarios u
ON v.id_usuario = u.id_usuario
INNER JOIN sucursales s
ON v.id_sucursal = s.id_sucursal
ORDER BY v.id_venta DESC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html lang='es'>
<head>

<meta charset='UTF-8'>

<title>Historial de Ventas</title>

<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>

</head>
<body>

<div class="container mt-4">

<h2>🧾 Historial de Ventas</h2>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Fecha</th>
<th>Usuario</th>
<th>Sucursal</th>
<th>Total</th>
</tr>

</thead>

<tbody>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<tr>

<td><?= $fila['id_venta']; ?></td>

<td><?= $fila['fecha_venta']; ?></td>

<td><?= $fila['usuario']; ?></td>

<td><?= $fila['sucursal']; ?></td>

<td>$<?= number_format($fila['total'],2); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>