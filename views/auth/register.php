<?php
session_start();

require_once '../../config/conexion.php';

$sqlAdmin = "
SELECT COUNT(*) AS total
FROM usuarios
WHERE id_rol = 1
";


/** @var mysqli $conn */
$resAdmin = mysqli_query($conn, $sqlAdmin);
$dataAdmin = mysqli_fetch_assoc($resAdmin);

$existeAdmin = $dataAdmin['total'] > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Administrador - ExpiTrack</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/login.css">
</head>

<body>

<div class="container-fluid vh-100">

<div class="row h-100">

<div class="col-lg-6 left-panel">
<div class="brand-content">
<h1 class="logo-title">ExpiTrack</h1>
<p class="slogan">Registro del administrador principal</p>
</div>
</div>

<div class="col-lg-6 login-panel">

<div class="login-card">

<h2 class="text-center mb-3">Crear administrador</h2>

<?php if($existeAdmin){ ?>

<div class="alert alert-warning">
Ya existe una cuenta de administrador registrada.
</div>

<a href="login.php" class="btn btn-login w-100">
Volver al login
</a>

<?php }else{ ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 'password'){ ?>
<div class="alert alert-danger">
Las contraseñas no coinciden.
</div>
<?php } ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 'correo'){ ?>
<div class="alert alert-danger">
Ese correo ya está registrado.
</div>
<?php } ?>

<form method="POST" action="../../controllers/register.php">

<div class="mb-3">
<label class="form-label">Nombre</label>
<input type="text" name="nombre" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Correo electrónico</label>
<input type="email" name="correo" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Contraseña</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Confirmar contraseña</label>
<input type="password" name="confirmar" class="form-control" required>
</div>

<button type="submit" class="btn btn-login w-100">
Crear cuenta
</button>

<div class="text-center mt-3">
<a href="login.php">Volver al login</a>
</div>

</form>

<?php } ?>

</div>

</div>

</div>

</div>

</body>
</html>