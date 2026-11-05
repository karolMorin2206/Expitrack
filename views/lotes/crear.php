<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT id_producto, nombre, codigo FROM productos WHERE estado = 1 ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Registrar Lote</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 max-w-4xl mx-auto w-full space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Ingresar Lote de Producto</h1>
                <p class="text-sm text-slate-400 mt-1">Asigna número de lote, cantidad y fecha de caducidad para habilitar stock en caja.</p>
            </div>

            <form action="../../controllers/guardar_lote.php" method="POST" class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 lg:p-8 space-y-6 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Seleccionar Producto</label>
                        <select name="id_producto" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                            <option value="">-- Elige un producto --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?php echo $p['id_producto']; ?>"><?php echo htmlspecialchars($p['nombre'] . ' (' . $p['codigo'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Número / Clave de Lote</label>
                        <input type="text" name="numero_lote" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Ej. LOTE-2026-01">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Cantidad de Unidades</label>
                        <input type="number" name="cantidad" min="1" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="100">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Fecha de Ingreso</label>
                        <input type="date" name="fecha_ingreso" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Fecha de Caducidad</label>
                        <input type="date" name="fecha_caducidad" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all">
                    Registrar Lote en Inventario
                </button>
            </form>
        </main>
    </div>

</body>
</html>