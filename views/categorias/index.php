<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Guardar nueva categoría si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_categoria'])) {
    $nombre = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!empty($nombre)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
            $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion]);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al crear la categoría: " . $e->getMessage();
        }
    }
}

// Obtener todas las categorías
try {
    $stmt = $pdo->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Categorías</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden font-sans">

    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <?php include_once __DIR__ . '/../layouts/navbar.php'; ?>

        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Formulario Crear Categoría (4 Cols) -->
            <div class="lg:col-span-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-folder-plus text-emerald-400"></i> Nueva Categoría
                    </h2>

                    <form action="index.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Nombre</label>
                            <input type="text" name="nombre_categoria" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Ej. Lácteos, Bebidas">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Descripción (Opcional)</label>
                            <textarea name="descripcion" rows="3" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-emerald-500" placeholder="Breve descripción..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all">
                            Guardar Categoría
                        </button>
                    </form>
                </div>
            </div>

            <!-- Listado de Categorías (8 Cols) -->
            <div class="lg:col-span-8">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-emerald-400"></i> Categorías Registradas
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-950/60 border-b border-slate-800">
                                <tr>
                                    <th class="p-3.5">ID</th>
                                    <th class="p-3.5">Categoría</th>
                                    <th class="p-3.5">Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php foreach ($categorias as $cat): ?>
                                <tr class="hover:bg-slate-800/40">
                                    <td class="p-3.5 font-mono text-xs text-slate-500">#<?php echo $cat['id_categoria']; ?></td>
                                    <td class="p-3.5 font-semibold text-white"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                    <td class="p-3.5 text-slate-400 text-xs"><?php echo htmlspecialchars($cat['descripcion'] ?: 'Sin descripción'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>