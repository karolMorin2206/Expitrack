<?php
session_start();
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpiTrack | Iniciar Sesión</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-mesh {
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 165, 233, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.8) 0px, transparent 100%);
        }
    </style>
</head>
<body class="h-full bg-mesh flex items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-200">

    <!-- Card Principal -->
    <div class="w-full max-w-5xl bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl shadow-2xl shadow-emerald-950/20 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
        
        <!-- Panel Izquierdo (Branding & Features) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-emerald-950/40 to-slate-900 p-8 lg:p-10 flex flex-col justify-between relative overflow-hidden border-b lg:border-b-0 lg:border-r border-slate-800/80">
            <!-- Circulo decorativo de fondo -->
            <div class="absolute -top-20 -left-20 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <!-- Logo & Marca -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-lg shadow-emerald-500/30 flex items-center justify-center">
                        <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                            <i class="fa-solid fa-boxes-packing text-emerald-400 text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-1">
                            Expi<span class="text-emerald-400">Track</span>
                        </h1>
                        <p class="text-xs text-slate-400 font-medium">Control Inteligente</p>
                    </div>
                </div>

                <!-- Titulo & Descripción -->
                <div class="space-y-3 mb-8">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Sistema Multimodular
                    </span>
                    <h2 class="text-2xl font-bold text-white leading-snug">
                        Gestión integral de productos y vencimientos
                    </h2>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Controla tu inventario por lote, proyecta alertas de caducidad y optimiza tus ventas en tiempo real.
                    </p>
                </div>

                <!-- Grid de Módulos Destacados -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/50 hover:border-emerald-500/40 transition-all duration-300 group">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-boxes-stacked text-sm"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-200">Inventario</p>
                        <p class="text-[11px] text-slate-400">Control por lotes</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/50 hover:border-emerald-500/40 transition-all duration-300 group">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-200">Alertas</p>
                        <p class="text-[11px] text-slate-400">Próximos a vencer</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/50 hover:border-emerald-500/40 transition-all duration-300 group">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-tags text-sm"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-200">Descuentos</p>
                        <p class="text-[11px] text-slate-400">Reglas automáticas</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/50 hover:border-emerald-500/40 transition-all duration-300 group">
                        <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cash-register text-sm"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-200">Ventas</p>
                        <p class="text-[11px] text-slate-400">Punto de venta y ticket</p>
                    </div>
                </div>
            </div>

            <!-- Footer del panel izquierdo -->
            <div class="relative z-10 pt-6 mt-6 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-500">
                <span>Plataforma Académica</span>
                <span>v1.0.0</span>
            </div>
        </div>

        <!-- Panel Derecho (Formulario de Autenticación) -->
        <div class="lg:col-span-7 p-8 lg:p-12 flex flex-col justify-between bg-slate-900/60">
            <div>
                <!-- Encabezado del Formulario -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400 mb-1">
                            <i class="fa-solid fa-shield-halved"></i> Acceso Seguro
                        </span>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Iniciar Sesión</h2>
                        <p class="text-sm text-slate-400 mt-1">Ingresa tus credenciales para ingresar a la plataforma.</p>
                    </div>
                </div>

                <!-- Mensaje de Error (si se envía por GET) -->
                <?php if ($error == 1): ?>
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-start gap-3 text-rose-300 text-sm animate-fade-in">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 text-base mt-0.5"></i>
                    <div>
                        <strong class="font-semibold block text-rose-200">Error de autenticación</strong>
                        El correo electrónico o la contraseña ingresados son incorrectos.
                    </div>
                </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="../../controllers/login.php" method="POST" class="space-y-5">
                    <!-- Campo: Correo Electrónico -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email" required value="admin@expitrack.com"
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-950/70 border border-slate-800 rounded-2xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all"
                                placeholder="tu@correo.com">
                        </div>
                    </div>

                    <!-- Campo: Contraseña -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Contraseña
                        </label>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </div>
                            <input type="password" id="password" name="password" required value="123456"
                                class="w-full pl-11 pr-11 py-3.5 bg-slate-950/70 border border-slate-800 rounded-2xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all"
                                placeholder="••••••••">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                                <i class="fa-regular fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Opciones extra: Recordarme y Olvidé contraseña -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300 select-none">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-emerald-500 focus:ring-emerald-500/20 focus:ring-offset-0">
                            <span>Recordarme</span>
                        </label>
                        <a href="forgot.php" class="font-medium text-emerald-400 hover:text-emerald-300 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Botón de Envío -->
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 group">
                        <span>Ingresar a la Plataforma</span>
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>

            <!-- Caja de Credenciales de Prueba -->
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <div class="p-3.5 rounded-2xl bg-emerald-500/5 border border-emerald-500/10 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-slate-400">Usuario Demo:</span>
                        <code class="text-emerald-300 font-mono">admin@expitrack.com</code>
                    </div>
                    <span class="text-slate-500 font-mono">123456</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Script para Toggle Mostrar/Ocultar Contraseña -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>