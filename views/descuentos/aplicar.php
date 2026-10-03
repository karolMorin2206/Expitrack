<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$id_lote = $_GET['id'];

/** @var mysqli $conn */
$sql = "
SELECT
l.*,
p.nombre,
p.precio_venta
FROM lotes l
INNER JOIN productos p
ON l.id_producto = p.id_producto
WHERE l.id_lote = '$id_lote'
";

$resultado = mysqli_query($conn,$sql);
$lote = mysqli_fetch_assoc($resultado);

if(isset($_POST['guardar'])){

    $tipo = $_POST['descuento_tipo'];
    $valor = $_POST['descuento_valor'];

    $sqlUpdate = "
    UPDATE lotes
    SET
    descuento_tipo = '$tipo',
    descuento_valor = '$valor'
    WHERE id_lote = '$id_lote'
    ";

    mysqli_query($conn,$sqlUpdate);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<title>Aplicar Descuento</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h2>💲 Aplicar Descuento</h2>

<div class="card">

<div class="card-body">

<h4><?= $lote['nombre']; ?></h4>

<p>
Lote:
<strong><?= $lote['numero_lote']; ?></strong>
</p>

<p>
Precio actual:
<strong>$<?= $lote['precio_venta']; ?></strong>
</p>

<form method="POST">

<div class="mb-3">

<label>Tipo de descuento</label>

<select name="descuento_tipo" class="form-control" required>

<option value="porcentaje">Porcentaje (%)</option>

<option value="monto">Monto ($)</option>

</select>

</div>

<div class="mb-3">

<label>Valor</label>

<input
type="number"
step="0.01"
name="descuento_valor"
class="form-control"
required>

</div>

<button type="submit" name="guardar" class="btn btn-success">
Guardar Descuento
</button>

<a href="index.php" class="btn btn-secondary">
Volver
</a>

</form>

</div>

</div>

</div>

</body>
</html>