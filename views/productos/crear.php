<?php

session_start();

require_once '../../config/conexion.php';

if($_SERVER['REQUEST_METHOD']=='POST'){

$codigo=$_POST['codigo'];
$nombre=$_POST['nombre'];
$id_categoria=$_POST['id_categoria'];
$precio_compra=$_POST['precio_compra'];
$precio_venta=$_POST['precio_venta'];
$stock_minimo=$_POST['stock_minimo'];

$sql="INSERT INTO productos
(
codigo,
nombre,
id_categoria,
precio_compra,
precio_venta,
stock_minimo
)
VALUES
(
'$codigo',
'$nombre',
'$id_categoria',
'$precio_compra',
'$precio_venta',
'$stock_minimo'
)";

/** @var mysqli $conn */
mysqli_query($conn,$sql);

header("Location:index.php");
exit();
}

/** @var mysqli $conn */
$categorias=mysqli_query(
$conn,
"SELECT * FROM categorias WHERE estado=1"
);

?>

<!DOCTYPE html>
<html>
<head>

<title>Nuevo Producto</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2>Nuevo Producto</h2>

<form method="POST">

<div class="mb-3">
<label>Código</label>
<input type="text" name="codigo" class="form-control" required>
</div>

<div class="mb-3">
<label>Nombre</label>
<input type="text" name="nombre" class="form-control" required>
</div>

<div class="mb-3">
<label>Categoría</label>

<select name="id_categoria" class="form-control">

<?php while($cat=mysqli_fetch_assoc($categorias)){ ?>

<option value="<?= $cat['id_categoria']; ?>">
<?= $cat['nombre']; ?>
</option>

<?php } ?>

</select>

</div>

<div class="mb-3">
<label>Precio Compra</label>
<input type="number" step="0.01" name="precio_compra" class="form-control">
</div>

<div class="mb-3">
<label>Precio Venta</label>
<input type="number" step="0.01" name="precio_venta" class="form-control">
</div>

<div class="mb-3">
<label>Stock Mínimo</label>
<input type="number" name="stock_minimo" class="form-control">
</div>

<button class="btn btn-success">
Guardar
</button>

</form>

</div>

</body>
</html>