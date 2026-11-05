<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Cargar catálogo de productos activos considerando ÚNICAMENTE lotes con stock > 0
try {
    $stmt = $pdo->query("
        SELECT 
            p.id_producto,
            p.codigo,
            p.nombre,
            p.precio_venta AS precio_regular,
            c.nombre AS categoria,
            COALESCE(SUM(l_sum.cantidad), 0) AS stock_disponible,
            l_proximo.descuento_tipo,
            COALESCE(l_proximo.descuento_valor, 0) AS descuento_valor
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        -- Suma total de stock activo con unidades disponibles
        LEFT JOIN lotes l_sum ON p.id_producto = l_sum.id_producto 
            AND l_sum.estado = 1 
            AND l_sum.cantidad > 0 
            AND l_sum.fecha_caducidad >= CURDATE()
        -- Subconsulta para traer únicamente el descuento del PRÓXIMO lote activo a vender (con cantidad > 0)
        LEFT JOIN lotes l_proximo ON l_proximo.id_lote = (
            SELECT id_lote 
            FROM lotes 
            WHERE id_producto = p.id_producto 
              AND estado = 1 
              AND cantidad > 0 
              AND fecha_caducidad >= CURDATE()
            ORDER BY fecha_caducidad ASC, id_lote ASC
            LIMIT 1
        )
        WHERE p.estado = 1
        GROUP BY p.id_producto
        HAVING stock_disponible > 0
        ORDER BY p.nombre ASC
    ");
    $productos_bd = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar precios con descuento dinámico del lote vigente con stock
    $productos = array_map(function($p) {
        $precio_regular = floatval($p['precio_regular']);
        $descuento_valor = floatval($p['descuento_valor']);
        $precio_final = $precio_regular;

        if ($descuento_valor > 0 && !empty($p['descuento_tipo'])) {
            if ($p['descuento_tipo'] === 'porcentaje') {
                $precio_final = $precio_regular - ($precio_regular * ($descuento_valor / 100));
            } else {
                $precio_final = $precio_regular - $descuento_valor;
            }
        }

        $p['precio_venta'] = max(0, $precio_final);
        $p['tiene_descuento'] = ($descuento_valor > 0);
        return $p;
    }, $productos_bd);

    // Obtener lista de clientes para Fiado
    $stmtClientes = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $productos = [];
    $clientes = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Punto de Venta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans" x-data="posData()">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-4 lg:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 overflow-hidden">
            
            <!-- COLUMNA IZQUIERDA: Catálogo & Búsqueda -->
            <section class="lg:col-span-7 flex flex-col min-h-0 bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl">
                
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text" x-model="busqueda" placeholder="Buscar por nombre o código de barras..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition-all"
                            @keydown.enter.prevent="buscarYAgregarPorCodigo()">
                    </div>

                    <!-- Botón Corte del Día -->
                    <button @click="obtenerCorteDia()" class="px-4 py-3 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Corte del Día</span>
                    </button>
                </div>

                <!-- Grid de Productos -->
                <div class="flex-1 overflow-y-auto pr-1 grid grid-cols-2 sm:grid-cols-3 gap-3.5 content-start">
                    <template x-for="p in productosFiltrados" :key="p.id_producto">
                        <button type="button" @click="agregarAlCarrito(p)"
                            class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800/80 text-left transition-all duration-200 group flex flex-col justify-between relative overflow-hidden shadow-md">
                            
                            <template x-if="p.tiene_descuento">
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[10px] font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-fire text-[9px]"></i> Oferta
                                </span>
                            </template>

                            <div>
                                <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block mb-1" x-text="p.categoria"></span>
                                <h3 class="font-bold text-white text-sm line-clamp-2 leading-tight group-hover:text-emerald-300 transition-colors" x-text="p.nombre"></h3>
                                <p class="text-[11px] text-slate-500 font-mono mt-1" x-text="'Cód: ' + p.codigo"></p>
                            </div>

                            <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                <span class="text-xs text-slate-400 font-medium" x-text="'Stock: ' + p.stock_disponible"></span>
                                <div class="text-right">
                                    <template x-if="p.tiene_descuento">
                                        <span class="text-[11px] text-slate-500 line-through font-mono block" x-text="'$' + parseFloat(p.precio_regular).toFixed(2)"></span>
                                    </template>
                                    <span class="text-sm font-extrabold text-emerald-400 font-mono" x-text="'$' + parseFloat(p.precio_venta).toFixed(2)"></span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </section>

            <!-- COLUMNA DERECHA: Carrito -->
            <section class="lg:col-span-5 flex flex-col min-h-0 bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-cart-shopping text-emerald-400"></i>
                            <span>Orden de Venta</span>
                        </h2>
                        <button type="button" @click="vaciarCarrito()" x-show="carrito.length > 0" class="text-xs text-rose-400 hover:text-rose-300 font-semibold transition-colors">
                            <i class="fa-solid fa-trash-can mr-1"></i> Vaciar
                        </button>
                    </div>

                    <div class="overflow-y-auto max-h-[340px] space-y-3 pr-1">
                        <template x-if="carrito.length === 0">
                            <div class="text-center py-12 text-slate-500">
                                <i class="fa-solid fa-basket-shopping text-4xl mb-3 block text-slate-700"></i>
                                <p class="text-sm font-medium">El carrito está vacío</p>
                            </div>
                        </template>

                        <template x-for="(item, index) in carrito" :key="item.id_producto">
                            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-white text-xs truncate" x-text="item.nombre"></h4>
                                    <span class="text-[11px] font-mono text-slate-400" x-text="'$' + parseFloat(item.precio_venta).toFixed(2) + ' c/u'"></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="cambiarCantidad(index, -1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs font-bold">-</button>
                                    <span class="w-6 text-center text-xs font-bold font-mono text-white" x-text="item.cantidad"></span>
                                    <button @click="cambiarCantidad(index, 1)" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs font-bold">+</button>
                                </div>

                                <div class="text-right font-mono text-xs font-bold text-emerald-400 min-w-[60px]" x-text="'$' + (item.cantidad * item.precio_venta).toFixed(2)"></div>

                                <button @click="eliminarItem(index)" class="text-slate-500 hover:text-rose-400 text-xs p-1">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 space-y-3 mt-4">
                    <div class="flex items-center justify-between text-base font-extrabold text-white">
                        <span>Total a Cobrar</span>
                        <span class="font-mono text-2xl text-emerald-400" x-text="'$' + total.toFixed(2)"></span>
                    </div>

                    <button @click="abrirModalPago()" :disabled="carrito.length === 0"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 disabled:opacity-50 text-white font-bold text-sm shadow-lg shadow-emerald-500/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Procesar Pago</span>
                    </button>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL DE PAGO -->
    <div x-show="modalPago" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4" x-cloak>
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-lg text-white">Confirmar Transacción</h3>
                <button @click="modalPago = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Métodos de Pago -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Método de Pago</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="metodoPago = 'EFECTIVO'" :class="metodoPago === 'EFECTIVO' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="p-3 rounded-xl border text-xs font-bold text-center transition-all">Efectivo</button>
                    <button type="button" @click="metodoPago = 'TARJETA'" :class="metodoPago === 'TARJETA' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="p-3 rounded-xl border text-xs font-bold text-center transition-all">Tarjeta</button>
                    <button type="button" @click="metodoPago = 'FIADO'" :class="metodoPago === 'FIADO' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-slate-950 border-slate-800 text-slate-400'" class="p-3 rounded-xl border text-xs font-bold text-center transition-all">Fiado</button>
                </div>
            </div>

            <!-- MODO EFECTIVO -->
            <div x-show="metodoPago === 'EFECTIVO'" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Monto Recibido</label>
                    <input type="number" step="0.01" x-model.number="pagoRecibido" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl font-mono text-lg text-emerald-400 focus:outline-none focus:border-emerald-500">
                </div>
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Cambio:</span>
                    <span class="font-mono font-bold text-lg text-emerald-400" x-text="'$' + (cambio >= 0 ? cambio.toFixed(2) : '0.00')"></span>
                </div>
            </div>

            <!-- MODO FIADO (DATOS DE CLIENTE) -->
            <div x-show="metodoPago === 'FIADO'" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-amber-400 uppercase mb-2">Seleccionar o Registrar Cliente</label>
                    
                    <!-- Desplegable Clientes Registrados -->
                    <select x-model="clienteSeleccionado" @change="cargarDatosCliente()" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-amber-500 mb-3">
                        <option value="NUEVO">-- Nuevo Cliente / No Registrado --</option>
                        <template x-for="c in clientes" :key="c.id_cliente">
                            <option :value="c.id_cliente" x-text="c.nombre + ' (' + (c.telefono || 'Sin tel') + ')'"></option>
                        </template>
                    </select>

                    <!-- Campos de Entrada -->
                    <div class="space-y-2">
                        <input type="text" x-model="clienteNombre" placeholder="Nombre Completo del Cliente *" required
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-amber-500">
                        
                        <input type="text" x-model="clienteTelefono" placeholder="Teléfono de Contacto"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-xs focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300">
                    <i class="fa-solid fa-info-circle mr-1"></i> Este saldo de <strong>$<span x-text="total.toFixed(2)"></span></strong> se sumará a la cuenta del cliente.
                </div>
            </div>

            <button @click="finalizarVenta()" :disabled="(metodoPago === 'EFECTIVO' && pagoRecibido < total) || (metodoPago === 'FIADO' && !clienteNombre.trim())"
                class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white font-bold text-sm shadow-lg transition-all">
                Completar Venta e Imprimir Ticket
            </button>
        </div>
    </div>

    <!-- MODAL CORTE DEL DÍA -->
    <div x-show="modalCorte" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4" x-cloak>
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-cash-register text-amber-400 text-lg"></i>
                    <h3 class="font-bold text-lg text-white">Corte de Caja del Día</h3>
                </div>
                <button @click="modalCorte = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <template x-if="corteData">
                <div class="space-y-3">
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                        <span class="text-slate-400 flex items-center gap-2"><i class="fa-solid fa-money-bill-wave text-emerald-400"></i> Ventas en Efectivo</span>
                        <span class="font-mono font-bold text-white text-sm" x-text="'$' + parseFloat(corteData.efectivo).toFixed(2)"></span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                        <span class="text-slate-400 flex items-center gap-2"><i class="fa-solid fa-credit-card text-teal-400"></i> Ventas con Tarjeta</span>
                        <span class="font-mono font-bold text-white text-sm" x-text="'$' + parseFloat(corteData.tarjeta).toFixed(2)"></span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-xl border border-amber-500/30 bg-amber-500/5 flex justify-between items-center text-xs">
                        <span class="text-amber-300 flex items-center gap-2"><i class="fa-solid fa-handshake text-amber-400"></i> Total Fiado (Pendiente Cobro)</span>
                        <span class="font-mono font-bold text-amber-400 text-sm" x-text="'$' + parseFloat(corteData.fiado).toFixed(2)"></span>
                    </div>

                    <!-- TOTAL NETO COBRADO (Sin sumar Fiado) -->
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex justify-between items-center pt-3">
                        <div>
                            <span class="text-xs text-slate-400 block font-semibold uppercase">Total Cobrado en Caja</span>
                            <span class="text-[10px] text-slate-500">(Efectivo + Tarjeta)</span>
                        </div>
                        <span class="font-mono font-extrabold text-2xl text-emerald-400" x-text="'$' + parseFloat(corteData.total_caja).toFixed(2)"></span>
                    </div>
                </div>
            </template>

            <button @click="modalCorte = false" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all">
                Cerrar Ventana
            </button>
        </div>
    </div>

    <script>
        function posData() {
            return {
                busqueda: '',
                productos: <?php echo json_encode($productos); ?>,
                clientes: <?php echo json_encode($clientes); ?>,
                carrito: [],
                modalPago: false,
                modalCorte: false,
                corteData: null,
                metodoPago: 'EFECTIVO',
                pagoRecibido: 0,
                clienteSeleccionado: 'NUEVO',
                clienteNombre: '',
                clienteTelefono: '',

                get productosFiltrados() {
                    if (!this.busqueda) return this.productos;
                    const q = this.busqueda.toLowerCase();
                    return this.productos.filter(p => p.nombre.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q));
                },

                get total() {
                    return this.carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_venta), 0);
                },

                get cambio() {
                    return this.pagoRecibido - this.total;
                },

                cargarDatosCliente() {
                    if (this.clienteSeleccionado === 'NUEVO') {
                        this.clienteNombre = '';
                        this.clienteTelefono = '';
                    } else {
                        const cli = this.clientes.find(c => c.id_cliente == this.clienteSeleccionado);
                        if (cli) {
                            this.clienteNombre = cli.nombre;
                            this.clienteTelefono = cli.telefono || '';
                        }
                    }
                },

                agregarAlCarrito(producto) {
                    const idx = this.carrito.findIndex(i => i.id_producto === producto.id_producto);
                    if (idx > -1) {
                        if (this.carrito[idx].cantidad < producto.stock_disponible) {
                            this.carrito[idx].cantidad++;
                        }
                    } else {
                        this.carrito.push({ ...producto, cantidad: 1 });
                    }
                },

                buscarYAgregarPorCodigo() {
                    const prod = this.productos.find(p => p.codigo.toLowerCase() === this.busqueda.trim().toLowerCase());
                    if (prod) {
                        this.agregarAlCarrito(prod);
                        this.busqueda = '';
                    }
                },

                cambiarCantidad(index, delta) {
                    const item = this.carrito[index];
                    const nuevaCant = item.cantidad + delta;
                    if (nuevaCant > 0 && nuevaCant <= item.stock_disponible) {
                        item.cantidad = nuevaCant;
                    }
                },

                eliminarItem(index) {
                    this.carrito.splice(index, 1);
                },

                vaciarCarrito() {
                    this.carrito = [];
                },

                abrirModalPago() {
                    this.pagoRecibido = this.total;
                    this.clienteSeleccionado = 'NUEVO';
                    this.clienteNombre = '';
                    this.clienteTelefono = '';
                    this.modalPago = true;
                },

                async obtenerCorteDia() {
                    try {
                        const res = await fetch('../../controllers/corte_caja.php');
                        this.corteData = await res.json();
                        this.modalCorte = true;
                    } catch (e) {
                        alert('Error al consultar el corte de caja.');
                    }
                },

                async finalizarVenta() {
                    const payload = {
                        metodo_pago: this.metodoPago,
                        total: this.total,
                        pago_recibido: this.pagoRecibido,
                        cambio: Math.max(0, this.cambio),
                        cliente_id: this.clienteSeleccionado,
                        cliente_nombre: this.clienteNombre,
                        cliente_telefono: this.clienteTelefono,
                        items: this.carrito
                    };

                    try {
                        const response = await fetch('../../controllers/procesar_venta.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const res = await response.json();

                        if (res.success) {
                            window.location.href = 'ticket.php?id_venta=' + res.id_venta;
                        } else {
                            alert('Error al registrar la venta: ' + res.message);
                        }
                    } catch (e) {
                        alert('Error de conexión al procesar la venta.');
                    }
                }
            }
        }
    </script>
</body>
</html>