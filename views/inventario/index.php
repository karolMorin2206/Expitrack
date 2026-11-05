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

try {
    // Consulta para consolidar el inventario total por producto
    $stmt = $pdo->query("
        SELECT 
            p.id_producto,
            p.codigo,
            p.nombre AS producto_nombre,
            p.precio_compra,
            p.precio_venta,
            p.stock_minimo,
            c.nombre AS categoria,
            COALESCE(SUM(l.cantidad), 0) AS stock_total,
            COUNT(DISTINCT l.id_lote) AS total_lotes
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN lotes l ON p.id_producto = l.id_producto AND l.estado = 1 AND l.fecha_caducidad >= CURDATE()
        WHERE p.estado = 1
        GROUP BY p.id_producto
        ORDER BY p.nombre ASC
    ");
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Calcular métricas generales
    $total_unidades = 0;
    $valor_inventario_compra = 0;
    $valor_inventario_venta = 0;
    $productos_stock_bajo = 0;
    $productos_agotados = 0;

    foreach ($inventario as $item) {
        $stk = intval($item['stock_total']);
        $total_unidades += $stk;
        $valor_inventario_compra += ($stk * floatval($item['precio_compra']));
        $valor_inventario_venta += ($stk * floatval($item['precio_venta']));

        if ($stk == 0) {
            $productos_agotados++;
        } elseif ($stk <= intval($item['stock_minimo'])) {
            $productos_stock_bajo++;
        }
    }

} catch (PDOException $e) {
    $inventario = [];
    $total_unidades = $valor_inventario_compra = $valor_inventario_venta = $productos_stock_bajo = $productos_agotados = 0;
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Inventario General</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans" x-data="inventarioManager()">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
            
            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                        <span>Inventario General</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Consolidado general de existencias, valor del stock y estado por producto.</p>
                </div>
                
                <a href="../lotes/crear.php" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2 self-start sm:self-auto">
                    <i class="fa-solid fa-plus"></i> Entrada de Stock (Lote)
                </a>
            </div>

            <!-- Resumen de Métricas de Inventario -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Unidades</span>
                        <span class="text-2xl font-extrabold text-white mt-1 block"><?php echo number_format($total_unidades); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Valor de Venta</span>
                        <span class="text-2xl font-extrabold text-emerald-400 font-mono mt-1 block">$<?php echo number_format($valor_inventario_venta, 2); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl border border-teal-500/20">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Stock Bajo</span>
                        <span class="text-2xl font-extrabold text-amber-300 mt-1 block"><?php echo $productos_stock_bajo; ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider block">Agotados</span>
                        <span class="text-2xl font-extrabold text-rose-300 mt-1 block"><?php echo $productos_agotados; ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl border border-rose-500/20">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                </div>

            </div>

            <!-- Tabla de Inventario con Buscador -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Filtrar por producto, código o categoría..."
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 transition-all">
                    </div>

                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <button @click="filtroEstado = 'TODOS'" :class="filtroEstado === 'TODOS' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">Todos</button>
                        <button @click="filtroEstado = 'BAJO'" :class="filtroEstado === 'BAJO' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">Stock Bajo</button>
                        <button @click="filtroEstado = 'AGOTADO'" :class="filtroEstado === 'AGOTADO' ? 'bg-rose-500/20 border-rose-500 text-rose-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">Agotados</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">Código</th>
                                <th class="p-3.5">Producto</th>
                                <th class="p-3.5">Categoría</th>
                                <th class="p-3.5">P. Venta</th>
                                <th class="p-3.5">Stock Mín.</th>
                                <th class="p-3.5">Stock Actual</th>
                                <th class="p-3.5">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="item in itemsFiltrados" :key="item.id_producto">
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="p-3.5 font-mono text-xs text-slate-400" x-text="item.codigo"></td>
                                    <td class="p-3.5 font-semibold text-white">
                                        <span x-text="item.producto_nombre"></span>
                                        <span class="block text-[10px] text-slate-500 font-normal" x-text="item.total_lotes + ' lote(s) activo(s)'"></span>
                                    </td>
                                    <td class="p-3.5 text-slate-400 text-xs" x-text="item.categoria"></td>
                                    <td class="p-3.5 font-mono font-bold text-emerald-400" x-text="'$' + parseFloat(item.precio_venta).toFixed(2)"></td>
                                    <td class="p-3.5 font-mono text-slate-400" x-text="item.stock_minimo"></td>
                                    <td class="p-3.5 font-mono font-bold text-base" :class="item.stock_total == 0 ? 'text-rose-400' : (item.stock_total <= item.stock_minimo ? 'text-amber-400' : 'text-white')" x-text="item.stock_total + ' u.'"></td>
                                    <td class="p-3.5">
                                        <template x-if="item.stock_total == 0">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Agotado</span>
                                        </template>
                                        <template x-if="item.stock_total > 0 && item.stock_total <= item.stock_minimo">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Stock Bajo</span>
                                        </template>
                                        <template x-if="item.stock_total > item.stock_minimo">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Óptimo</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>

    <script>
        function inventarioManager() {
            return {
                search: '',
                filtroEstado: 'TODOS',
                items: <?php echo json_encode($inventario); ?>,

                get itemsFiltrados() {
                    return this.items.filter(i => {
                        const q = this.search.toLowerCase();
                        const matchText = i.producto_nombre.toLowerCase().includes(q) || 
                                          i.codigo.toLowerCase().includes(q) || 
                                          i.categoria.toLowerCase().includes(q);

                        const stk = parseInt(i.stock_total);
                        const min = parseInt(i.stock_minimo);

                        let matchEstado = true;
                        if (this.filtroEstado === 'BAJO') {
                            matchEstado = (stk > 0 && stk <= min);
                        } else if (this.filtroEstado === 'AGOTADO') {
                            matchEstado = (stk === 0);
                        }

                        return matchText && matchEstado;
                    });
                }
            }
        }
    </script>
</body>
</html>