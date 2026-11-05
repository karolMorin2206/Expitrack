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
    // Obtenemos TODOS los lotes
    $stmt =$pdo->query("
        SELECT 
            l.id_lote,
            l.numero_lote,
            l.cantidad,
            l.fecha_ingreso,
            l.fecha_caducidad,
            l.descuento_tipo,
            l.descuento_valor,
            l.estado,
            p.nombre AS producto_nombre,
            p.codigo AS producto_codigo,
            c.nombre AS categoria
        FROM lotes l
        INNER JOIN productos p ON l.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        ORDER BY l.fecha_caducidad ASC
    ");
    $lotes =$stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Métricas para tarjetas superiores (Regla de 30 días)
    $total_lotes = 0;
    $lotes_activos = 0;
    $lotes_criticos = 0;
    $lotes_vencidos = 0;
    $lotes_agotados = 0;
    $lotes_archivados = 0;

    $hoy = date('Y-m-d');$limite_critico = date('Y-m-d', strtotime('+30 days'));

    foreach ($lotes as$l) {
        if (intval($l['estado']) === 0) {$lotes_archivados++;
            continue;
        }

        $total_lotes++;
        $cant = intval($l['cantidad']);

        if ($cant === 0) {$lotes_agotados++;
        } else {
            if ($l['fecha_caducidad'] < $hoy) {$lotes_vencidos++;
            } elseif ($l['fecha_caducidad'] <= $limite_critico) {$lotes_criticos++;
            } else {
                $lotes_activos++;
            }
        }
    }

} catch (PDOException $e) {$lotes = [];
    $total_lotes =$lotes_activos = $lotes_criticos =$lotes_vencidos = $lotes_agotados =$lotes_archivados = 0;
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Control de Lotes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans" x-data="lotesManager()">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">
            
            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-emerald-400"></i>
                        <span>Control de Lotes</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Monitoreo de semáforo de vencimientos y gestión de inventario por lote.</p>
                </div>
                
                <a href="crear.php" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2 self-start sm:self-auto">
                    <i class="fa-solid fa-plus"></i> Registrar Nuevo Lote
                </a>
            </div>

            <!-- Resumen de Tarjetas con Semáforo (Ajustado a 30 días) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Verde: Vigentes -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider block">Buen Estado</span>
                        <span class="text-2xl font-extrabold text-emerald-400 mt-1 block"><?php echo number_format($lotes_activos); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- Amarillo: Próximos a Caducar (30 Días) -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider block">Próximos a Caducar</span>
                        <span class="text-2xl font-extrabold text-amber-300 mt-1 block"><?php echo number_format($lotes_criticos); ?></span>
                        <span class="text-[10px] text-slate-500 font-medium">En los próximos 30 días</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <!-- Rojo: Caducados -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-400 uppercase tracking-wider block">Lotes Vencidos</span>
                        <span class="text-2xl font-extrabold text-rose-300 mt-1 block"><?php echo number_format($lotes_vencidos); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl border border-rose-500/20">
                        <i class="fa-solid fa-skull-crossbones"></i>
                    </div>
                </div>

                <!-- Gris: Agotados -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Agotados (0 u.)</span>
                        <span class="text-2xl font-extrabold text-slate-300 mt-1 block"><?php echo number_format($lotes_agotados); ?></span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center text-xl border border-slate-700">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                </div>

            </div>

            <!-- Tabla con Semáforo y Filtros -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Buscar por producto, N° lote o categoría..."
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 transition-all">
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <button @click="filtroEstado = 'TODOS'" :class="filtroEstado === 'TODOS' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">Todos los Activos</button>
                        <button @click="filtroEstado = 'BUENO'" :class="filtroEstado === 'BUENO' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">🟢 Buen Estado</button>
                        <button @click="filtroEstado = 'POR_VENCER'" :class="filtroEstado === 'POR_VENCER' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">🟡 Por Vencer (30 Días)</button>
                        <button @click="filtroEstado = 'CADUCADO'" :class="filtroEstado === 'CADUCADO' ? 'bg-rose-500/20 border-rose-500 text-rose-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">🔴 Caducados</button>
                        <button @click="filtroEstado = 'AGOTADO'" :class="filtroEstado === 'AGOTADO' ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all">⚪ Agotados</button>
                        
                        <button @click="filtroEstado = 'ARCHIVADOS'" :class="filtroEstado === 'ARCHIVADOS' ? 'bg-purple-500/20 border-purple-500 text-purple-300' : 'bg-slate-950 border-slate-800 text-slate-400'" class="px-3 py-2 rounded-xl border transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-box-archive"></i>
                            <span>Archivados (<?php echo $lotes_archivados; ?>)</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">Semáforo</th>
                                <th class="p-3.5">N° Lote</th>
                                <th class="p-3.5">Producto</th>
                                <th class="p-3.5">Categoría</th>
                                <th class="p-3.5">Stock</th>
                                <th class="p-3.5">F. Caducidad</th>
                                <th class="p-3.5">Descuento</th>
                                <th class="p-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-if="lotesFiltrados.length === 0">
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-500">
                                        No se encontraron lotes bajo este filtro.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="lote in lotesFiltrados" :key="lote.id_lote">
                                <tr class="hover:bg-slate-800/40 transition-colors" :class="parseInt(lote.estado) === 0 ? 'opacity-60 bg-slate-950/40' : ''">
                                    
                                    <!-- INDICADOR DE SEMÁFORO -->
                                    <td class="p-3.5">
                                        <template x-if="parseInt(lote.estado) === 0">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                                <i class="fa-solid fa-box-archive text-[10px]"></i> Archivado
                                            </span>
                                        </template>
                                        <template x-if="parseInt(lote.estado) === 1 && parseInt(lote.cantidad) == 0">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                                <span class="w-2 h-2 rounded-full bg-slate-500"></span> Agotado
                                            </span>
                                        </template>
                                        <template x-if="parseInt(lote.estado) === 1 && parseInt(lote.cantidad) > 0 && esVencido(lote.fecha_caducidad)">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Caducado
                                            </span>
                                        </template>
                                        <template x-if="parseInt(lote.estado) === 1 && parseInt(lote.cantidad) > 0 && !esVencido(lote.fecha_caducidad) && esCritico(lote.fecha_caducidad)">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Por Vencer
                                            </span>
                                        </template>
                                        <template x-if="parseInt(lote.estado) === 1 && parseInt(lote.cantidad) > 0 && !esVencido(lote.fecha_caducidad) && !esCritico(lote.fecha_caducidad)">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Buen Estado
                                            </span>
                                        </template>
                                    </td>

                                    <td class="p-3.5 font-mono text-xs font-bold text-slate-200" x-text="lote.numero_lote"></td>
                                    <td class="p-3.5 font-semibold text-white">
                                        <span x-text="lote.producto_nombre"></span>
                                        <span class="block text-[10px] text-slate-500 font-mono" x-text="'Cód: ' + lote.producto_codigo"></span>
                                    </td>
                                    <td class="p-3.5 text-slate-400 text-xs" x-text="lote.categoria"></td>
                                    <td class="p-3.5 font-mono font-bold" :class="parseInt(lote.cantidad) == 0 ? 'text-slate-600' : 'text-emerald-400'" x-text="lote.cantidad + ' u.'"></td>
                                    <td class="p-3.5 font-mono text-xs font-bold" :class="parseInt(lote.estado) === 1 && parseInt(lote.cantidad) > 0 && esVencido(lote.fecha_caducidad) ? 'text-rose-400' : (parseInt(lote.estado) === 1 && parseInt(lote.cantidad) > 0 && esCritico(lote.fecha_caducidad) ? 'text-amber-400' : 'text-slate-300')" x-text="formatFecha(lote.fecha_caducidad)"></td>
                                    <td class="p-3.5 font-mono text-xs">
                                        <template x-if="parseFloat(lote.descuento_valor) > 0">
                                            <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 font-bold"
                                                x-text="lote.descuento_tipo === 'porcentaje' ? lote.descuento_valor + '%' : '$' + lote.descuento_valor"></span>
                                        </template>
                                        <template x-if="!parseFloat(lote.descuento_valor) || parseFloat(lote.descuento_valor) === 0">
                                            <span class="text-slate-600">-</span>
                                        </template>
                                    </td>

                                    <!-- ACCIONES -->
                                    <td class="p-3.5 text-right">
                                        <template x-if="parseInt(lote.estado) === 1">
                                            <button @click="cambiarEstadoLote(lote, 0)" title="Archivar lote" 
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-amber-400 border border-slate-700 transition-all text-xs font-medium flex items-center gap-1.5 ml-auto">
                                                <i class="fa-solid fa-box-archive"></i>
                                                <span>Archivar</span>
                                            </button>
                                        </template>

                                        <template x-if="parseInt(lote.estado) === 0">
                                            <button @click="cambiarEstadoLote(lote, 1)" title="Restaurar lote" 
                                                class="px-2.5 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 border border-purple-500/30 transition-all text-xs font-medium flex items-center gap-1.5 ml-auto">
                                                <i class="fa-solid fa-rotate-left"></i>
                                                <span>Restaurar</span>
                                            </button>
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
        function lotesManager() {
            return {
                search: '',
                filtroEstado: 'TODOS',
                lotes: <?php echo json_encode($lotes); ?>,
                hoy: '<?php echo date('Y-m-d'); ?>',

                get lotesFiltrados() {
                    return this.lotes.filter(l => {
                        const q = this.search.toLowerCase();
                        const matchText = l.producto_nombre.toLowerCase().includes(q) || 
                                          l.numero_lote.toLowerCase().includes(q) || 
                                          l.categoria.toLowerCase().includes(q) ||
                                          l.producto_codigo.toLowerCase().includes(q);

                        const est = parseInt(l.estado);
                        const cant = parseInt(l.cantidad);
                        const vencido = this.esVencido(l.fecha_caducidad);
                        const critico = this.esCritico(l.fecha_caducidad);

                        if (this.filtroEstado === 'ARCHIVADOS') {
                            return matchText && est === 0;
                        }

                        if (est === 0) return false;

                        let matchEstado = true;
                        if (this.filtroEstado === 'BUENO') {
                            matchEstado = (cant > 0 && !vencido && !critico);
                        } else if (this.filtroEstado === 'POR_VENCER') {
                            matchEstado = (cant > 0 && !vencido && critico);
                        } else if (this.filtroEstado === 'CADUCADO') {
                            matchEstado = (cant > 0 && vencido);
                        } else if (this.filtroEstado === 'AGOTADO') {
                            matchEstado = (cant === 0);
                        }

                        return matchText && matchEstado;
                    });
                },

                esVencido(fecha) {
                    return fecha < this.hoy;
                },

                // Lógica de cálculo ajustada a 30 días
                esCritico(fecha) {
                    if (this.esVencido(fecha)) return false;
                    const fCad = new Date(fecha);
                    const fHoy = new Date(this.hoy);
                    const diffTime = fCad - fHoy;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    return diffDays <= 30;
                },

                formatFecha(fecha) {
                    if (!fecha) return '-';
                    const parts = fecha.split('-');
                    if (parts.length === 3) {
                        return parts[2] + '/' + parts[1] + '/' + parts[0];
                    }
                    return fecha;
                },

                async cambiarEstadoLote(lote, nuevoEstado) {
                    const accionText = nuevoEstado === 0 ? 'archivar' : 'restaurar';
                    if (!confirm(`¿Deseas ${accionText} el lote "${lote.numero_lote}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('../../controllers/archivar_lote.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_lote: lote.id_lote, estado: nuevoEstado })
                        });
                        const data = await response.json();

                        if (data.success) {
                            lote.estado = nuevoEstado;
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (e) {
                        alert(`Error de conexión al ${accionText} el lote.`);
                    }
                }
            }
        }
    </script>
</body>
</html>