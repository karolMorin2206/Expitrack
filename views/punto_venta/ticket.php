<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_venta = isset($_GET['id_venta']) ? intval($_GET['id_venta']) : 0;

if ($id_venta <= 0) {
    header("Location: index.php");
    exit();
}

try {
    // 1. Obtener la venta con datos de usuario y sucursal
    $stmtVenta = $pdo->prepare("
        SELECT 
            v.*,
            u.nombre AS cajero,
            s.nombre AS sucursal,
            s.direccion AS sucursal_direccion,
            s.telefono AS sucursal_telefono
        FROM ventas v
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
        INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
        WHERE v.id_venta = :id_venta
        LIMIT 1
    ");
    $stmtVenta->execute(['id_venta' => $id_venta]);
    $venta = $stmtVenta->fetch();

    if (!$venta) {
        die("Venta no encontrada.");
    }

    // 2. Obtener los detalles de la venta
    $stmtDetalle = $pdo->prepare("
        SELECT 
            dv.*,
            p.nombre AS producto_nombre,
            p.codigo
        FROM detalle_ventas dv
        INNER JOIN productos p ON dv.id_producto = p.id_producto
        WHERE dv.id_venta = :id_venta
    ");
    $stmtDetalle->execute(['id_venta' => $id_venta]);
    $detalles = $stmtDetalle->fetchAll();

} catch (PDOException $e) {
    die("Error al recuperar el ticket: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Ticket #<?php echo str_pad($venta['id_venta'], 6, '0', STR_PAD_LEFT); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .font-mono-ticket {
            font-family: 'Space Mono', monospace;
        }
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .ticket-card {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 80mm !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col items-center justify-center p-4 font-sans">

    <!-- Botones de Acción (Ocultos al imprimir) -->
    <div class="no-print flex items-center gap-3 mb-6">
        <a href="index.php" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all flex items-center gap-2 shadow-lg">
            <i class="fa-solid fa-arrow-left"></i> Nueva Venta
        </a>
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-500/25 flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Imprimir Ticket / PDF
        </button>
    </div>

    <!-- Ticket Térmico -->
    <div class="ticket-card w-full max-w-[380px] bg-white text-slate-900 p-6 rounded-3xl shadow-2xl font-mono-ticket text-xs leading-relaxed">
        
        <!-- Encabezado -->
        <div class="text-center space-y-1 pb-4 border-b border-dashed border-slate-300">
            <h1 class="text-xl font-bold tracking-tight text-slate-950">ExpiTrack</h1>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600"><?php echo htmlspecialchars($venta['sucursal']); ?></p>
            <p class="text-[10px] text-slate-500"><?php echo htmlspecialchars($venta['sucursal_direccion']); ?></p>
            <p class="text-[10px] text-slate-500">Tel: <?php echo htmlspecialchars($venta['sucursal_telefono']); ?></p>
        </div>

        <!-- Meta Datos de Venta -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span class="text-slate-500">Ticket #:</span>
                <span class="font-bold">#<?php echo str_pad($venta['id_venta'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Fecha:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Atendió:</span>
                <span class="truncate max-w-[150px]"><?php echo htmlspecialchars($venta['cajero']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Método Pago:</span>
                <span class="font-bold"><?php echo htmlspecialchars($venta['metodo_pago']); ?></span>
            </div>
        </div>

        <!-- Tabla de Artículos -->
        <div class="py-3 border-b border-dashed border-slate-300">
            <div class="flex font-bold border-b border-slate-200 pb-1 mb-2 text-[10px] uppercase text-slate-500">
                <span class="flex-1">Cant x Producto</span>
                <span class="w-16 text-right">Total</span>
            </div>

            <div class="space-y-2">
                <?php foreach ($detalles as $d): ?>
                <div>
                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($d['producto_nombre']); ?></div>
                    <div class="flex justify-between text-[11px] text-slate-600">
                        <span><?php echo $d['cantidad']; ?> x $<?php echo number_format($d['precio'], 2); ?></span>
                        <span class="font-bold text-slate-900">$<?php echo number_format($d['cantidad'] * $d['precio'], 2); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Totales y Pago -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1.5 text-[11px]">
            <div class="flex justify-between text-sm font-bold text-slate-950 pt-1">
                <span>TOTAL:</span>
                <span class="text-base">$<?php echo number_format($venta['total'], 2); ?></span>
            </div>
            
            <?php if ($venta['metodo_pago'] === 'EFECTIVO'): ?>
            <div class="flex justify-between text-slate-600 pt-1">
                <span>Efectivo Recibido:</span>
                <span>$<?php echo number_format($venta['pago_recibido'], 2); ?></span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Cambio:</span>
                <span class="font-bold">$<?php echo number_format($venta['cambio'], 2); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pie de Página del Ticket -->
        <div class="pt-4 text-center space-y-1">
            <p class="font-bold text-slate-900 text-[11px]">¡Gracias por su compra!</p>
            <p class="text-[10px] text-slate-500">Conserve este ticket para devoluciones o aclaraciones.</p>
            <div class="pt-2">
                <i class="fa-solid fa-barcode text-3xl text-slate-800"></i>
            </div>
        </div>

    </div>

</body>
</html>