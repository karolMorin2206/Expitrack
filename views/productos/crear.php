<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener categorías para el desplegable
try {
    $stmt = $pdo->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre ASC");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Nuevo Producto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 max-w-4xl mx-auto w-full space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Registrar Nuevo Producto</h1>
                    <p class="text-sm text-slate-400 mt-1">Llena los datos básicos del producto para agregarlo al catálogo.</p>
                </div>
                <a href="index.php" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Lista
                </a>
            </div>

            <form action="../../controllers/guardar_producto.php" method="POST" class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 lg:p-8 space-y-6 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Código de Barras / Clave</label>
                        <input type="text" name="codigo" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Ej. 750123456789">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Nombre del Producto</label>
                        <input type="text" name="nombre" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Ej. Leche Entera 1L">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Categoría</label>
                        <select name="id_categoria" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Precio de Venta ($)</label>
                        <input type="number" step="0.01" name="precio_venta" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 font-mono text-sm focus:outline-none focus:border-emerald-500" placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Precio de Compra ($)</label>
                        <input type="number" step="0.01" name="precio_compra" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 font-mono text-sm focus:outline-none focus:border-emerald-500" placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Stock Mínimo para Alerta</label>
                        <input type="number" name="stock_minimo" value="5" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Descripción (Opcional)</label>
                    <textarea name="descripcion" rows="2" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Detalles del producto..."></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all">
                    Guardar Producto
                </button>
            </form>
        </main>
    </div>

</body>
</html>