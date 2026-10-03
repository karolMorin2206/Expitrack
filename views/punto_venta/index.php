<?php
session_start();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';

/** @var mysqli $conn */

function e($valor){
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/* =========================
   INICIALIZAR CARRITO
========================= */
if(!isset($_SESSION['carrito'])){
    $_SESSION['carrito'] = [];
}

$mensaje = "";
$tipo_mensaje = "success";

/* =========================
   QUITAR PRODUCTO DEL CARRITO
========================= */
if(isset($_GET['quitar'])){
    $idQuitar = (int) $_GET['quitar'];

    if(isset($_SESSION['carrito'][$idQuitar])){
        unset($_SESSION['carrito'][$idQuitar]);
    }

    header("Location: index.php");
    exit();
}

/* =========================
   VACIAR CARRITO
========================= */
if(isset($_POST['vaciar_carrito'])){
    $_SESSION['carrito'] = [];
    header("Location: index.php");
    exit();
}

/* =========================
   AGREGAR AL CARRITO
========================= */
if(isset($_POST['agregar_carrito'])){

    $id_producto = (int) $_POST['id_producto'];
    $cantidad = (int) $_POST['cantidad'];

    if($id_producto <= 0 || $cantidad <= 0){

        $mensaje = "Debes seleccionar un producto y una cantidad válida.";
        $tipo_mensaje = "danger";

    }else{

        $sqlProducto = "
        SELECT
            p.id_producto,
            p.codigo,
            p.nombre,
            p.precio_venta,
            COALESCE(SUM(l.cantidad),0) AS stock
        FROM productos p
        LEFT JOIN lotes l
            ON p.id_producto = l.id_producto
            AND l.estado = 1
            AND l.cantidad > 0
            AND l.fecha_caducidad >= CURDATE()
        WHERE p.id_producto = '$id_producto'
        AND p.estado = 1
        GROUP BY p.id_producto, p.codigo, p.nombre, p.precio_venta
        ";
     /** @var mysqli $conn */
        $resProducto = mysqli_query($conn, $sqlProducto);
        $producto = mysqli_fetch_assoc($resProducto);

        if(!$producto){

            $mensaje = "El producto seleccionado no existe.";
            $tipo_mensaje = "danger";

        }else{

            $stockDisponible = (int)$producto['stock'];
            $cantidadActual = $_SESSION['carrito'][$id_producto]['cantidad'] ?? 0;
            $cantidadTotal = $cantidadActual + $cantidad;

            if($cantidadTotal > $stockDisponible){

                $mensaje = "No hay suficiente stock. Disponible: " . $stockDisponible;
                $tipo_mensaje = "danger";

            }else{

                $_SESSION['carrito'][$id_producto] = [
                    'id_producto' => $producto['id_producto'],
                    'codigo' => $producto['codigo'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio_venta'],
                    'cantidad' => $cantidadTotal
                ];

                $mensaje = "Producto agregado al carrito correctamente.";
                $tipo_mensaje = "success";
            }
        }
    }
}

/* =========================
   COBRAR VENTA
========================= */
if(isset($_POST['cobrar_venta'])){

    if(empty($_SESSION['carrito'])){

        $mensaje = "El carrito está vacío.";
        $tipo_mensaje = "danger";

    }else{

        $metodo_pago = $_POST['metodo_pago'];

        $total = 0;

        foreach($_SESSION['carrito'] as $item){
            $total += $item['precio'] * $item['cantidad'];
        }

        $monto_efectivo = 0;
        $monto_tarjeta = 0;
        $monto_fiado = 0;
        $codigo_transaccion = "";
        $pago_recibido = 0;
        $cambio = 0;
        $puedeGuardar = true;

        if($metodo_pago == "EFECTIVO"){

            $monto_efectivo = floatval($_POST['pago_recibido']);
            $pago_recibido = $monto_efectivo;
            $cambio = $monto_efectivo - $total;

            if($monto_efectivo < $total){
                $mensaje = "El efectivo recibido no puede ser menor al total.";
                $tipo_mensaje = "danger";
                $puedeGuardar = false;
            }

        }elseif($metodo_pago == "TARJETA"){

            $monto_tarjeta = $total;
            $pago_recibido = $total;
            $cambio = 0;

            /** @var mysqli $conn */
            $codigo_transaccion = mysqli_real_escape_string(
                $conn,
                $_POST['codigo_transaccion']
            );

            if(trim($codigo_transaccion) == ""){
                $mensaje = "Debes ingresar el código de transacción de la tarjeta.";
                $tipo_mensaje = "danger";
                $puedeGuardar = false;
            }

        }elseif($metodo_pago == "FIADO"){

            $monto_fiado = $total;
            $pago_recibido = 0;
            $cambio = 0;

        }elseif($metodo_pago == "MIXTO"){

            $monto_efectivo = floatval($_POST['monto_efectivo']);
            $monto_tarjeta = floatval($_POST['monto_tarjeta']);
            $monto_fiado = floatval($_POST['monto_fiado']);
            /** @var mysqli $conn */
            $codigo_transaccion = mysqli_real_escape_string(
                $conn,
                $_POST['codigo_transaccion']
            );

            $suma_pago = $monto_efectivo + $monto_tarjeta + $monto_fiado;

            $pago_recibido = $monto_efectivo + $monto_tarjeta;
            $cambio = $suma_pago - $total;

            if($suma_pago < $total){
                $mensaje = "La suma del pago mixto no cubre el total de la venta.";
                $tipo_mensaje = "danger";
                $puedeGuardar = false;
            }

            if($monto_tarjeta > 0 && trim($codigo_transaccion) == ""){
                $mensaje = "Debes ingresar el código de transacción para el pago con tarjeta.";
                $tipo_mensaje = "danger";
                $puedeGuardar = false;
            }

            if($cambio < 0){
                $cambio = 0;
            }

        }else{

            $mensaje = "Método de pago no válido.";
            $tipo_mensaje = "danger";
            $puedeGuardar = false;
        }

        if($puedeGuardar){
            /** @var mysqli $conn */
            mysqli_begin_transaction($conn);

            try{

                $id_usuario = $_SESSION['id_usuario'];
                $id_sucursal = $_SESSION['id_sucursal'];

                $sqlVenta = "
                INSERT INTO ventas(
                    id_usuario,
                    total,
                    id_sucursal,
                    metodo_pago,
                    pago_recibido,
                    cambio,
                    monto_efectivo,
                    monto_tarjeta,
                    monto_fiado,
                    codigo_transaccion
                )
                VALUES(
                    '$id_usuario',
                    '$total',
                    '$id_sucursal',
                    '$metodo_pago',
                    '$pago_recibido',
                    '$cambio',
                    '$monto_efectivo',
                    '$monto_tarjeta',
                    '$monto_fiado',
                    '$codigo_transaccion'
                )
                ";

                if(!mysqli_query($conn, $sqlVenta)){
                    throw new Exception("Error al guardar la venta: " . mysqli_error($conn));
                }

                $id_venta = mysqli_insert_id($conn);

                foreach($_SESSION['carrito'] as $item){

                    $id_producto = (int)$item['id_producto'];
                    $cantidad_vender = (int)$item['cantidad'];
                    $precio = $item['precio'];

                    $sqlStock = "
                    SELECT COALESCE(SUM(cantidad),0) AS stock
                    FROM lotes
                    WHERE id_producto = '$id_producto'
                    AND estado = 1
                    AND cantidad > 0
                    AND fecha_caducidad >= CURDATE()
                    ";

                    $resStock = mysqli_query($conn, $sqlStock);
                    $stockActual = mysqli_fetch_assoc($resStock);

                    if((int)$stockActual['stock'] < $cantidad_vender){
                        throw new Exception("No hay suficiente stock disponible para el producto: " . $item['nombre']);
                    }

                    $sqlDetalle = "
                    INSERT INTO detalle_ventas(
                        id_venta,
                        id_producto,
                        cantidad,
                        precio
                    )
                    VALUES(
                        '$id_venta',
                        '$id_producto',
                        '$cantidad_vender',
                        '$precio'
                    )
                    ";

                    if(!mysqli_query($conn, $sqlDetalle)){
                        throw new Exception("Error al guardar detalle de venta: " . mysqli_error($conn));
                    }

                    /* FIFO: descuenta primero el lote que caduca antes */
                    $restante = $cantidad_vender;

                    $sqlLotes = "
                    SELECT *
                    FROM lotes
                    WHERE id_producto = '$id_producto'
                    AND estado = 1
                    AND cantidad > 0
                    AND fecha_caducidad >= CURDATE()
                    ORDER BY fecha_caducidad ASC, id_lote ASC
                    ";

                    $resLotes = mysqli_query($conn, $sqlLotes);

                    while($lote = mysqli_fetch_assoc($resLotes)){

                        if($restante <= 0){
                            break;
                        }

                        $id_lote = $lote['id_lote'];
                        $stock_lote = $lote['cantidad'];

                        if($stock_lote >= $restante){

                            $nuevo_stock = $stock_lote - $restante;

                            $sqlUpdate = "
                            UPDATE lotes
                            SET cantidad = '$nuevo_stock'
                            WHERE id_lote = '$id_lote'
                            ";

                            if(!mysqli_query($conn, $sqlUpdate)){
                                throw new Exception("Error al actualizar inventario: " . mysqli_error($conn));
                            }

                            $restante = 0;

                        }else{

                            $sqlUpdate = "
                            UPDATE lotes
                            SET cantidad = 0
                            WHERE id_lote = '$id_lote'
                            ";

                            if(!mysqli_query($conn, $sqlUpdate)){
                                throw new Exception("Error al actualizar inventario: " . mysqli_error($conn));
                            }

                            $restante -= $stock_lote;
                        }
                    }

                    if($restante > 0){
                        throw new Exception("No se pudo descontar todo el stock del producto: " . $item['nombre']);
                    }
                }

                mysqli_commit($conn);

                $_SESSION['carrito'] = [];

                header("Location: ticket.php?id=" . $id_venta);
                exit();

            }catch(Exception $e){

                mysqli_rollback($conn);

                $mensaje = $e->getMessage();
                $tipo_mensaje = "danger";
            }
        }
    }
}

/* =========================
   OBTENER PRODUCTOS
========================= */
$sqlProductos = "
SELECT
    p.id_producto,
    p.codigo,
    p.nombre,
    p.precio_venta,
    COALESCE(SUM(l.cantidad),0) AS stock
FROM productos p
LEFT JOIN lotes l
    ON p.id_producto = l.id_producto
    AND l.estado = 1
    AND l.cantidad > 0
    AND l.fecha_caducidad >= CURDATE()
WHERE p.estado = 1
GROUP BY p.id_producto, p.codigo, p.nombre, p.precio_venta
HAVING stock > 0
ORDER BY p.nombre ASC
";
/** @var mysqli $conn */
$resProductos = mysqli_query($conn, $sqlProductos);
$productos = [];

while($fila = mysqli_fetch_assoc($resProductos)){
    $productos[] = $fila;
}

$productosJS = json_encode(
    $productos,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

/* =========================
   TOTALES DEL CARRITO
========================= */
$totalCarrito = 0;
$totalItems = 0;

foreach($_SESSION['carrito'] as $item){
    $totalCarrito += $item['precio'] * $item['cantidad'];
    $totalItems += $item['cantidad'];
}

$logoRuta = "../../assets/img/logo.png";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Punto de Venta - ExpiTrack</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root{
    --azul:#0f172a;
    --azul2:#1e3a8a;
    --celeste:#2563eb;
    --verde:#16a34a;
    --gris:#eef2f7;
    --texto:#0f172a;
    --rojo:#ef4444;
}

body{
    background:linear-gradient(135deg,#eef2f7 0%,#dde7f2 100%);
    font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color:var(--texto);
    min-height:100vh;
}

.page-wrap{
    padding:28px;
}

.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:24px;
    flex-wrap:wrap;
}

.page-title h1{
    font-size:2.35rem;
    font-weight:800;
    margin:0;
    display:flex;
    align-items:center;
    gap:12px;
}

.page-title p{
    margin:8px 0 0 0;
    color:#475569;
    font-size:1.05rem;
}

.status-badge{
    background:linear-gradient(135deg,#16a34a,#15803d);
    color:#fff;
    padding:14px 18px;
    border-radius:16px;
    font-weight:700;
    box-shadow:0 10px 25px rgba(22,163,74,.25);
}

.brand-hero{
    background:linear-gradient(135deg,#0b1220 0%,#0f172a 45%,#12315d 100%);
    border-radius:24px;
    padding:28px;
    color:#fff;
    position:relative;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(15,23,42,.25);
    margin-bottom:24px;
}

.brand-hero::after{
    content:"";
    position:absolute;
    right:-30px;
    top:-30px;
    width:220px;
    height:220px;
    background:radial-gradient(circle,rgba(37,99,235,.35) 0%,rgba(37,99,235,0) 70%);
}

.brand-content{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    position:relative;
    z-index:2;
}

.brand-left{
    display:flex;
    align-items:center;
    gap:18px;
}

.brand-logo{
    width:82px;
    height:82px;
    object-fit:contain;
    border-radius:18px;
    background:rgba(255,255,255,.08);
    padding:10px;
    box-shadow:0 8px 30px rgba(0,0,0,.18);
}

.brand-text h2{
    margin:0;
    font-size:2rem;
    font-weight:800;
}

.brand-text p{
    margin:6px 0 0;
    color:#cbd5e1;
    font-size:1rem;
}

.brand-right{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.mini-stat{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.08);
    border-radius:18px;
    padding:14px 18px;
    min-width:140px;
    backdrop-filter:blur(8px);
}

.mini-stat small{
    display:block;
    color:#cbd5e1;
    margin-bottom:4px;
}

.mini-stat strong{
    font-size:1.2rem;
    color:#fff;
}

.modern-card{
    background:#fff;
    border:none;
    border-radius:24px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
    overflow:hidden;
}

.card-header-custom{
    padding:24px 26px 8px;
    border:none;
    background:#fff;
}

.card-header-custom h3{
    margin:0;
    font-weight:800;
    font-size:1.25rem;
    display:flex;
    align-items:center;
    gap:10px;
}

.card-body-custom{
    padding:22px 26px 26px;
}

.section-subtitle{
    color:#64748b;
    margin-top:4px;
    margin-bottom:0;
    font-size:.95rem;
}

.form-label{
    font-weight:700;
    color:#334155;
    margin-bottom:8px;
}

.form-control,
.form-select{
    border-radius:16px;
    min-height:56px;
    border:1px solid #dbe2ea;
    font-size:1rem;
    padding-left:16px;
    box-shadow:none!important;
}

.form-control:focus,
.form-select:focus{
    border-color:#3b82f6;
}

.btn-main{
    height:58px;
    border:none;
    border-radius:18px;
    font-size:1.1rem;
    font-weight:800;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    box-shadow:0 12px 28px rgba(37,99,235,.25);
    transition:.25s ease;
    color:#fff;
}

.btn-main:hover{
    transform:translateY(-2px);
    color:#fff;
}

.btn-soft-danger{
    border-radius:14px;
    border:1px solid #fecaca;
    color:#dc2626;
    background:#fff5f5;
    font-weight:700;
    padding:10px 16px;
}

.btn-soft-danger:hover{
    background:#fee2e2;
    color:#b91c1c;
}

.cart-table{
    margin-bottom:0;
}

.cart-table thead th{
    background:#111827;
    color:#fff;
    border:none;
    padding:14px 12px;
    font-size:.95rem;
}

.cart-table tbody td{
    padding:14px 12px;
    vertical-align:middle;
    border-color:#edf2f7;
}

.empty-cart{
    text-align:center;
    color:#64748b;
    padding:28px 0;
    font-size:1.05rem;
}

.total-panel{
    background:linear-gradient(135deg,#020b2d,#08163f 55%,#0a234f);
    color:#fff;
    border-radius:24px;
    padding:24px 28px;
    margin-top:18px;
    box-shadow:0 16px 34px rgba(2,11,45,.28);
}

.total-line{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
    font-size:1.15rem;
}

.total-line.total{
    border-top:1px solid rgba(255,255,255,.18);
    padding-top:16px;
    margin-top:14px;
    font-size:1.4rem;
    font-weight:800;
}

.total-amount{
    font-size:3.2rem;
    font-weight:900;
    line-height:1;
}

.payment-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    margin-top:18px;
}

.btn-cobrar{
    width:100%;
    margin-top:20px;
    height:62px;
    border:none;
    border-radius:18px;
    font-size:1.25rem;
    font-weight:800;
    background:linear-gradient(135deg,#74c69d,#40916c);
    color:#fff;
    box-shadow:0 14px 28px rgba(64,145,108,.25);
}

.btn-cobrar:hover{
    color:#fff;
    transform:translateY(-2px);
}

.info-boxes{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-top:18px;
}

.info-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:16px 18px;
}

.info-box small{
    color:#64748b;
    display:block;
    margin-bottom:5px;
}

.info-box strong{
    font-size:1.1rem;
}

.alert{
    border-radius:18px;
    border:none;
    padding:16px 18px;
    font-weight:600;
    box-shadow:0 10px 20px rgba(15,23,42,.05);
}

.remove-btn{
    width:36px;
    height:36px;
    border:none;
    border-radius:12px;
    background:#fee2e2;
    color:#dc2626;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    margin:auto;
}

.remove-btn:hover{
    background:#fecaca;
    color:#b91c1c;
}

.price-chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#eff6ff;
    color:#1d4ed8;
    padding:8px 12px;
    border-radius:999px;
    font-weight:700;
    font-size:.95rem;
}

.shortcuts-box{
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:16px;
    padding:13px 15px;
    color:#475569;
    margin-top:16px;
}

.pago-rapido{
    border-radius:999px;
    font-weight:700;
    padding:6px 12px;
}

.product-helper{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:12px;
}

@media(max-width:991px){
    .payment-grid{
        grid-template-columns:1fr;
    }

    .info-boxes{
        grid-template-columns:1fr;
    }

    .total-amount{
        font-size:2.5rem;
    }
}
</style>
</head>

<body>

<div class="page-wrap">

    <div class="top-header">
        <div class="page-title">
            <h1><i class="bi bi-cart3"></i> Punto de Venta</h1>
            <p>Venta rápida con búsqueda por nombre, código y atajos de teclado.</p>
        </div>

        <div class="status-badge">
            <i class="bi bi-check-circle-fill"></i> Caja abierta
        </div>
    </div>

    <div class="brand-hero">
        <div class="brand-content">
            <div class="brand-left">
                <img src="<?= e($logoRuta); ?>" alt="Logo ExpiTrack" class="brand-logo">
                <div class="brand-text">
                    <h2>ExpiTrack POS</h2>
                    <p>Control inteligente, menos pérdidas, más ganancias.</p>
                </div>
            </div>

            <div class="brand-right">
                <div class="mini-stat">
                    <small>Usuario</small>
                    <strong><?= e($_SESSION['nombre']); ?></strong>
                </div>
                <div class="mini-stat">
                    <small>Productos en carrito</small>
                    <strong><?= e($totalItems); ?></strong>
                </div>
                <div class="mini-stat">
                    <small>Total actual</small>
                    <strong>$<?= number_format($totalCarrito,2); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php if($mensaje != ""){ ?>
        <div class="alert alert-<?= e($tipo_mensaje); ?> mb-4">
            <?= e($mensaje); ?>
        </div>
    <?php } ?>

    <div class="row g-4">

        <!-- AGREGAR PRODUCTO -->
        <div class="col-lg-5">
            <div class="modern-card h-100">
                <div class="card-header-custom">
                    <h3><i class="bi bi-search"></i> Agregar Producto</h3>
                    <p class="section-subtitle">Escribe el nombre o código del producto para vender más rápido.</p>
                </div>

                <div class="card-body-custom">
                    <form method="POST" id="formAgregar">

                        <input type="hidden" name="id_producto" id="id_producto">

                        <div class="mb-4">
                            <label class="form-label">Buscar producto</label>

                            <input
                            type="text"
                            id="producto_buscar"
                            class="form-control"
                            list="lista_productos"
                            placeholder="Escribe nombre o código..."
                            autocomplete="off"
                            autofocus>

                            <datalist id="lista_productos">
                                <?php foreach($productos as $producto){ ?>
                                    <option value="<?= e($producto['codigo'] . ' - ' . $producto['nombre']); ?>">
                                        $<?= number_format($producto['precio_venta'],2); ?> | Stock: <?= e($producto['stock']); ?>
                                    </option>
                                <?php } ?>
                            </datalist>

                            <div class="product-helper">
                                <span class="price-chip">
                                    <i class="bi bi-upc-scan"></i>
                                    Producto:
                                    <strong id="productoSeleccionado">Ninguno</strong>
                                </span>

                                <span class="price-chip">
                                    <i class="bi bi-cash"></i>
                                    Precio:
                                    <strong id="precioProducto">$0.00</strong>
                                </span>

                                <span class="price-chip">
                                    <i class="bi bi-box-seam"></i>
                                    Stock:
                                    <strong id="stockProducto">0</strong>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Cantidad</label>
                            <input
                            type="number"
                            name="cantidad"
                            id="cantidad"
                            class="form-control"
                            min="1"
                            value="1"
                            required>
                        </div>

                        <div class="info-boxes">
                            <div class="info-box">
                                <small>Estado</small>
                                <strong>Venta rápida</strong>
                            </div>
                            <div class="info-box">
                                <small>Inventario</small>
                                <strong>Automático</strong>
                            </div>
                            <div class="info-box">
                                <small>Ticket</small>
                                <strong>Imprimible</strong>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="agregar_carrito" class="btn btn-main w-100">
                                <i class="bi bi-plus-circle"></i> Agregar al carrito
                            </button>
                        </div>

                        <div class="shortcuts-box small">
                            <strong>Atajos rápidos:</strong><br>
                            F2 = Buscar |
                            F3 = Cantidad |
                            F4 = Método pago |
                            F6 = Pago |
                            F8 = Cobrar |
                            Esc = Limpiar
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- CARRITO -->
        <div class="col-lg-7">
            <div class="modern-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h3><i class="bi bi-basket"></i> Carrito de Venta</h3>
                        <p class="section-subtitle">Resumen de productos agregados antes de cobrar.</p>
                    </div>

                    <form method="POST" class="m-0">
                        <button type="submit" name="vaciar_carrito" class="btn-soft-danger">
                            <i class="bi bi-trash3"></i> Vaciar carrito
                        </button>
                    </form>
                </div>

                <div class="card-body-custom">

                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Quitar</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(empty($_SESSION['carrito'])){ ?>
                                <tr>
                                    <td colspan="5" class="empty-cart">
                                        No hay productos en el carrito
                                    </td>
                                </tr>
                            <?php }else{ ?>

                                <?php foreach($_SESSION['carrito'] as $item){ 
                                    $subtotal = $item['precio'] * $item['cantidad'];
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($item['nombre']); ?></strong><br>
                                            <small class="text-muted">Código: <?= e($item['codigo'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?= e($item['cantidad']); ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($item['precio'],2); ?>
                                        </td>
                                        <td class="text-end">
                                            <strong>$<?= number_format($subtotal,2); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <a href="index.php?quitar=<?= e($item['id_producto']); ?>"
                                               class="remove-btn"
                                               onclick="return confirm('¿Quitar producto del carrito?')">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="total-panel">
                        <div class="total-line">
                            <span>Subtotal</span>
                            <strong>$<?= number_format($totalCarrito,2); ?></strong>
                        </div>

                        <div class="total-line">
                            <span>Artículos</span>
                            <strong><?= e($totalItems); ?></strong>
                        </div>

                        <div class="total-line total">
                            <span>Total</span>
                            <div class="text-end">
                                <div class="total-amount" id="totalPantalla">
                                    $<?= number_format($totalCarrito,2); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" id="formCobro">

                        <div class="payment-grid">

                            <div>
                                <label class="form-label mt-3">Método de pago</label>

                                <select name="metodo_pago" id="metodo_pago" class="form-select" required>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TARJETA">Tarjeta</option>
                                    <option value="MIXTO">Mixto</option>
                                    <option value="FIADO">Fiado</option>
                                </select>
                            </div>

                            <div id="box_efectivo">
                                <label class="form-label mt-3">Pago recibido en efectivo</label>

                                <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="pago_recibido"
                                id="pago_recibido"
                                class="form-control"
                                value="<?= number_format($totalCarrito,2,'.',''); ?>">

                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm pago-rapido" data-monto="<?= e($totalCarrito); ?>">
                                        Exacto
                                    </button>

                                    <button type="button" class="btn btn-outline-primary btn-sm pago-rapido" data-extra="50">
                                        +50
                                    </button>

                                    <button type="button" class="btn btn-outline-primary btn-sm pago-rapido" data-extra="100">
                                        +100
                                    </button>

                                    <button type="button" class="btn btn-outline-primary btn-sm pago-rapido" data-extra="200">
                                        +200
                                    </button>

                                    <button type="button" class="btn btn-outline-primary btn-sm pago-rapido" data-extra="500">
                                        +500
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div id="box_tarjeta" class="mt-3 d-none">

                            <label class="form-label">Código de transacción</label>

                            <input
                            type="text"
                            name="codigo_transaccion"
                            id="codigo_transaccion"
                            class="form-control"
                            placeholder="Ejemplo: TXN-984512">

                        </div>

                        <div id="box_mixto" class="mt-3 d-none">

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Monto en efectivo</label>
                                    <input type="number" step="0.01" min="0" name="monto_efectivo" id="monto_efectivo" class="form-control" value="0">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Monto en tarjeta</label>
                                    <input type="number" step="0.01" min="0" name="monto_tarjeta" id="monto_tarjeta" class="form-control" value="0">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Monto fiado</label>
                                    <input type="number" step="0.01" min="0" name="monto_fiado" id="monto_fiado" class="form-control" value="0">
                                </div>

                            </div>

                            <div class="alert alert-info">
                                En pago mixto puedes dividir el total entre efectivo, tarjeta y fiado.
                            </div>

                        </div>

                        <div class="mt-3">
                            <span class="price-chip">
                                <i class="bi bi-cash-coin"></i>
                                Cambio estimado:
                                <strong id="cambioVista">$0.00</strong>
                            </span>
                        </div>

                        <button type="submit"
                                name="cobrar_venta"
                                id="btnCobrar"
                                class="btn btn-cobrar"
                                <?= empty($_SESSION['carrito']) ? 'disabled' : ''; ?>>
                            <i class="bi bi-cash-stack"></i> Cobrar Venta
                        </button>

                    </form>

                </div>
            </div>
        </div>

    </div>

</div>

<script>
const productos = <?= $productosJS; ?>;

const formAgregar = document.getElementById('formAgregar');
const productoBuscar = document.getElementById('producto_buscar');
const idProducto = document.getElementById('id_producto');
const cantidad = document.getElementById('cantidad');

const productoSeleccionado = document.getElementById('productoSeleccionado');
const precioProducto = document.getElementById('precioProducto');
const stockProducto = document.getElementById('stockProducto');

const metodoPago = document.getElementById('metodo_pago');

const boxEfectivo = document.getElementById('box_efectivo');
const boxTarjeta = document.getElementById('box_tarjeta');
const boxMixto = document.getElementById('box_mixto');

const pagoRecibido = document.getElementById('pago_recibido');
const montoEfectivo = document.getElementById('monto_efectivo');
const montoTarjeta = document.getElementById('monto_tarjeta');
const montoFiado = document.getElementById('monto_fiado');

const codigoTransaccion = document.getElementById('codigo_transaccion');
const cambioVista = document.getElementById('cambioVista');

const btnCobrar = document.getElementById('btnCobrar');

const total = <?= json_encode((float)$totalCarrito); ?>;

function normalizar(texto){
    return String(texto || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function buscarProducto(){

    let texto = normalizar(productoBuscar.value);

    let encontrado = productos.find(p => {

        let codigo = normalizar(p.codigo);
        let nombre = normalizar(p.nombre);
        let completo = normalizar((p.codigo || '') + ' - ' + (p.nombre || ''));

        return (
            completo === texto ||
            codigo === texto ||
            nombre === texto ||
            completo.includes(texto) ||
            nombre.includes(texto) ||
            codigo.includes(texto)
        );
    });

    if(encontrado){

        idProducto.value = encontrado.id_producto;
        productoSeleccionado.textContent = encontrado.nombre;
        precioProducto.textContent = "$" + parseFloat(encontrado.precio_venta).toFixed(2);
        stockProducto.textContent = encontrado.stock;
        cantidad.max = encontrado.stock;

    }else{

        idProducto.value = "";
        productoSeleccionado.textContent = "Ninguno";
        precioProducto.textContent = "$0.00";
        stockProducto.textContent = "0";
        cantidad.removeAttribute('max');
    }
}

productoBuscar.addEventListener('input', buscarProducto);
productoBuscar.addEventListener('change', buscarProducto);

formAgregar.addEventListener('submit', function(e){
    buscarProducto();

    if(idProducto.value === ""){
        e.preventDefault();
        alert("Selecciona un producto válido. Puedes escribir el código o nombre.");
        productoBuscar.focus();
        productoBuscar.select();
    }
});

function mostrarCampos(){

    let metodo = metodoPago.value;

    boxEfectivo.classList.add('d-none');
    boxTarjeta.classList.add('d-none');
    boxMixto.classList.add('d-none');

    codigoTransaccion.removeAttribute('required');

    if(metodo === 'EFECTIVO'){

        boxEfectivo.classList.remove('d-none');

    }else if(metodo === 'TARJETA'){

        boxTarjeta.classList.remove('d-none');
        codigoTransaccion.setAttribute('required', true);

    }else if(metodo === 'MIXTO'){

        boxMixto.classList.remove('d-none');
        boxTarjeta.classList.remove('d-none');

    }else if(metodo === 'FIADO'){

        // No muestra efectivo ni tarjeta
    }

    calcularCambio();
}

function calcularCambio(){

    let metodo = metodoPago.value;
    let cambio = 0;

    if(metodo === 'EFECTIVO'){

        let efectivo = parseFloat(pagoRecibido.value || 0);
        cambio = efectivo - total;

    }else if(metodo === 'TARJETA'){

        cambio = 0;

    }else if(metodo === 'FIADO'){

        cambio = 0;

    }else if(metodo === 'MIXTO'){

        let efectivo = parseFloat(montoEfectivo.value || 0);
        let tarjeta = parseFloat(montoTarjeta.value || 0);
        let fiado = parseFloat(montoFiado.value || 0);

        let suma = efectivo + tarjeta + fiado;

        cambio = suma - total;
    }

    if(cambio < 0){
        cambioVista.textContent = "Faltan $" + Math.abs(cambio).toFixed(2);
        cambioVista.style.color = "#ef4444";
    }else{
        cambioVista.textContent = "$" + cambio.toFixed(2);
        cambioVista.style.color = "#16a34a";
    }
}

document.querySelectorAll('.pago-rapido').forEach(btn => {

    btn.addEventListener('click', function(){

        if(this.dataset.monto){
            pagoRecibido.value = parseFloat(this.dataset.monto).toFixed(2);
        }

        if(this.dataset.extra){
            pagoRecibido.value = (total + parseFloat(this.dataset.extra)).toFixed(2);
        }

        calcularCambio();
    });
});

document.addEventListener('keydown', function(e){

    if(e.key === 'F2'){
        e.preventDefault();
        productoBuscar.focus();
        productoBuscar.select();
    }

    if(e.key === 'F3'){
        e.preventDefault();
        cantidad.focus();
        cantidad.select();
    }

    if(e.key === 'F4'){
        e.preventDefault();
        metodoPago.focus();
    }

    if(e.key === 'F6'){
        e.preventDefault();

        if(metodoPago.value === 'EFECTIVO'){
            pagoRecibido.focus();
            pagoRecibido.select();
        }else if(metodoPago.value === 'MIXTO'){
            montoEfectivo.focus();
            montoEfectivo.select();
        }else if(metodoPago.value === 'TARJETA'){
            codigoTransaccion.focus();
            codigoTransaccion.select();
        }
    }

    if(e.key === 'F8'){
        e.preventDefault();

        if(btnCobrar && !btnCobrar.disabled){
            btnCobrar.click();
        }
    }

    if(e.key === 'Escape'){
        e.preventDefault();

        productoBuscar.value = "";
        idProducto.value = "";
        productoSeleccionado.textContent = "Ninguno";
        precioProducto.textContent = "$0.00";
        stockProducto.textContent = "0";
        cantidad.value = 1;

        productoBuscar.focus();
    }
});

metodoPago.addEventListener('change', mostrarCampos);

pagoRecibido.addEventListener('input', calcularCambio);
montoEfectivo.addEventListener('input', calcularCambio);
montoTarjeta.addEventListener('input', calcularCambio);
montoFiado.addEventListener('input', calcularCambio);

mostrarCampos();
buscarProducto();
</script>

</body>
</html>