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

// Obtener historial de facturas emitidas
try {
    $stmtFacturas = $pdo->query("
        SELECT f.*, v.fecha AS fecha_venta, v.total
        FROM facturas f
        INNER JOIN ventas v ON f.id_venta = v.id_venta
        ORDER BY f.fecha_emision DESC
    ");
    $facturas = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $facturas = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Módulo de Facturación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans" x-data="facturacionManager()">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-teal-400"></i>
                        <span>Generación y Emisión de Facturas</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Genera comprobantes fiscales y facturas digitales a partir de los tickets de venta.</p>
                </div>

                <button @click="modalNuevaFactura = true" class="px-4 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-600 text-white font-bold text-xs shadow-lg shadow-teal-500/25 transition-all flex items-center gap-2 self-start sm:self-auto">
                    <i class="fa-solid fa-plus"></i> Nueva Factura
                </button>
            </div>

            <!-- Contadores Rápidos -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Facturas Emitidas</span>
                        <span class="text-3xl font-extrabold text-white mt-1 block"><?php echo count($facturas); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl border border-teal-500/20">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider block">Monto Facturado</span>
                        <span class="text-3xl font-extrabold text-emerald-400 font-mono mt-1 block">
                            $<?php 
                                $totalFact = array_reduce($facturas, function($acc, $f){ return $acc + floatval($f['total']); }, 0);
                                echo number_format($totalFact, 2);
                            ?>
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20">
                        <i class="fa-solid fa-dollar-sign"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Estatus Timbrado</span>
                        <span class="text-2xl font-extrabold text-amber-300 mt-1 block">Vigente</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>

            <!-- Tabla de Historial de Facturas -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-teal-400"></i>
                    <span>Historial de Comprobantes Emitidos</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">Folio / Serie</th>
                                <th class="p-3.5">N° Ticket</th>
                                <th class="p-3.5">RFC / Razón Social</th>
                                <th class="p-3.5">Fecha Emisión</th>
                                <th class="p-3.5">Monto Total</th>
                                <th class="p-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php if (count($facturas) === 0): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    No hay facturas generadas todavía. Haz clic en "Nueva Factura" para emitir la primera.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($facturas as $f): ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 font-mono text-teal-400 font-bold">FACT-<?php echo str_pad($f['id_factura'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="p-3.5 font-mono text-slate-300">#<?php echo str_pad($f['id_venta'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td class="p-3.5">
                                    <span class="font-semibold text-white block"><?php echo htmlspecialchars($f['razon_social']); ?></span>
                                    <span class="text-xs font-mono text-slate-400"><?php echo htmlspecialchars($f['rfc']); ?></span>
                                </td>
                                <td class="p-3.5 text-xs text-slate-300 font-mono"><?php echo date('d/m/Y H:i', strtotime($f['fecha_emision'])); ?></td>
                                <td class="p-3.5 font-mono font-extrabold text-emerald-400">$<?php echo number_format($f['total'], 2); ?></td>
                                <td class="p-3.5 text-right">
                                    <a href="pdf_factura.php?id_factura=<?php echo $f['id_factura']; ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-teal-500/10 hover:bg-teal-500/20 text-teal-400 border border-teal-500/20 text-xs font-semibold transition-all inline-flex items-center gap-1">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
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

    <!-- MODAL NUEVA FACTURA -->
    <div x-show="modalNuevaFactura" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4" x-cloak>
        <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-teal-400 text-lg"></i>
                    <h3 class="font-bold text-lg text-white">Generar Factura Electrónica</h3>
                </div>
                <button @click="modalNuevaFactura = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Formulario de Emisión -->
            <form @submit.prevent="generarFactura()" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">N° de Ticket o Venta *</label>
                    <input type="number" x-model="idVenta" required placeholder="Ej. 1" 
                        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-teal-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">RFC / Identificación Fiscal *</label>
                        <input type="text" x-model="rfc" required placeholder="XAXX010101000" 
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-teal-500 uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Razón Social / Nombre *</label>
                        <input type="text" x-model="razonSocial" required placeholder="Público en General / Empresa" 
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Correo Electrónico *</label>
                    <input type="email" x-model="correo" required placeholder="cliente@correo.com" 
                        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-teal-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Uso de CFDI</label>
                        <select x-model="usoCfdi" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 text-xs focus:outline-none focus:border-teal-500">
                            <option value="G03">G03 - Gastos en general</option>
                            <option value="S01">S01 - Sin efectos fiscales</option>
                            <option value="CP01">CP01 - Pagos</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Régimen Fiscal</label>
                        <select x-model="regimenFiscal" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 text-xs focus:outline-none focus:border-teal-500">
                            <option value="616">616 - Sin obligaciones fiscales</option>
                            <option value="601">601 - General de Ley Personas Morales</option>
                            <option value="612">612 - Personas Físicas con Actividades</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="modalNuevaFactura = false" class="w-1/3 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="w-2/3 py-3 rounded-xl bg-teal-500 hover:bg-teal-600 text-white font-bold text-xs shadow-lg shadow-teal-500/25 transition-all">
                        Generar y Emitir Factura
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function facturacionManager() {
            return {
                modalNuevaFactura: false,
                idVenta: '',
                rfc: '',
                razonSocial: '',
                correo: '',
                usoCfdi: 'G03',
                regimenFiscal: '616',

                async generarFactura() {
                    const payload = {
                        id_venta: this.idVenta,
                        rfc: this.rfc,
                        razon_social: this.razonSocial,
                        correo: this.correo,
                        uso_cfdi: this.usoCfdi,
                        regimen_fiscal: this.regimenFiscal
                    };

                    try {
                        const response = await fetch('../../controllers/guardar_factura.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (data.success) {
                            alert('¡Factura emitida correctamente!');
                            this.modalNuevaFactura = false;
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (e) {
                        alert('Error de conexión al procesar la factura.');
                    }
                }
            }
        }
    </script>
</body>
</html>