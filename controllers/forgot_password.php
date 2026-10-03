<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/mail.php';

$correo = $_POST['correo'];

$sql = "
SELECT id_usuario, nombre, correo
FROM usuarios
WHERE correo = ?
LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($resultado) == 0){
    header("Location: ../views/auth/forgot.php?enviado=1");
    exit();
}

$usuario = mysqli_fetch_assoc($resultado);

$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

$sqlUpdate = "
UPDATE usuarios
SET reset_token = ?,
    reset_expira = ?
WHERE id_usuario = ?
";

$stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpdate, "ssi", $token, $expira, $usuario['id_usuario']);
mysqli_stmt_execute($stmtUpdate);

$link = APP_URL . "/views/auth/reset.php?token=" . $token;

$contenido = "
<h2>Recuperar contraseña</h2>
<p>Hola, {$usuario['nombre']}.</p>
<p>Para cambiar tu contraseña, haz clic en el siguiente botón:</p>
<p>
<a href='$link' style='background:#0f8f7f;color:white;padding:12px 18px;text-decoration:none;border-radius:8px;'>
Cambiar contraseña
</a>
</p>
<p>Este enlace vence en 1 hora.</p>
<p>Si no solicitaste este cambio, ignora este correo.</p>
";

enviarCorreo($usuario['correo'], $usuario['nombre'], "Recuperar contraseña - ExpiTrack", $contenido);

header("Location: ../views/auth/forgot.php?enviado=1");
exit();