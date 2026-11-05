<?php
session_start();

$conexion_path = __DIR__ . '/../../config/conexion.php';
if (!file_exists($conexion_path)) {
    die("Error: No se encontró el archivo de conexión.");
}
require_once $conexion_path;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Rango de fechas por defecto (Mes actual)
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

try {
    // 1. Resumen de Ingresos por Método de Pago en el periodo
    $stmtTotales = $pdo->prepare("
        SELECT 
            COALESCE(SUM(total), 0) AS total_general,
            COALESCE(SUM(CASE WHEN metodo_pago = 'EFECTIVO' THEN total ELSE 0 END), 0) AS total_efectivo,
            COALESCE(SUM(CASE WHEN metodo_pago = 'TARJETA' THEN total ELSE 0 END), 0) AS total_tarjeta,
            COALESCE(SUM(CASE WHEN metodo_pago = 'FIADO' THEN total ELSE 0 END), 0) AS total_fiado,
            COUNT(*) AS total_transacciones
        FROM ventas
        WHERE DATE(fecha) BETWEEN :inicio AND :fin
    ");
    $stmtTotales->execute(['inicio' => $fecha_inicio, 'fin' => $fecha_fin]);
    $resumen = $stmtTotales->fetch(PDO::FETCH_ASSOC);

    // Total en caja efectivo + tarjeta
    $caja_real = floatval($resumen['total_efectivo']) + floatval($resumen['total_tarjeta']);

    // 2. Top Productos Más Vendidos
    $stmtTop = $pdo->prepare("
        SELECT 
            p.nombre AS producto,
            c.nombre AS categoria,
            SUM(dv.cantidad) AS unidades_vendidas,
            SUM(dv.cantidad * dv.precio) AS ingresos_generados
        FROM detalle_ventas dv
        INNER JOIN ventas v ON dv.id_venta = v.id_venta
        INNER JOIN productos p ON dv.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE DATE(v.fecha) BETWEEN :inicio AND :fin
        GROUP BY dv.id_producto
        ORDER BY unidades_vendidas DESC
        LIMIT 10
    ");
    $stmtTop->execute(['inicio' => $fecha_inicio, 'fin' => $fecha_fin]);
    $top_productos = $stmtTop->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    $resumen = ['total_general' => 0, 'total_efectivo' => 0, 'total_tarjeta' => 0, 'total_fiado' => 0, 'total_transacciones' => 0];
    $caja_real = 0;
    $top_productos = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Reporte de Ventas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
            
            <!-- Encabezado y Filtro de Fechas -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-400"></i>
                        <span>Reportes Financieros y de Ventas</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Análisis de ingresos, métodos de cobro y productos de mayor rotación.</p>
                </div>

                <!-- Formulario de Rango de Fechas -->
                <form action="index.php" method="GET" class="flex flex-wrap items-center gap-3 bg-slate-900 border border-slate-800 p-2.5 rounded-2xl shadow-lg">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-semibold pl-2">Desde:</span>
                        <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" 
                            class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-semibold">Hasta:</span>
                        <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>" 
                            class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition-all">
                        Filtrar
                    </button>
                    
                    <button type="button" onclick="window.print()" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all" title="Imprimir Reporte">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </form>
            </div>

            <!-- Métrica Principal: Total Ingresos Realmente Cobrados -->
            <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-slate-900 border border-emerald-500/20 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block mb-1">Total Ingresos Netos en Caja</span>
                    <span class="text-4xl font-black text-white font-mono">$<?php echo number_format($caja_real, 2); ?></span>
                    <p class="text-xs text-slate-400 mt-1">Suma acumulada de pagos en Efectivo + Tarjeta (periodo seleccionado).</p>
                </div>

                <div class="flex items-center gap-4 border-t sm:border-t-0 sm:border-l border-slate-800 pt-4 sm:pt-0 sm:pl-6">
                    <div>
                        <span class="text-[11px] text-slate-400 font-semibold uppercase block">Transacciones</span>
                        <span class="text-2xl font-extrabold text-white font-mono"><?php echo number_format($resumen['total_transacciones']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Desglose de Métodos de Pago -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider block">Cobrado en Efectivo</span>
                        <span class="text-2xl font-extrabold text-emerald-300 font-mono mt-1 block">$<?php echo number_format($resumen['total_efectivo'], 2); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Cobrado con Tarjeta</span>
                        <span class="text-2xl font-extrabold text-teal-300 font-mono mt-1 block">$<?php echo number_format($resumen['total_tarjeta'], 2); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl border border-teal-500/20">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Ventas a Fiado (Por Cobrar)</span>
                        <span class="text-2xl font-extrabold text-amber-300 font-mono mt-1 block">$<?php echo number_format($resumen['total_fiado'], 2); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                </div>

            </div>

            <!-- Tabla de Productos Más Vendidos -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-ranking-star text-amber-400"></i>
                    <span>Top 10 Productos Más Vendidos</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">#</th>
                                <th class="p-3.5">Producto</th>
                                <th class="p-3.5">Categoría</th>
                                <th class="p-3.5 text-center">Unidades Vendidas</th>
                                <th class="p-3.5 text-right">Ingresos Generados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php if (count($top_productos) === 0): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    No se registraron ventas en el rango de fechas seleccionado.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php $pos = 1; foreach ($top_productos as $tp): ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-mono text-xs font-bold text-amber-400">#<?php echo $pos++; ?></td>
                                <td class="p-3.5 font-semibold text-white"><?php echo htmlspecialchars($tp['producto']); ?></td>
                                <td class="p-3.5 text-slate-400 text-xs"><?php echo htmlspecialchars($tp['categoria']); ?></td>
                                <td class="p-3.5 text-center font-mono font-bold text-emerald-400"><?php echo number_format($tp['unidades_vendidas']); ?> u.</td>
                                <td class="p-3.5 text-right font-mono font-extrabold text-white">$<?php echo number_format($tp['ingresos_generados'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>