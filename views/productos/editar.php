<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$id = $_GET['id'];

if(isset($_POST['actualizar'])){

    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $id_categoria = $_POST['id_categoria'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock_minimo = $_POST['stock_minimo'];

    /** @var mysqli $conn */
    $sql = "UPDATE productos SET
            codigo='$codigo',
            nombre='$nombre',
            id_categoria='$id_categoria',
            precio_compra='$precio_compra',
            precio_venta='$precio_venta',
            stock_minimo='$stock_minimo'
            WHERE id_producto='$id'";

    mysqli_query($conn, $sql);

    header("Location: index.php");
}

/** @var mysqli $conn */
$sqlProducto = "SELECT * FROM productos WHERE id_producto='$id'";
$resultProducto = mysqli_query($conn, $sqlProducto);
$producto = mysqli_fetch_assoc($resultProducto);

$sqlCategorias = "SELECT * FROM categorias WHERE estado=1";
$categorias = mysqli_query($conn, $sqlCategorias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Producto</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h2>Editar Producto</h2>

<form method="POST">

<div class="mb-3">
<label>Código</label>
<input type="text" name="codigo" class="form-control"
value="<?php echo $producto['codigo']; ?>" required>
</div>

<div class="mb-3">
<label>Nombre</label>
<input type="text" name="nombre" class="form-control"
value="<?php echo $producto['nombre']; ?>" required>
</div>

<div class="mb-3">
<label>Categoría</label>

<select name="id_categoria" class="form-control">

<?php while($cat = mysqli_fetch_assoc($categorias)){ ?>

<option value="<?php echo $cat['id_categoria']; ?>"
<?php if($cat['id_categoria'] == $producto['id_categoria']) echo "selected"; ?>>

<?php echo $cat['nombre']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">
<label>Precio Compra</label>

<input type="number"
step="0.01"
name="precio_compra"
class="form-control"
value="<?php echo $producto['precio_compra']; ?>">
</div>

<div class="mb-3">
<label>Precio Venta</label>

<input type="number"
step="0.01"
name="precio_venta"
class="form-control"
value="<?php echo $producto['precio_venta']; ?>">
</div>

<div class="mb-3">
<label>Stock Mínimo</label>

<input type="number"
name="stock_minimo"
class="form-control"
value="<?php echo $producto['stock_minimo']; ?>">
</div>

<button type="submit" name="actualizar" class="btn btn-success">
Actualizar
</button>

<a href="index.php" class="btn btn-secondary">
Volver
</a>

</form>

</div>

</body>
</html>