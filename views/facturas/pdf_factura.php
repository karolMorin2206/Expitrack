<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

$id_factura = isset($_GET['id_factura']) ? intval($_GET['id_factura']) : 0;

try {
    $stmt = $pdo->prepare("
        SELECT f.*, v.fecha AS fecha_venta, v.total, v.metodo_pago, u.nombre AS cajero
        FROM facturas f
        INNER JOIN ventas v ON f.id_venta = v.id_venta
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE f.id_factura = :id_factura
        LIMIT 1
    ");
    $stmt->execute(['id_factura' => $id_factura]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        die("Factura no encontrada.");
    }

    // Obtener detalles de productos
    $stmtDetalle = $pdo->prepare("
        SELECT dv.*, p.nombre AS producto_nombre, p.codigo
        FROM detalle_ventas dv
        INNER JOIN productos p ON dv.id_producto = p.id_producto
        WHERE dv.id_venta = :id_venta
    ");
    $stmtDetalle->execute(['id_venta' => $factura['id_venta']]);
    $items = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar la factura.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura FACT-<?php echo str_pad($factura['id_factura'], 5, '0', STR_PAD_LEFT); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-8 flex flex-col items-center justify-center font-sans">

    <div class="no-print mb-6">
        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-lg transition-all flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Imprimir / Guardar en PDF
        </button>
    </div>

    <div class="w-full max-w-3xl bg-white text-slate-900 rounded-3xl p-8 shadow-2xl space-y-6">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">ExpiTrack POS</h1>
                <p class="text-xs text-slate-500">Sistema de Control de Lotes y Facturación Digital</p>
                <p class="text-xs text-slate-500">RFC: EXP230101XYZ</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase text-emerald-600 block">Factura Electrónica</span>
                <span class="text-xl font-mono font-extrabold text-slate-900">FACT-<?php echo str_pad($factura['id_factura'], 5, '0', STR_PAD_LEFT); ?></span>
                <span class="text-xs text-slate-500 block">Fecha: <?php echo date('d/m/Y H:i', strtotime($factura['fecha_emision'])); ?></span>
            </div>
        </div>

        <!-- Datos del Receptor -->
        <div class="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
            <div>
                <span class="font-bold text-slate-400 block uppercase">Receptor / Cliente</span>
                <span class="font-extrabold text-slate-800 text-sm block"><?php echo htmlspecialchars($factura['razon_social']); ?></span>
                <span class="font-mono text-slate-600">RFC: <?php echo htmlspecialchars($factura['rfc']); ?></span>
            </div>
            <div>
                <span class="font-bold text-slate-400 block uppercase">Detalles CFDI</span>
                <span class="block text-slate-700">Uso CFDI: <?php echo htmlspecialchars($factura['uso_cfdi']); ?></span>
                <span class="block text-slate-700">Ticket Ref: #<?php echo str_pad($factura['id_venta'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
        </div>

        <!-- Tabla de Conceptos -->
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold">
                    <th class="py-2">Cant.</th>
                    <th class="py-2">Producto</th>
                    <th class="py-2 text-right">P. Unitario</th>
                    <th class="py-2 text-right">Importe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($items as $it): ?>
                <tr>
                    <td class="py-3 font-mono font-bold text-slate-700"><?php echo $it['cantidad']; ?> u.</td>
                    <td class="py-3 font-semibold text-slate-900"><?php echo htmlspecialchars($it['producto_nombre']); ?></td>
                    <td class="py-3 text-right font-mono text-slate-600">$<?php echo number_format($it['precio'], 2); ?></td>
                    <td class="py-3 text-right font-mono font-bold text-slate-900">$<?php echo number_format($it['cantidad'] * $it['precio'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="border-t border-slate-200 pt-4 flex justify-between items-center">
            <span class="text-xs text-slate-500">Método de Pago: <strong><?php echo htmlspecialchars($factura['metodo_pago']); ?></strong></span>
            <div class="text-right">
                <span class="text-xs text-slate-400 uppercase font-bold block">Total a Pagar</span>
                <span class="text-2xl font-mono font-black text-emerald-600">$<?php echo number_format($factura['total'], 2); ?></span>
            </div>
        </div>

    </div>

</body>
</html>