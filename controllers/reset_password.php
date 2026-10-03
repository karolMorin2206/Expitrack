<?php
require_once __DIR__ . '/../config/conexion.php';

$token = $_POST['token'];
$password = $_POST['password'];
$confirmar = $_POST['confirmar'];

if($password !== $confirmar){
    header("Location: ../views/auth/reset.php?token=$token&error=1");
    exit();
}

$sql = "
SELECT id_usuario
FROM usuarios
WHERE reset_token = ?
AND reset_expira >= NOW()
LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($resultado) == 0){
    echo "El enlace no es válido o ya expiró.";
    exit();
}

$usuario = mysqli_fetch_assoc($resultado);

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sqlUpdate = "
UPDATE usuarios
SET password = ?,
    reset_token = NULL,
    reset_expira = NULL
WHERE id_usuario = ?
";

$stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpdate, "si", $passwordHash, $usuario['id_usuario']);
mysqli_stmt_execute($stmtUpdate);

header("Location: ../views/auth/login.php?reset=1");
exit();