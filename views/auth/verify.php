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
WHERE verification_token = ?
LIMIT 1
";

/** @var mysqli $conn */
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($resultado) == 0){
    echo "Token inválido o cuenta ya verificada.";
    exit();
}

$usuario = mysqli_fetch_assoc($resultado);

$sqlUpdate = "
UPDATE usuarios
SET 
    email_verificado = 1,
    estado = 1,
    verification_token = NULL
WHERE id_usuario = ?
";

/** @var mysqli $conn */
$stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpdate, "i", $usuario['id_usuario']);
mysqli_stmt_execute($stmtUpdate);

header("Location: login.php?verificado=1");
exit();