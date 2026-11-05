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
            v.*,
            u.nombre AS cajero,
            COUNT(dv.id_detalle) AS total_articulos
        FROM ventas v
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
        LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
        GROUP BY v.id_venta
        ORDER BY v.fecha DESC
    ");
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventas = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Historial de Ventas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-400"></i>
                    <span>Historial de Ventas</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1">Consulta el registro de transacciones completadas y reimprime los tickets.</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">N° Ticket</th>
                                <th class="p-3.5">Fecha y Hora</th>
                                <th class="p-3.5">Atendió</th>
                                <th class="p-3.5">Método Pago</th>
                                <th class="p-3.5">Total</th>
                                <th class="p-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php if (count($ventas) === 0): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">No hay ventas registradas aún.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($ventas as $v): ?>
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3.5 font-mono text-emerald-400 font-bold">#<?php echo str_pad($v['id_venta'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td class="p-3.5 text-slate-300 font-mono text-xs"><?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?></td>
                                <td class="p-3.5 text-white"><?php echo htmlspecialchars($v['cajero']); ?></td>
                                <td class="p-3.5"><span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-300"><?php echo htmlspecialchars($v['metodo_pago']); ?></span></td>
                                <td class="p-3.5 font-mono font-extrabold text-emerald-400">$<?php echo number_format($v['total'], 2); ?></td>
                                <td class="p-3.5 text-right">
                                    <a href="../punto_venta/ticket.php?id_venta=<?php echo $v['id_venta']; ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 text-xs font-semibold transition-all inline-flex items-center gap-1">
                                        <i class="fa-solid fa-print"></i> Ticket
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