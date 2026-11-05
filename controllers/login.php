<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        header("Location: ../views/auth/login.php?error=1");
        exit();
    }

    try {
        // Consultar el usuario en la base de datos
        $stmt = $pdo->prepare("
            SELECT u.*, r.nombre AS rol_nombre, s.nombre AS sucursal_nombre 
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            INNER JOIN sucursales s ON u.id_sucursal = s.id_sucursal
            WHERE u.correo = :correo AND u.estado = 1
            LIMIT 1
        ");
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch();

        // Validar credenciales (acepta Admin123 o 123456)
        if ($usuario && (password_verify($password, $usuario['password']) || $password === 'Admin123' || $password === '123456')) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario_sucursal'] = $usuario['sucursal_nombre'];
            $_SESSION['id_sucursal'] = $usuario['id_sucursal'];

            // Redirigir al Dashboard principal
            header("Location: ../views/dashboard/index.php");
            exit();
        } else {
            header("Location: ../views/auth/login.php?error=1");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../views/auth/login.php?error=1");
        exit();
    }
} else {
    header("Location: ../views/auth/login.php");
    exit();
}
?>