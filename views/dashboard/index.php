<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */

/* TOTAL PRODUCTOS */
$sqlProductos = "
SELECT COUNT(*) total
FROM productos
WHERE estado = 1
";

$resProductos = mysqli_query($conn,$sqlProductos);
$totalProductos = mysqli_fetch_assoc($resProductos)['total'];

/* LOTES EN RIESGO */
$sqlRiesgo = "
SELECT COUNT(*) total
FROM lotes
WHERE estado = 1
AND fecha_caducidad BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
";

$resRiesgo = mysqli_query($conn,$sqlRiesgo);
$totalRiesgo = mysqli_fetch_assoc($resRiesgo)['total'];

/* LOTES VENCIDOS */
$sqlVencidos = "
SELECT COUNT(*) total
FROM lotes
WHERE estado = 1
AND fecha_caducidad < CURDATE()
";

$resVencidos = mysqli_query($conn,$sqlVencidos);
$totalVencidos = mysqli_fetch_assoc($resVencidos)['total'];

/* VALOR EN RIESGO */
$sqlValorRiesgo = "
SELECT SUM(
    p.precio_venta * l.cantidad
) valor
FROM lotes l
INNER JOIN productos p
ON l.id_producto = p.id_producto
WHERE l.fecha_caducidad BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
";

$resValorRiesgo = mysqli_query($conn,$sqlValorRiesgo);
$valorRiesgo = mysqli_fetch_assoc($resValorRiesgo)['valor'];

if(!$valorRiesgo){
    $valorRiesgo = 0;
}

/* STOCK BAJO */
$sqlStockBajo = "
SELECT COUNT(*) total
FROM lotes
WHERE estado = 1
AND cantidad <= 10
";

$resStockBajo = mysqli_query($conn,$sqlStockBajo);
$totalStockBajo = mysqli_fetch_assoc($resStockBajo)['total'];

/* ÚLTIMOS LOTES */
$sqlUltimos = "
SELECT
p.nombre,
l.numero_lote,
l.fecha_caducidad
FROM lotes l
INNER JOIN productos p
ON l.id_producto = p.id_producto
ORDER BY l.id_lote DESC
LIMIT 5
";

$ultimos = mysqli_query($conn,$sqlUltimos);
/* VENTAS HOY */
$sqlVentasHoy = "
SELECT COUNT(*) total
FROM ventas
WHERE DATE(fecha_venta) = CURDATE()
";

$resVentasHoy = mysqli_query($conn,$sqlVentasHoy);
$ventasHoy = mysqli_fetch_assoc($resVentasHoy)['total'];

/* VENTAS MES */
$sqlVentasMes = "
SELECT COUNT(*) total
FROM ventas
WHERE MONTH(fecha_venta)=MONTH(CURDATE())
AND YEAR(fecha_venta)=YEAR(CURDATE())
";

$resVentasMes = mysqli_query($conn,$sqlVentasMes);
$ventasMes = mysqli_fetch_assoc($resVentasMes)['total'];

/* INGRESOS MES */
$sqlIngresosMes = "
SELECT SUM(total) ingresos
FROM ventas
WHERE MONTH(fecha_venta)=MONTH(CURDATE())
AND YEAR(fecha_venta)=YEAR(CURDATE())
";

$resIngresosMes = mysqli_query($conn,$sqlIngresosMes);
$ingresosMes = mysqli_fetch_assoc($resIngresosMes)['ingresos'];

if(!$ingresosMes){
    $ingresosMes = 0;
}

/* VENTAS POR MES */
$sqlGrafica = "
SELECT
MONTH(fecha_venta) mes,
SUM(total) total
FROM ventas
GROUP BY MONTH(fecha_venta)
ORDER BY MONTH(fecha_venta)
";

$resGrafica = mysqli_query($conn,$sqlGrafica);

$meses = [];
$totales = [];

while($fila = mysqli_fetch_assoc($resGrafica)){
    $meses[] = $fila['mes'];
    $totales[] = $fila['total'];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Dashboard - ExpiTrack</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    background:linear-gradient(180deg,#f8fafc,#eef2f7);
}

.content{
    margin-left:280px;
}

.sidebar{
    width:280px;
    min-height:100vh;
    position:fixed;
    left:0;
    top:0;
    z-index:1000;

    background:linear-gradient(
        180deg,
        #0f172a 0%,
        #111827 50%,
        #1e293b 100%
    );

    box-shadow:8px 0 25px rgba(0,0,0,.20);
    padding:20px;
}

.logo-box{
    padding: 15px 20px 5px;
    text-align: center;
}

.logo-box h2{
    color:#fff;
    font-weight:700;
    margin:0;
    font-size:2rem;
}

.logo-box span{
    color:#94a3b8;
    font-size:11px;
    letter-spacing:2px;
    text-transform:uppercase;
}

.sidebar-divider{
    height:1px;
    background:rgba(255,255,255,.12);
    margin:20px 0;
}

.sidebar-title{
    color:#64748b;
    font-size:11px;
    letter-spacing:2px;
    font-weight:700;
    margin-bottom:15px;
    padding-left:12px;
}

.sidebar .nav-link{
    display:flex;
    align-items:center;
    gap:12px;

    color:#cbd5e1 !important;
    padding:13px 15px;
    border-radius:14px;
    margin-bottom:8px;

    transition:.3s;
    font-size:16px;
}

.sidebar .nav-link i{
    font-size:18px;
    background: transparent;
}

.sidebar .nav-link:hover{
    background:rgba(255,255,255,.08);
    transform:translateX(6px);
    color:white !important;
}

.sidebar .nav-link.active{
    background:linear-gradient(
        90deg,
        #2563eb,
        #3b82f6
    );

    color:white !important;
    font-weight:600;

    box-shadow:0 6px 18px rgba(37,99,235,.35);
}

.logout-container{
    position: sticky;
    bottom: 0;
    padding: 15px;
    z-index: 100;
    color:white !important;
}

.sidebar .btn-danger{
    border:none;
    border-radius:14px;
    padding:12px;

    font-weight:600;

    background:linear-gradient(
        135deg,
        #ef4444,
        #dc2626
    );

    transition:.3s;
}

.sidebar .btn-danger:hover{
    transform:translateY(-2px);
}

.user-panel{
    display:flex;
    align-items:center;
    gap:12px;
    margin:20px 0;
    padding:12px;
    color:#ffffff;
    border-radius:12px;
    margin: 5px 15px 12px;
}

.avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
}

.user-panel small{
    display:block;
    color:#9ca3af;
}

.sidebar{
    width:280px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;

    display:flex;
    flex-direction:column;
}

.sidebar-menu{
    flex:1;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:5px;

    /* espacio para el botón cerrar sesión */
    padding-bottom:90px;
}

/* Scroll moderno */
.sidebar-menu::-webkit-scrollbar{
    width:6px;
}

.sidebar-menu::-webkit-scrollbar-track{
    background:transparent;
}

.sidebar-menu::-webkit-scrollbar-thumb{
    background:#3b82f6;
    border-radius:20px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover{
    background:#60a5fa;
}

.logout-container{
    padding:15px;
    margin-top:auto;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 4px 20px rgba(0,0,0,.08);
    transition:.3s;
}

.card:hover{
    transform:translateY(-4px);
}

.dashboard-card{
    overflow:hidden;
    position:relative;
}
.card-link{
    text-decoration:none;
    color:inherit;
    display:block;
}

.card-link:hover{
    color:inherit;
}

.alert-link{
    text-decoration:none;
    color:inherit;
    display:block;
}

.alert-link:hover{
    color:inherit;
}

.dashboard-card{
    cursor:pointer;
}

.alert{
    cursor:pointer;
    transition:.3s;
}

.alert:hover{
    transform:translateX(5px);
}

.dashboard-card::before{
    content:'';
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:5px;
}

.border-blue::before{
    background:#2563eb;
}

.border-yellow::before{
    background:#f59e0b;
}

.border-red::before{
    background:#ef4444;
}

.border-green::before{
    background:#16a34a;
}

.border-cyan::before{
    background:#06b6d4;
}

.card-body{
    padding:22px;
}

.dashboard-card h6{
    font-size:15px;
    font-weight:600;
}

.dashboard-card h2{
    font-size:2rem;
    font-weight:700;
}

.alert{
    border:none;
    border-radius:14px;
}

.table{
    margin-top:15px;
}

.table thead{
    background:#111827;
    color:white;
}

.table td,
.table th{
    padding:14px;
    vertical-align:middle;
}

.table tbody tr:hover{
    background:#f3f4f6;
}

.welcome-card{
    padding:15px;
}

.welcome-title{
    font-size:2.2rem;
    font-weight:700;
}

.welcome-text{
    color:#6b7280;
    font-size:1.1rem;
}
</style>

</head>

<body>

<div class="d-flex">

<?php include '../layouts/sidebar.php'; ?>

<div class="content w-100">

<?php include '../layouts/navbar.php'; ?>

<div class="container-fluid p-4">


<!-- KPIs -->
<div class="row g-4">

    <div class="col-xl-3 col-md-6">

        <a href="../productos/index.php" class="card-link">

            <div class="card dashboard-card border-blue">
                <div class="card-body">
                    <h6>📦 Productos</h6>
                    <h2><?= $totalProductos ?></h2>
                </div>
            </div>

        </a>

    </div>

    <div class="col-xl-3 col-md-6">

        <a href="../caducidades/index.php" class="card-link">

            <div class="card dashboard-card border-yellow">
                <div class="card-body">
                    <h6 class="text-warning">⚠️ En Riesgo</h6>
                    <h2><?= $totalRiesgo ?></h2>
                </div>
            </div>

        </a>

    </div>

    <div class="col-xl-3 col-md-6">

        <a href="../lotes/index.php?estado=vencidos" class="card-link">

            <div class="card dashboard-card border-red">
                <div class="card-body">
                    <h6 class="text-danger">❌ Vencidos</h6>
                    <h2><?= $totalVencidos ?></h2>
                </div>
            </div>

        </a>

    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card border-green">
            <div class="card-body">
                <h6 class="text-success">💰 Valor Riesgo</h6>
                <h2>$<?= number_format($valorRiesgo,2) ?></h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">

        <a href="../lotes/index.php?stock=bajo" class="card-link">

            <div class="card dashboard-card border-cyan">
                <div class="card-body">
                    <h6 class="text-info">📉 Stock Bajo</h6>
                    <h2><?= $totalStockBajo ?></h2>
                </div>
            </div>

        </a>

    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card shadow border-0 bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white">🛒 Ventas Hoy</h6>
                <h2><?= $ventasHoy ?></h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card shadow border-0 bg-success text-white">
            <div class="card-body">
                <h6 class="text-white">📈 Ventas del Mes</h6>
                <h2><?= $ventasMes ?></h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card shadow border-0 bg-warning">
            <div class="card-body">
                <h6>💵 Ingresos del Mes</h6>
                <h2>$<?= number_format($ingresosMes,2) ?></h2>
            </div>
        </div>
    </div>

</div>

<!-- Bienvenida -->
<div class="card welcome-card border-0 mt-4">
    <div class="card-body">

        <h2 class="welcome-title">
            👋 Bienvenido a ExpiTrack
        </h2>

        <p class="welcome-text mb-0">
            Sistema inteligente para la gestión de inventarios y control de caducidades.
        </p>

    </div>
</div>

<!-- Alertas -->
<div class="mt-4">

    <a href="../lotes/index.php?estado=vencidos"
    class="alert-link">

        <div class="alert alert-danger shadow-sm">
            <strong>❌ Atención:</strong>
            Existen <?= $totalVencidos ?> lotes vencidos.
        </div>

    </a>

    <a href="../lotes/index.php?estado=riesgo"
    class="alert-link">

        <div class="alert alert-warning shadow-sm">
            <strong>⚠️ Advertencia:</strong>
            Existen <?= $totalRiesgo ?> lotes próximos a caducar.
        </div>

    </a>

    <a href="../lotes/index.php?stock=bajo"
    class="alert-link">

        <div class="alert alert-info shadow-sm">
            <strong>📉 Inventario Bajo:</strong>
            Existen <?= $totalStockBajo ?> lotes con pocas existencias.
        </div>

    </a>

</div>

<!-- Gráfica -->
<div class="card shadow border-0 mt-4">
    <div class="card-body">

        <h4 class="mb-4">📊 Ventas por Mes</h4>

        <div style="height:350px">
            <canvas id="graficaVentas"></canvas>
        </div>

    </div>
</div>

<!-- Últimos lotes -->
<div class="card shadow border-0 mt-4">
    <div class="card-body">

        <h4 class="mb-4">📦 Últimos Lotes Registrados</h4>

        <table class="table table-hover align-middle">

            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Caducidad</th>
                </tr>
            </thead>

            <tbody>

                <?php while($lote=mysqli_fetch_assoc($ultimos)){ ?>

                <tr>
                    <td><?= $lote['nombre'] ?></td>
                    <td><?= $lote['numero_lote'] ?></td>
                    <td><?= $lote['fecha_caducidad'] ?></td>
                </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>
</div>

</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('graficaVentas');

new Chart(ctx,{
    type:'line',
    data:{
        labels: <?= json_encode($meses); ?>,
        datasets:[{
            label:'Ingresos',
            data: <?= json_encode($totales); ?>,
            fill:true,
            tension:.4,
            borderWidth:3,
            backgroundColor:'rgba(37,99,235,.15)',
            borderColor:'#2563eb'
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                display:false
            }
        }
    }
});

</script>
</body>

</html>