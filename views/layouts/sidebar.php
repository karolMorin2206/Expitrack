<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between hidden md:flex shrink-0 h-screen sticky top-0">
    <!-- Header Fijo (Logo) -->
    <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800/80 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-lg shadow-emerald-500/20 flex items-center justify-center">
            <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                <i class="fa-solid fa-boxes-packing text-emerald-400 text-lg"></i>
            </div>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-white">
                Expi<span class="text-emerald-400">Track</span>
            </h1>
            <p class="text-[10px] text-slate-500 font-medium tracking-wide uppercase">Gestión de Lotes</p>
        </div>
    </div>

    <!-- Navegación Principal con Scroll Independiente -->
    <nav class="p-4 space-y-1.5 flex-1 overflow-y-auto custom-scrollbar">
        <div class="px-3 py-2 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Menú Principal</div>
        
        <a href="../dashboard/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo ($pagina_actual == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-chart-pie w-5"></i>
            <span>Dashboard</span>
        </a>

        <a href="../punto_venta/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'punto_venta') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-cash-register w-5"></i>
            <span>Punto de Venta</span>
        </a>

        <a href="../inventario/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'inventario') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-boxes-stacked w-5"></i>
            <span>Inventario General</span>
        </a>

        <a href="../lotes/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'lotes') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-layer-group w-5"></i>
            <span>Control de Lotes</span>
        </a>

        <a href="../alertas/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all relative <?php echo (strpos($_SERVER['REQUEST_URI'], 'alertas') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-bell w-5 text-amber-400"></i>
            <span>Caducidades / Alertas</span>
        </a>

        <div class="pt-4 px-3 py-2 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Catálogo & Ventas</div>

        <a href="../productos/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'productos') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-box w-5"></i>
            <span>Productos</span>
        </a>

        <a href="../categorias/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'categorias') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-tags w-5"></i>
            <span>Categorías</span>
        </a>

        <a href="../ventas/historial.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'ventas') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-receipt w-5"></i>
            <span>Historial Ventas</span>
        </a>

        <a href="../reportes/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'reportes') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-file-invoice-dollar w-5"></i>
            <span>Reportes</span>
        </a>

        <a href="../facturas/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'facturas') !== false) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'; ?>">
            <i class="fa-solid fa-file-pdf w-5 text-teal-400"></i>
            <span>Facturas</span>
        </a>
    </nav>

    <!-- Footer Fijo (Info del Usuario & Salida) -->
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 shrink-0">
        <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-800/40 border border-slate-700/50 mb-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-sm border border-emerald-500/30">
                <?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-semibold text-slate-200 truncate"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></p>
                <p class="text-[10px] text-emerald-400 font-medium truncate"><?php echo htmlspecialchars($_SESSION['usuario_rol'] ?? 'Rol'); ?></p>
            </div>
        </div>
        <a href="../../controllers/logout.php" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-semibold transition-all">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>