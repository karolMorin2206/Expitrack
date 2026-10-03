<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña - ExpiTrack</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/login.css">
</head>

<body>

<div class="container-fluid vh-100">

<div class="row h-100">

<div class="col-lg-6 left-panel">
<div class="brand-content">
<h1 class="logo-title">ExpiTrack</h1>
<p class="slogan">Recuperación segura de contraseña</p>
</div>
</div>

<div class="col-lg-6 login-panel">

<div class="login-card">

<h2 class="text-center mb-3">Recuperar contraseña</h2>

<?php if(isset($_GET['enviado'])){ ?>
<div class="alert alert-success">
Si el correo existe, recibirás un enlace para cambiar tu contraseña.
</div>
<?php } ?>

<form method="POST" action="../../controllers/forgot_password.php">

<div class="mb-3">
<label class="form-label">Correo electrónico</label>
<input type="email" name="correo" class="form-control" required>
</div>

<button type="submit" class="btn btn-login w-100">
Enviar enlace
</button>

<div class="text-center mt-3">
<a href="login.php">Volver al login</a>
</div>

</form>

</div>

</div>

</div>

</div>

</body>
</html>