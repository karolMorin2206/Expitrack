<header class="h-20 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <!-- Indicador de Sucursal -->
        <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-medium text-slate-300">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Sucursal: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['usuario_sucursal'] ?? 'Matriz'); ?></strong></span>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <!-- Alerta Rápida -->
        <a href="../alertas/index.php" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-slate-300 hover:text-amber-400 hover:border-amber-400/40 transition-all relative">
            <i class="fa-regular fa-bell"></i>
            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-amber-400"></span>
        </a>

        <!-- Perfil Usuario Header -->
        <div class="pl-3 border-l border-slate-800 flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <span class="block text-xs font-semibold text-slate-200"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?></span>
                <span class="block text-[10px] text-slate-400"><?php echo htmlspecialchars($_SESSION['usuario_correo'] ?? 'admin@expitrack.com'); ?></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-md shadow-emerald-500/20">
                <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center text-emerald-400 font-bold text-sm">
                    <?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>
</header>