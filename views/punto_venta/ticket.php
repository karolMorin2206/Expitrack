<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$id_venta = $_GET['id'];

$sqlVenta = "
SELECT
    v.*,
    u.nombre AS usuario,
    s.nombre AS sucursal
FROM ventas v
INNER JOIN usuarios u
    ON v.id_usuario = u.id_usuario
INNER JOIN sucursales s
    ON v.id_sucursal = s.id_sucursal
WHERE v.id_venta = '$id_venta'
";

$resVenta = mysqli_query($conn, $sqlVenta);
$venta = mysqli_fetch_assoc($resVenta);

if(!$venta){
    echo "Venta no encontrada";
    exit();
}

$sqlDetalle = "
SELECT
    d.*,
    p.nombre AS producto
FROM detalle_ventas d
INNER JOIN productos p
    ON d.id_producto = p.id_producto
WHERE d.id_venta = '$id_venta'
";

$detalle = mysqli_query($conn, $sqlDetalle);

$logo = "../../assets/img/logo.png";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Venta - ExpiTrack</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:linear-gradient(135deg,#eef2f7,#dde7f2);
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.ticket{
    width:420px;
    margin:35px auto;
    background:white;
    border-radius:22px;
    padding:28px;
    box-shadow:0 20px 45px rgba(15,23,42,.18);
}

.logo-ticket{
    display:block;
    width:95px;
    height:95px;
    object-fit:contain;
    margin:0 auto 10px;
}

.ticket h2{
    text-align:center;
    font-weight:900;
    margin:0;
    color:#0f172a;
    font-size:2.1rem;
}

.ticket .subtitle{
    text-align:center;
    color:#64748b;
    margin-bottom:15px;
    font-size:.95rem;
}

.line{
    border-top:1px dashed #94a3b8;
    margin:18px 0;
}

.info p{
    margin:5px 0;
    font-size:15px;
}

.info strong{
    color:#0f172a;
}

.table-ticket{
    width:100%;
    font-size:14px;
}

.table-ticket th{
    color:#0f172a;
    padding-bottom:8px;
    border-bottom:1px solid #e2e8f0;
}

.table-ticket td{
    padding:8px 0;
    border-bottom:1px solid #f1f5f9;
}

.total-box{
    background:#0f172a;
    color:white;
    border-radius:18px;
    padding:18px;
    margin-top:16px;
}

.total-line{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
}

.total-final{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-top:1px solid rgba(255,255,255,.2);
    padding-top:12px;
    margin-top:10px;
}

.total-final strong{
    font-size:1.8rem;
}

.payment-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:15px;
    margin-top:15px;
}

.payment-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
    font-size:15px;
}

.payment-row:last-child{
    margin-bottom:0;
}

.thanks{
    text-align:center;
    color:#64748b;
    margin-top:18px;
    font-size:14px;
}

.btns{
    display:flex;
    gap:10px;
    justify-content:center;
    margin-top:22px;
}

.btn{
    border-radius:12px;
    font-weight:700;
    padding:10px 14px;
}

@media print{
    body{
        background:white;
    }

    .ticket{
        box-shadow:none;
        margin:0 auto;
        width:100%;
        border-radius:0;
    }

    .btns{
        display:none;
    }
}
</style>
</head>

<body>

<div class="ticket">

    <img src="<?= $logo; ?>" class="logo-ticket" alt="Logo ExpiTrack">

    <h2>ExpiTrack</h2>

    <div class="subtitle">
        Sistema de Inventario y Punto de Venta
    </div>

    <div class="line"></div>

    <div class="info">
        <p><strong>Ticket:</strong> #<?= $venta['id_venta']; ?></p>
        <p><strong>Fecha:</strong> <?= $venta['fecha_venta']; ?></p>
        <p><strong>Sucursal:</strong> <?= $venta['sucursal']; ?></p>
        <p><strong>Cajero:</strong> <?= $venta['usuario']; ?></p>
        <p><strong>Método:</strong> <?= $venta['metodo_pago']; ?></p>
    </div>

    <div class="line"></div>

    <table class="table-ticket">

        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>

        <tbody>

        <?php while($fila = mysqli_fetch_assoc($detalle)){ ?>

            <tr>
                <td><?= $fila['producto']; ?></td>
                <td class="text-center"><?= $fila['cantidad']; ?></td>
                <td class="text-end">
                    $<?= number_format($fila['precio'] * $fila['cantidad'],2); ?>
                </td>
            </tr>

        <?php } ?>

        </tbody>

    </table>

    <div class="total-box">

        <div class="total-line">
            <span>Subtotal</span>
            <strong>$<?= number_format($venta['total'],2); ?></strong>
        </div>

        <div class="total-final">
            <span>Total</span>
            <strong>$<?= number_format($venta['total'],2); ?></strong>
        </div>

    </div>

    <div class="payment-box">

        <div class="payment-row">
            <span>Pago recibido:</span>
            <strong>$<?= number_format($venta['pago_recibido'],2); ?></strong>
        </div>

        <div class="payment-row">
            <span>Cambio:</span>
            <strong>$<?= number_format($venta['cambio'],2); ?></strong>
        </div>

        <?php if(isset($venta['monto_efectivo']) && $venta['monto_efectivo'] > 0){ ?>
        <div class="payment-row">
            <span>Efectivo:</span>
            <strong>$<?= number_format($venta['monto_efectivo'],2); ?></strong>
        </div>
        <?php } ?>

        <?php if(isset($venta['monto_tarjeta']) && $venta['monto_tarjeta'] > 0){ ?>
        <div class="payment-row">
            <span>Tarjeta:</span>
            <strong>$<?= number_format($venta['monto_tarjeta'],2); ?></strong>
        </div>
        <?php } ?>

        <?php if(isset($venta['monto_fiado']) && $venta['monto_fiado'] > 0){ ?>
        <div class="payment-row">
            <span>Fiado:</span>
            <strong>$<?= number_format($venta['monto_fiado'],2); ?></strong>
        </div>
        <?php } ?>

        <?php if(!empty($venta['codigo_transaccion'])){ ?>
        <div class="payment-row">
            <span>Código transacción:</span>
            <strong><?= $venta['codigo_transaccion']; ?></strong>
        </div>
        <?php } ?>

    </div>

    <div class="line"></div>

    <div class="thanks">
        <p class="mb-1">Gracias por su compra</p>
        <p class="mb-0">ExpiTrack © <?= date('Y'); ?></p>
    </div>

    <div class="btns">

        <button onclick="window.print()" class="btn btn-dark">
            <i class="bi bi-printer"></i>
            Imprimir
        </button>

        <a href="index.php" class="btn btn-primary">
            <i class="bi bi-cart-plus"></i>
            Nueva Venta
        </a>

    </div>

</div>

</body>
</html>