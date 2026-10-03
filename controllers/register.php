<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/mail.php';

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = $_POST['password'];
$confirmar = $_POST['confirmar'];

if($password !== $confirmar){
    header("Location: ../views/auth/register.php?error=password");
    exit();
}

$sqlAdmin = "
SELECT COUNT(*) AS total
FROM usuarios
WHERE id_rol = 1
";

$resAdmin = mysqli_query($conn, $sqlAdmin);
$dataAdmin = mysqli_fetch_assoc($resAdmin);

if($dataAdmin['total'] > 0){
    header("Location: ../views/auth/login.php");
    exit();
}

$sqlCorreo = "
SELECT id_usuario
FROM usuarios
WHERE correo = ?
LIMIT 1
";

$stmtCorreo = mysqli_prepare($conn, $sqlCorreo);
mysqli_stmt_bind_param($stmtCorreo, "s", $correo);
mysqli_stmt_execute($stmtCorreo);

$resCorreo = mysqli_stmt_get_result($stmtCorreo);

if(mysqli_num_rows($resCorreo) > 0){
    header("Location: ../views/auth/register.php?error=correo");
    exit();
}

$sqlSucursal = "
SELECT id_sucursal
FROM sucursales
WHERE estado = 1
ORDER BY id_sucursal ASC
LIMIT 1
";

$resSucursal = mysqli_query($conn, $sqlSucursal);
$sucursal = mysqli_fetch_assoc($resSucursal);

$id_sucursal = $sucursal ? $sucursal['id_sucursal'] : 1;

$id_rol = 1;
$estado = 0;
$email_verificado = 0;
$token = bin2hex(random_bytes(32));
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sqlInsert = "
INSERT INTO usuarios(
    nombre,
    correo,
    password,
    id_rol,
    id_sucursal,
    estado,
    email_verificado,
    verification_token
)
VALUES(
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?
)
";

$stmt = mysqli_prepare($conn, $sqlInsert);

mysqli_stmt_bind_param(
    $stmt,
    "sssiiiis",
    $nombre,
    $correo,
    $passwordHash,
    $id_rol,
    $id_sucursal,
    $estado,
    $email_verificado,
    $token
);

if(!mysqli_stmt_execute($stmt)){
    die("Error al crear usuario: " . mysqli_error($conn));
}

$link = APP_URL . "/views/auth/verify.php?token=" . $token;

$contenido = "
<h2>Verifica tu cuenta de ExpiTrack</h2>
<p>Hola, $nombre.</p>
<p>Para activar tu cuenta de administrador, haz clic en el siguiente botón:</p>
<p>
<a href='$link' style='background:#0f8f7f;color:white;padding:12px 18px;text-decoration:none;border-radius:8px;'>
Verificar cuenta
</a>
</p>
<p>Si el botón no funciona, copia este enlace:</p>
<p>$link</p>
";

enviarCorreo($correo, $nombre, "Verificación de cuenta - ExpiTrack", $contenido);

header("Location: ../views/auth/login.php?registro=1");
exit();