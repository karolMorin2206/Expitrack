<?php
session_start();

$conexion_path = __DIR__ . '/../../config/conexion.php';
if (!file_exists($conexion_path)) {
    die("Error: No se encontró el archivo de conexión en la ruta esperada.");
}
require_once $conexion_path;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    // 1. Lotes Vencidos
    $stmtVencidos = $pdo->prepare("
        SELECT l.*, p.nombre AS producto_nombre, p.codigo, p.precio_venta, c.nombre AS categoria
        FROM lotes l
        INNER JOIN productos p ON l.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE l.estado = 1 AND l.cantidad > 0 AND l.fecha_caducidad < CURDATE()
        ORDER BY l.fecha_caducidad ASC
    ");
    $stmtVencidos->execute();
    $lotes_vencidos = $stmtVencidos->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 2. Lotes Críticos (Vencen en <= 7 días)
    $stmtCriticos = $pdo->prepare("
        SELECT l.*, p.nombre AS producto_nombre, p.codigo, p.precio_venta, c.nombre AS categoria
        FROM lotes l
        INNER JOIN productos p ON l.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE l.estado = 1 AND l.cantidad > 0 
        AND l.fecha_caducidad >= CURDATE()
        AND l.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY l.fecha_caducidad ASC
    ");
    $stmtCriticos->execute();
    $lotes_criticos = $stmtCriticos->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 3. Lotes Próximos (Vencen en 8 a 30 días)
    $stmtProximos = $pdo->prepare("
        SELECT l.*, p.nombre AS producto_nombre, p.codigo, p.precio_venta, c.nombre AS categoria
        FROM lotes l
        INNER JOIN productos p ON l.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE l.estado = 1 AND l.cantidad > 0 
        AND l.fecha_caducidad > DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND l.fecha_caducidad <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY l.fecha_caducidad ASC
    ");
    $stmtProximos->execute();
    $lotes_proximos = $stmtProximos->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    $lotes_vencidos = [];
    $lotes_criticos = [];
    $lotes_proximos = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Alertas de Caducidad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Alpine.js para controlar el estado del modal -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans" x-data="alertasManager()">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Encabezado -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-bell text-amber-400"></i>
                        <span>Alertas y Control de Caducidades</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Supervisa lotes vencidos o próximos a expirar para aplicar mermas o promociones.</p>
                </div>
            </div>

            <!-- Resumen de Contadores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider block">Ya Vencidos</span>
                        <span class="text-3xl font-extrabold text-rose-200 mt-1 block"><?php echo count($lotes_vencidos); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-skull-crossbones"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Urgentes (≤ 7 días)</span>
                        <span class="text-3xl font-extrabold text-amber-200 mt-1 block"><?php echo count($lotes_criticos); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-teal-400 uppercase tracking-wider block">Atención (8 - 30 días)</span>
                        <span class="text-3xl font-extrabold text-teal-200 mt-1 block"><?php echo count($lotes_proximos); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 1: VENCIDOS -->
            <?php if (count($lotes_vencidos) > 0): ?>
            <div class="bg-slate-900 border border-rose-500/30 rounded-3xl p-6 space-y-4 shadow-xl">
                <h2 class="text-lg font-bold text-rose-400 flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark"></i> Lotes Vencidos (Retirar Inmediatamente)
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3">Producto</th>
                                <th class="p-3">N° Lote</th>
                                <th class="p-3">Categoría</th>
                                <th class="p-3">Stock</th>
                                <th class="p-3">Venció el</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php foreach ($lotes_vencidos as $l): ?>
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 font-semibold text-white"><?php echo htmlspecialchars($l['producto_nombre']); ?></td>
                                <td class="p-3 font-mono text-slate-300"><?php echo htmlspecialchars($l['numero_lote']); ?></td>
                                <td class="p-3 text-slate-400"><?php echo htmlspecialchars($l['categoria']); ?></td>
                                <td class="p-3 font-bold text-rose-400"><?php echo $l['cantidad']; ?> u.</td>
                                <td class="p-3 font-mono text-rose-300"><?php echo date('d/m/Y', strtotime($l['fecha_caducidad'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECCIÓN 2: URGENTES (<= 7 DÍAS) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-xl">
                <h2 class="text-lg font-bold text-amber-400 flex items-center gap-2">
                    <i class="fa-solid fa-bolt"></i> Vencimiento en 7 Días o Menos (Sugerido: Aplicar Descuento)
                </h2>
                <?php if (count($lotes_criticos) === 0): ?>
                    <p class="text-sm text-slate-500 py-4 text-center">No hay lotes con vencimiento crítico en este momento.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3">Producto</th>
                                <th class="p-3">N° Lote</th>
                                <th class="p-3">Stock</th>
                                <th class="p-3">Caduca</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php foreach ($lotes_criticos as $l): ?>
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 font-semibold text-white"><?php echo htmlspecialchars($l['producto_nombre']); ?></td>
                                <td class="p-3 font-mono text-slate-300"><?php echo htmlspecialchars($l['numero_lote']); ?></td>
                                <td class="p-3 font-bold text-amber-400"><?php echo $l['cantidad']; ?> u.</td>
                                <td class="p-3 font-mono text-amber-300"><?php echo date('d/m/Y', strtotime($l['fecha_caducidad'])); ?></td>
                                <td class="p-3 text-right">
                                    <button 
                                        @click="abrirModalDescuento(<?php echo htmlspecialchars(json_encode($l)); ?>)"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition-all">
                                        Crear Descuento
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- SECCIÓN 3: ATENCIÓN (8-30 DÍAS) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-xl">
                <h2 class="text-lg font-bold text-teal-400 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-week"></i> Próximos a Vencer (Próximos 30 días)
                </h2>
                <?php if (count($lotes_proximos) === 0): ?>
                    <p class="text-sm text-slate-500 py-4 text-center">No hay productos en esta ventana de tiempo.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3">Producto</th>
                                <th class="p-3">N° Lote</th>
                                <th class="p-3">Stock</th>
                                <th class="p-3">Fecha Caducidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php foreach ($lotes_proximos as $l): ?>
                            <tr class="hover:bg-slate-800/40">
                                <td class="p-3 font-semibold text-white"><?php echo htmlspecialchars($l['producto_nombre']); ?></td>
                                <td class="p-3 font-mono text-slate-300"><?php echo htmlspecialchars($l['numero_lote']); ?></td>
                                <td class="p-3 font-bold text-white"><?php echo $l['cantidad']; ?> u.</td>
                                <td class="p-3 font-mono text-teal-300"><?php echo date('d/m/Y', strtotime($l['fecha_caducidad'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <!-- MODAL FLOTANTE PARA CREAR DESCUENTO -->
    <div x-show="modalAbierto" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5 relative">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-white">Aplicar Descuento al Lote</h3>
                        <p class="text-xs text-slate-400">Promoción rápida por caducidad</p>
                    </div>
                </div>
                <button @click="modalAbierto = false" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Información del Lote Seleccionado -->
            <template x-if="loteSeleccionado">
                <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-2xl space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-slate-400 uppercase">Producto</span>
                        <span class="text-xs font-mono font-bold text-emerald-400" x-text="'$' + parseFloat(loteSeleccionado.precio_venta).toFixed(2) + ' (Precio Regular)'"></span>
                    </div>
                    <div class="text-sm font-bold text-white" x-text="loteSeleccionado.producto_nombre"></div>
                    <div class="flex justify-between text-xs text-slate-400 pt-1 border-t border-slate-800/60">
                        <span>Lote: <strong class="text-slate-200 font-mono" x-text="loteSeleccionado.numero_lote"></strong></span>
                        <span>Stock: <strong class="text-amber-400" x-text="loteSeleccionado.cantidad + ' u.'"></strong></span>
                    </div>
                </div>
            </template>

            <!-- Formulario Modal -->
            <form @submit.prevent="guardarDescuento()" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Tipo de Descuento</label>
                        <select x-model="descuentoTipo" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="monto">Monto Fijo ($)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Valor del Descuento</label>
                        <input type="number" step="0.01" x-model="descuentoValor" required placeholder="Ej. 20" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 font-mono text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <!-- Cálculo del Precio Final -->
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-between text-xs">
                    <span class="text-slate-300 font-medium">Nuevo Precio en Caja:</span>
                    <span class="font-mono font-bold text-emerald-400 text-base" x-text="'$' + precioConDescuento"></span>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="modalAbierto = false" class="w-1/3 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="w-2/3 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-lg shadow-emerald-500/25 transition-all">
                        Aplicar Descuento
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Script para Lógica del Modal -->
    <script>
        function alertasManager() {
            return {
                modalAbierto: false,
                loteSeleccionado: null,
                descuentoTipo: 'porcentaje',
                descuentoValor: '',

                abrirModalDescuento(lote) {
                    this.loteSeleccionado = lote;
                    this.descuentoTipo = 'porcentaje';
                    this.descuentoValor = lote.descuento_valor || 20;
                    this.modalAbierto = true;
                },

                get precioConDescuento() {
                    if (!this.loteSeleccionado) return '0.00';
                    const base = parseFloat(this.loteSeleccionado.precio_venta);
                    const val = parseFloat(this.descuentoValor) || 0;

                    let final = base;
                    if (this.descuentoTipo === 'porcentaje') {
                        final = base - (base * (val / 100));
                    } else {
                        final = base - val;
                    }
                    return Math.max(0, final).toFixed(2);
                },

                async guardarDescuento() {
                    if (!this.loteSeleccionado) return;

                    const payload = {
                        id_lote: this.loteSeleccionado.id_lote,
                        descuento_tipo: this.descuentoTipo,
                        descuento_valor: this.descuentoValor
                    };

                    try {
                        const response = await fetch('../../controllers/guardar_descuento.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (data.success) {
                            alert('¡Descuento aplicado correctamente!');
                            this.modalAbierto = false;
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (e) {
                        alert('Error al conectar con el servidor.');
                    }
                }
            }
        }
    </script>
</body>
</html>