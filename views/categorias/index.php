<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$sql = "SELECT * FROM categorias WHERE estado = 1";
/** @var mysqli $conn */
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - ExpiTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#f4f6f9;
        }

        .sidebar{
            width:250px;
            position:fixed;
            height:100vh;
        }

        .content{
            margin-left:250px;
        }
    </style>

</head>
<body>

<div class="d-flex">

    <?php include '../layouts/sidebar.php'; ?>

    <div class="content w-100">

        <?php include '../layouts/navbar.php'; ?>

        <div class="container-fluid p-4">

            <div class="d-flex justify-content-between mb-3">

                <h2>Categorías</h2>

                <a href="crear.php" class="btn btn-success">
                    + Nueva Categoría
                </a>

            </div>

            <div class="card shadow">

                <div class="card-body">

                    <table class="table table-bordered table-hover">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php while($fila = mysqli_fetch_assoc($resultado)): ?>

                            <tr>

                                <td><?php echo $fila['id_categoria']; ?></td>

                                <td><?php echo $fila['nombre']; ?></td>

                                <td><?php echo $fila['descripcion']; ?></td>

                                <td>

                                    <a href="editar.php?id=<?php echo $fila['id_categoria']; ?>"
                                    class="btn btn-warning btn-sm">
                                        Editar
                                    </a>

                                    <a href="eliminar.php?id=<?php echo $fila['id_categoria']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Deseas eliminar esta categoría?')">
                                    Eliminar
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>