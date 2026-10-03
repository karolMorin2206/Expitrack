<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

$filtro = "";

if(isset($_GET['estado']) && $_GET['estado'] == 'vencidos'){

    $filtro = " AND l.fecha_caducidad < CURDATE() ";

}

if(isset($_GET['stock']) && $_GET['stock'] == 'bajo'){

    $filtro = " AND l.cantidad <= 10 ";

}

if(isset($_GET['estado']) && $_GET['estado'] == 'riesgo'){

    $filtro = "
    AND l.fecha_caducidad BETWEEN CURDATE()
    AND DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
    ";

}

$sql = "
SELECT
    l.*,
    p.nombre AS producto
FROM lotes l
INNER JOIN productos p
    ON l.id_producto = p.id_producto
WHERE l.estado = 1
$filtro
ORDER BY l.fecha_caducidad ASC
";

/** @var mysqli $conn */
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lotes - ExpiTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

    <?php

    $titulo = "Todos los Lotes";

        if(isset($_GET['estado']) && $_GET['estado'] == 'vencidos'){
            $titulo = "🔴 Lotes Vencidos";
        }

        if(isset($_GET['estado']) && $_GET['estado'] == 'riesgo'){
            $titulo = "🟡 Lotes Próximos a Caducar";
        }

        if(isset($_GET['stock']) && $_GET['stock'] == 'bajo'){
            $titulo = "📉 Lotes con Stock Bajo";
        }

    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2><?= $titulo ?></h2>

        <a href="crear.php" class="btn btn-success">
            + Nuevo Lote
        </a>

    </div>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">

        <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Lote</th>
            <th>Cantidad</th>
            <th>Ingreso</th>
            <th>Caducidad</th>
            <th>Semáforo</th>
            <th>Acciones</th>
        </tr>

        </thead>

        <tbody>

        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

            <?php

            $hoy = new DateTime();
            $caduca = new DateTime($fila['fecha_caducidad']);

            $diferencia = $hoy->diff($caduca);

            $meses =
                ($diferencia->y * 12)
                +
                $diferencia->m;

            if($caduca < $hoy){

                $estado = "🔴 Rojo";
                $clase = "table-danger";

            }elseif($meses > 8){

                $estado = "🟢 Verde";
                $clase = "table-success";

            }elseif($meses > 2){

                $estado = "🟡 Amarillo";
                $clase = "table-warning";

            }else{

                $estado = "🔴 Rojo";
                $clase = "table-danger";
            }

            ?>

            <tr class="<?= $clase; ?>">

                <td><?= $fila['id_lote']; ?></td>

                <td><?= $fila['producto']; ?></td>

                <td><?= $fila['numero_lote']; ?></td>

                <td><?= $fila['cantidad']; ?></td>

                <td><?= $fila['fecha_ingreso']; ?></td>

                <td><?= $fila['fecha_caducidad']; ?></td>

                <td>
                    <strong><?= $estado; ?></strong>
                </td>

                <td>

                    <a
                        href="editar.php?id=<?= $fila['id_lote']; ?>"
                        class="btn btn-warning btn-sm">
                        Editar
                    </a>

                    <a
                        href="eliminar.php?id=<?= $fila['id_lote']; ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('¿Eliminar lote?')">
                        Eliminar
                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</body>

</html>