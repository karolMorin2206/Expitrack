<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $stmt = $pdo->query("
        SELECT 
            p.*, 
            c.nombre AS categoria,
            COALESCE(SUM(l.cantidad), 0) AS stock_total
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN lotes l ON p.id_producto = l.id_producto AND l.estado = 1
        WHERE p.estado = 1
        GROUP BY p.id_producto
        ORDER BY p.nombre ASC
    ");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Catálogo de Productos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-box text-emerald-400"></i>
                        <span>Catálogo de Productos</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Administra los artículos registrados en el sistema.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="crear.php" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Nuevo Producto
                    </a>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">Código</th>
                                <th class="p-3.5">Producto</th>
                                <th class="p-3.5">Categoría</th>
                                <th class="p-3.5">Precio Venta</th>
                                <th class="p-3.5">Stock Total</th>
                                <th class="p-3.5 text-right">Lotes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php if (count($productos) === 0): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    No hay productos registrados aún.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($productos as $p): ?>
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3.5 font-mono text-slate-400 text-xs"><?php echo htmlspecialchars($p['codigo']); ?></td>
                                <td class="p-3.5 font-semibold text-white"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td class="p-3.5 text-slate-400"><?php echo htmlspecialchars($p['categoria']); ?></td>
                                <td class="p-3.5 font-mono font-bold text-emerald-400">$<?php echo number_format($p['precio_venta'], 2); ?></td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $p['stock_total'] > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'; ?>">
                                        <?php echo $p['stock_total']; ?> unidades
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="../lotes/crear.php" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">
                                        + Lote
                                    </a>
                                </td>
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