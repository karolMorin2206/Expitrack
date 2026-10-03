<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    $sql = "UPDATE categorias
            SET estado = 0
            WHERE id_categoria = $id";
    /** @var mysqli $conn */
    mysqli_query($conn, $sql);

}

header("Location: index.php");
exit();