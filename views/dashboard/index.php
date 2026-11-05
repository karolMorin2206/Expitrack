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

$sucursal_id = $_SESSION['id_sucursal'] ?? 1;

try {
    // 1. Total Productos Registrados en Inventario
    $stmtProductos = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 1");
    $total_productos = $stmtProductos->fetchColumn() ?: 0;

    // 2. Lotes Próximos a Caducar (en los próximos 30 días con stock > 0)
    $stmtProximos = $pdo->query("
        SELECT COUNT(*) 
        FROM lotes 
        WHERE estado = 1 
          AND cantidad > 0 
          AND fecha_caducidad >= CURDATE() 
          AND fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    $proximos_caducar = $stmtProximos->fetchColumn() ?: 0;

    // 3. Lotes Vencidos (con stock > 0)
    $stmtVencidos = $pdo->query("
        SELECT COUNT(*) 
        FROM lotes 
        WHERE estado = 1 
          AND cantidad > 0 
          AND fecha_caducidad < CURDATE()
    ");
    $lotes_vencidos = $stmtVencidos->fetchColumn() ?: 0;

    // 4. Total de Ventas de Hoy
    $stmtVentasHoy = $pdo->query("
        SELECT COALESCE(SUM(total), 0) 
        FROM ventas 
        WHERE DATE(fecha) = CURDATE()
    ");
    $ventas_hoy = $stmtVentasHoy->fetchColumn() ?: 0;

} catch (PDOException $e) {
    $total_productos = $proximos_caducar = $lotes_vencidos = $ventas_hoy = 0;
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Banner de Bienvenida -->
            <div class="p-6 lg:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl relative overflow-hidden">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-3">
                        <i class="fa-solid fa-chart-line text-[10px]"></i> Panel de Control
                    </span>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">
                        Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?> 👋
                    </h1>
                    <p class="text-sm text-slate-400 mt-2 leading-relaxed">
                        Bienvenido a <strong class="text-white">ExpiTrack</strong>. Aquí tienes el resumen ejecutivo del estado de inventario, ventas y alertas de caducidades en tiempo real.
                    </p>
                </div>
            </div>

            <!-- TARJETAS INTERACTIVAS DE RESUMEN (ENLACES) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- 1. PRODUCTOS REGISTRADOS -> Inventario General -->
                <a href="../inventario/index.php" 
                   class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/80 shadow-xl transition-all duration-200 group flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 group-hover:text-emerald-400 uppercase tracking-wider block transition-colors">Productos Registrados</span>
                        <span class="text-3xl font-extrabold text-white mt-1 block"><?php echo number_format($total_productos); ?></span>
                        <span class="text-[11px] text-slate-500 mt-1 block font-medium">Catálogo activo</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </a>

                <!-- 2. PRÓXIMOS A CADUCAR -> Control de Lotes -->
                <a href="../lotes/index.php" 
                   class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/50 hover:bg-slate-800/80 shadow-xl transition-all duration-200 group flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Próximos a Caducar</span>
                        <span class="text-3xl font-extrabold text-amber-300 mt-1 block"><?php echo number_format($proximos_caducar); ?></span>
                        <span class="text-[11px] text-slate-500 mt-1 block font-medium">En los próximos 30 días</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </a>

                <!-- 3. LOTES VENCIDOS -> Caducidades / Alertas -->
                <a href="../alertas/index.php" 
                   class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/50 hover:bg-slate-800/80 shadow-xl transition-all duration-200 group flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider block">Lotes Vencidos</span>
                        <span class="text-3xl font-extrabold text-rose-300 mt-1 block"><?php echo number_format($lotes_vencidos); ?></span>
                        <span class="text-[11px] text-slate-500 mt-1 block font-medium">Requieren retiro de inventario</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl border border-rose-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-skull-crossbones"></i>
                    </div>
                </a>

                <!-- 4. VENTAS DE HOY -> Historial de Ventas -->
                <a href="../ventas/historial.php" 
                   class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-teal-500/50 hover:bg-slate-800/80 shadow-xl transition-all duration-200 group flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Ventas de Hoy</span>
                        <span class="text-3xl font-extrabold text-teal-300 font-mono mt-1 block">$<?php echo number_format($ventas_hoy, 2); ?></span>
                        <span class="text-[11px] text-slate-500 mt-1 block font-medium">Ingresos acumulados</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl border border-teal-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </a>

            </div>

            <!-- Acciones Rápidas -->
            <div class="space-y-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-amber-400"></i> Acciones Rápidas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    <a href="../punto_venta/index.php" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/80 transition-all duration-200 group shadow-lg">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg mb-4 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <h3 class="font-bold text-white text-base group-hover:text-emerald-300 transition-colors">Abrir Punto de Venta</h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Registra cobros rápidos, aplica descuentos y genera tickets de compra.</p>
                    </a>

                    <a href="../lotes/crear.php" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/80 transition-all duration-200 group shadow-lg">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg mb-4 border border-amber-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <h3 class="font-bold text-white text-base group-hover:text-amber-300 transition-colors">Registrar Nuevo Lote</h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Ingresa productos con su número de lote y fecha de vencimiento.</p>
                    </a>

                    <a href="../alertas/index.php" class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/80 transition-all duration-200 group shadow-lg">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-lg mb-4 border border-rose-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <h3 class="font-bold text-white text-base group-hover:text-rose-300 transition-colors">Revisar Alertas</h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Consulta los productos próximos a caducar para aplicar ofertas.</p>
                    </a>

                </div>
            </div>

        </main>
    </div>

</body>
</html>