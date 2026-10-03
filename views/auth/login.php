<?php
session_start();

if(isset($_SESSION['id_usuario'])){
    header("Location: ../dashboard/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ExpiTrack | Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html, body{
    width:100%;
    height:100%;
    overflow:hidden;
}

body{
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:
        radial-gradient(circle at top left, rgba(34,197,94,.18), transparent 32%),
        radial-gradient(circle at bottom right, rgba(14,165,233,.22), transparent 35%),
        linear-gradient(135deg,#eef6f4,#f8fafc);
}

/* CONTENEDOR GENERAL */

.auth-shell{
    width:100%;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
}

/* TARJETA CENTRAL */

.auth-card{
    width:min(1120px, 100%);
    height:min(680px, calc(100vh - 48px));
    background:#ffffff;
    border-radius:34px;
    display:grid;
    grid-template-columns:.95fr 1.05fr;
    overflow:hidden;
    box-shadow:0 30px 90px rgba(15,23,42,.18);
}

/* PANEL IZQUIERDO */

.brand-panel{
    position:relative;
    background:
        linear-gradient(145deg, rgba(4,20,35,.96), rgba(7,44,64,.96)),
        radial-gradient(circle at 20% 20%, rgba(34,197,94,.25), transparent 35%),
        radial-gradient(circle at 80% 80%, rgba(14,165,233,.25), transparent 35%);
    color:white;
    padding:44px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    overflow:hidden;
}

.brand-panel::before{
    content:"";
    position:absolute;
    width:280px;
    height:280px;
    border-radius:50%;
    background:rgba(34,197,94,.16);
    top:-90px;
    right:-90px;
}

.brand-panel::after{
    content:"";
    position:absolute;
    width:230px;
    height:230px;
    border-radius:50%;
    background:rgba(14,165,233,.14);
    bottom:-80px;
    left:-70px;
}

.brand-top,
.brand-bottom{
    position:relative;
    z-index:2;
}

.logo-frame{
    width:120px;
    height:120px;
    border-radius:26px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.16);
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:24px;
}

.logo-frame img{
    width:95px;
    height:95px;
    object-fit:contain;
}

.brand-panel h1{
    font-size:3.6rem;
    font-weight:900;
    letter-spacing:-2px;
    margin-bottom:12px;
}

.brand-panel p{
    color:#cbd5e1;
    font-size:1.05rem;
    line-height:1.6;
    max-width:380px;
}

.brand-stats{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-top:28px;
}

.stat-box{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:18px;
    padding:16px;
    backdrop-filter:blur(10px);
}

.stat-box i{
    color:#4ade80;
    font-size:1.2rem;
    margin-bottom:10px;
}

.stat-box strong{
    display:block;
    font-size:.95rem;
    margin-bottom:3px;
}

.stat-box span{
    color:#cbd5e1;
    font-size:.78rem;
}

.brand-bottom{
    font-size:.85rem;
    color:#94a3b8;
}

/* PANEL DERECHO */

.login-panel{
    background:#ffffff;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:44px;
}

.login-box{
    width:100%;
    max-width:420px;
}

.mobile-logo{
    display:none;
    text-align:center;
    margin-bottom:16px;
}

.mobile-logo img{
    width:76px;
    height:76px;
    object-fit:contain;
}

.login-label{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 14px;
    border-radius:999px;
    background:#ecfdf5;
    color:#047857;
    font-weight:800;
    font-size:.83rem;
    margin-bottom:18px;
}

.login-box h2{
    font-size:2.25rem;
    font-weight:900;
    color:#0f172a;
    margin-bottom:8px;
}

.login-box .subtitle{
    color:#64748b;
    margin-bottom:24px;
    font-size:.98rem;
}

/* ALERTAS */

.alert{
    border:none;
    border-radius:16px;
    padding:13px 15px;
    font-weight:700;
    font-size:.92rem;
}

/* FORMULARIO */

.form-block{
    margin-bottom:18px;
}

.form-block label{
    display:block;
    color:#334155;
    font-weight:800;
    margin-bottom:8px;
    font-size:.92rem;
}

.input-line{
    height:56px;
    border:1px solid #dbe4ef;
    border-radius:18px;
    background:#f8fafc;
    display:flex;
    align-items:center;
    padding:0 15px;
    transition:.22s ease;
}

.input-line:focus-within{
    background:white;
    border-color:#0ea5e9;
    box-shadow:0 0 0 4px rgba(14,165,233,.12);
}

.input-line i{
    color:#64748b;
    margin-right:11px;
}

.input-line input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    color:#0f172a;
    font-size:1rem;
}

.input-line input::placeholder{
    color:#94a3b8;
}

.eye-btn{
    border:none;
    background:transparent;
    color:#64748b;
    padding:0;
}

/* OPCIONES */

.login-options{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:6px 0 22px;
    gap:10px;
}

.remember{
    display:flex;
    align-items:center;
    gap:7px;
    color:#475569;
    font-weight:600;
    font-size:.9rem;
}

.remember input{
    accent-color:#0ea5e9;
}

.login-options a{
    color:#0284c7;
    text-decoration:none;
    font-weight:800;
    font-size:.9rem;
}

.login-options a:hover{
    text-decoration:underline;
}

/* BOTÓN */

.login-btn{
    width:100%;
    height:58px;
    border:none;
    border-radius:18px;
    background:linear-gradient(135deg,#16a34a,#0284c7);
    color:white;
    font-weight:900;
    font-size:1.04rem;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    box-shadow:0 16px 32px rgba(2,132,199,.24);
    transition:.22s ease;
}

.login-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 20px 38px rgba(2,132,199,.30);
}

/* INFO */

.quick-info{
    margin-top:22px;
    padding:14px;
    border-radius:18px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    text-align:center;
}

.quick-info small{
    display:block;
    color:#64748b;
    margin-bottom:4px;
    font-weight:700;
}

.quick-info strong{
    color:#0f172a;
    font-size:.92rem;
}

.footer{
    text-align:center;
    margin-top:18px;
    color:#94a3b8;
    font-size:.82rem;
    font-weight:600;
}

/* RESPONSIVE SIN SCROLL */

@media(max-width:992px){

    .auth-shell{
        padding:18px;
    }

    .auth-card{
        grid-template-columns:1fr;
        height:calc(100vh - 36px);
        max-width:480px;
    }

    .brand-panel{
        display:none;
    }

    .login-panel{
        padding:28px;
    }

    .mobile-logo{
        display:block;
    }

    .login-box h2{
        font-size:2rem;
        text-align:center;
    }

    .login-box .subtitle{
        text-align:center;
    }

    .login-label{
        display:flex;
        width:max-content;
        margin:0 auto 16px;
    }

    .login-options{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>
</head>

<body>

<div class="auth-shell">

    <div class="auth-card">

        <!-- PANEL IZQUIERDO -->
        <div class="brand-panel">

            <div class="brand-top">

                <div class="logo-frame">
                    <img src="../../assets/img/logo.png" alt="Logo ExpiTrack">
                </div>

                <h1>ExpiTrack</h1>

                <p>
                    Sistema para controlar inventario, caducidades, descuentos y ventas de forma inteligente.
                </p>

                <div class="brand-stats">

                    <div class="stat-box">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <strong>Inventario</strong>
                        <span>Control por lote</span>
                    </div>

                    <div class="stat-box">
                        <i class="fa-solid fa-bell"></i>
                        <strong>Alertas</strong>
                        <span>Caducidades</span>
                    </div>

                    <div class="stat-box">
                        <i class="fa-solid fa-tags"></i>
                        <strong>Descuentos</strong>
                        <span>Automáticos</span>
                    </div>

                    <div class="stat-box">
                        <i class="fa-solid fa-cash-register"></i>
                        <strong>Ventas</strong>
                        <span>Punto de venta</span>
                    </div>

                </div>

            </div>

            <div class="brand-bottom">
                Control inteligente, menos pérdidas, más ganancias.
            </div>

        </div>

        <!-- PANEL DERECHO -->
        <div class="login-panel">

            <div class="login-box">

                <div class="mobile-logo">
                    <img src="../../assets/img/logo.png" alt="Logo ExpiTrack">
                </div>

                <div class="login-label">
                    <i class="fa-solid fa-shield-halved"></i>
                    Acceso seguro
                </div>

                <h2>Iniciar sesión</h2>

                <p class="subtitle">
                    Ingresa tus datos para acceder al sistema.
                </p>

                <?php if(isset($_GET['error']) && $_GET['error'] == 'correo'){ ?>
                    <div class="alert alert-danger">
                        El correo no está registrado.
                    </div>
                <?php } ?>

                <?php if(isset($_GET['error']) && $_GET['error'] == 'password'){ ?>
                    <div class="alert alert-danger">
                        La contraseña es incorrecta.
                    </div>
                <?php } ?>

                <?php if(isset($_GET['error']) && $_GET['error'] == 'verificar'){ ?>
                    <div class="alert alert-warning">
                        Tu cuenta todavía no está verificada.
                    </div>
                <?php } ?>

                <?php if(isset($_GET['error']) && $_GET['error'] == '1'){ ?>
                    <div class="alert alert-danger">
                        Correo o contraseña incorrectos.
                    </div>
                <?php } ?>

                <form method="POST" action="../../controllers/login.php">

                    <div class="form-block">
                        <label>Correo electrónico</label>

                        <div class="input-line">
                            <i class="fa-solid fa-envelope"></i>

                            <input
                            type="email"
                            name="correo"
                            placeholder="admin@expitrack.com"
                            required>
                        </div>
                    </div>

                    <div class="form-block">
                        <label>Contraseña</label>

                        <div class="input-line">
                            <i class="fa-solid fa-lock"></i>

                            <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="********"
                            required>

                            <button type="button" class="eye-btn" onclick="mostrarPassword()">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-options">

                        <label class="remember">
                            <input type="checkbox">
                            Recordarme
                        </label>

                        <a href="forgot.php">
                            ¿Olvidaste tu contraseña?
                        </a>

                    </div>

                    <button type="submit" class="login-btn">
                        Ingresar
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>

                </form>

                <div class="quick-info">
                    <small>Usuario de prueba</small>
                    <strong>admin@expitrack.com</strong>
                </div>

                <div class="footer">
                    ExpiTrack © <?= date('Y'); ?>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
function mostrarPassword(){
    let password = document.getElementById("password");
    let eyeIcon = document.getElementById("eyeIcon");

    if(password.type === "password"){
        password.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    }else{
        password.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>