<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */
if(isset($_GET['id'])){

    $id = $_GET['id'];

    $sql = "UPDATE productos
            SET estado = 0
            WHERE id_producto = '$id'";

    mysqli_query($conn, $sql);
}

header("Location: index.php");
exit();
?>