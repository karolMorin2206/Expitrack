<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO categorias(nombre, descripcion)
            VALUES('$nombre','$descripcion')";
    /** @var mysqli $conn */
    if(mysqli_query($conn, $sql)){
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva Categoría</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header">
            <h3>Nueva Categoría</h3>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label>Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label>Descripción</label>
                    <textarea
                        name="descripcion"
                        class="form-control">
                    </textarea>
                </div>

                <button class="btn btn-success">
                    Guardar
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Volver
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>