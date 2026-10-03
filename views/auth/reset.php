<?php
require_once '../../config/conexion.php';

if(!isset($_GET['token'])){
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

$sql = "
SELECT id_usuario
FROM usuarios
WHERE reset_token = ?
AND reset_expira >= NOW()
LIMIT 1
";

/** @var mysqli $conn */
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($resultado) == 0){
    echo "El enlace no es válido o ya expiró.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva contraseña - ExpiTrack</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/login.css">
</head>

<body>

<div class="container-fluid vh-100">

<div class="row h-100">

<div class="col-lg-6 left-panel">
<div class="brand-content">
<h1 class="logo-title">ExpiTrack</h1>
<p class="slogan">Crea una nueva contraseña</p>
</div>
</div>

<div class="col-lg-6 login-panel">

<div class="login-card">

<h2 class="text-center mb-3">Nueva contraseña</h2>

<?php if(isset($_GET['error'])){ ?>
<div class="alert alert-danger">
Las contraseñas no coinciden.
</div>
<?php } ?>

<form method="POST" action="../../controllers/reset_password.php">

<input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

<div class="mb-3">
<label class="form-label">Nueva contraseña</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Confirmar contraseña</label>
<input type="password" name="confirmar" class="form-control" required>
</div>

<button type="submit" class="btn btn-login w-100">
Actualizar contraseña
</button>

</form>

</div>

</div>

</div>

</div>

</body>
</html>