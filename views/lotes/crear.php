<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */
$sqlProductos = "SELECT * FROM productos WHERE estado = 1";
$productos = mysqli_query($conn, $sqlProductos);

if(isset($_POST['guardar'])){

    $id_producto = $_POST['id_producto'];
    $numero_lote = $_POST['numero_lote'];
    $cantidad = $_POST['cantidad'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $fecha_caducidad = $_POST['fecha_caducidad'];

    $id_sucursal = $_SESSION['id_sucursal'];

    /** @var mysqli $conn */
    $sql = "INSERT INTO lotes(
                id_producto,
                numero_lote,
                cantidad,
                fecha_ingreso,
                fecha_caducidad,
                id_sucursal
            )
            VALUES(
                '$id_producto',
                '$numero_lote',
                '$cantidad',
                '$fecha_ingreso',
                '$fecha_caducidad',
                '$id_sucursal'
            )";

    mysqli_query($conn,$sql);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Lote</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

<h2>Registrar Lote</h2>

<form method="POST">

<div class="mb-3">
<label>Producto</label>

<select name="id_producto" class="form-control" required>

<?php while($producto = mysqli_fetch_assoc($productos)){ ?>

<option value="<?= $producto['id_producto']; ?>">
<?= $producto['nombre']; ?>
</option>

<?php } ?>

</select>

</div>

<div class="mb-3">
<label>Número de Lote</label>
<input type="text" name="numero_lote" class="form-control" required>
</div>

<div class="mb-3">
<label>Cantidad</label>
<input type="number" name="cantidad" class="form-control" required>
</div>

<div class="mb-3">
<label>Fecha de Ingreso</label>
<input type="date" name="fecha_ingreso" class="form-control" required>
</div>

<div class="mb-3">
<label>Fecha de Caducidad</label>
<input type="date" name="fecha_caducidad" class="form-control" required>
</div>

<button type="submit" name="guardar" class="btn btn-success">
Guardar Lote
</button>

<a href="index.php" class="btn btn-secondary">
Volver
</a>

</form>

</div>

</body>
</html>