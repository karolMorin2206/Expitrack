<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../config/conexion.php';


$correo = $_POST['correo'];
$password = md5($_POST['password']);

$sql = "SELECT u.*, r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.correo = ?
        AND u.password = ?
        AND u.estado = 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $correo,
    $password
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($resultado) > 0){

    $usuario = mysqli_fetch_assoc($resultado);

    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['id_sucursal'] = $usuario['id_sucursal'];

    header("Location: ../views/dashboard/index.php");
    exit();

}else{

    header("Location: ../views/auth/login.php?error=1");
    exit();
}