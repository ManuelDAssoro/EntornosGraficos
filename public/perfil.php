<?php
session_start();
require_once '../config/db.php';
$page_title = 'Mi Perfil';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$tipoAlerta = '';

// Obtener nombre de usuario
try {
    $stmt = $pdo->prepare("SELECT nombreUsuario, claveUsuario FROM usuarios WHERE codUsuario = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    $nombreUsuario = $usuario['nombreusuario'] ?? 'Usuario';
    $claveActualHasheada = $usuario['claveusuario'] ?? '';
} catch (Exception $e) {
    $mensaje = 'Error al cargar datos del perfil.';
    $tipoAlerta = 'danger';
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave_actual = $_POST['clave_actual'] ?? '';
    $clave_nueva = $_POST['clave_nueva'] ?? '';
    $clave_confirmar = $_POST['clave_confirmar'] ?? '';

    if (!password_verify($clave_actual, $claveActualHasheada)) {
        $mensaje = 'La contraseña actual es incorrecta.';
        $tipoAlerta = 'danger';
    } elseif (strlen($clave_nueva) < 6) {
        $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres.';
        $tipoAlerta = 'warning';
    } elseif ($clave_nueva !== $clave_confirmar) {
        $mensaje = 'Las nuevas contraseñas no coinciden.';
        $tipoAlerta = 'warning';
    } else {
        try {
            $nuevaClaveHasheada = password_hash($clave_nueva, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET claveUsuario = ? WHERE codUsuario = ?");
            $stmt->execute([$nuevaClaveHasheada, $_SESSION['usuario_id']]);

            $mensaje = 'Contraseña actualizada exitosamente.';
            $tipoAlerta = 'success';
        } catch (Exception $e) {
            $mensaje = 'Error al actualizar la contraseña.';
            $tipoAlerta = 'danger';
        }
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard_admin.php">
                <i class="fas fa-store"></i> Mi Shopping
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="dashboard_admin.php">Menu</a>
                    <a class="nav-link" href="admin_locales.php">Locales</a>
                </div>
                <div class="d-flex">
                    <?php include 'layout/header.php'; ?>
                </div>
            </div>
        </div>
    </nav>

<div class="container mt-5">
    <h3>Mi Perfil</h3>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoAlerta ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Información de Usuario</h5>
            <p><strong>Nombre de Usuario:</strong> <?= htmlspecialchars($nombreUsuario) ?></p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Cambiar Contraseña</h5>
            <form method="post">
                <div class="mb-3">
                    <label for="clave_actual" class="form-label">Contraseña Actual</label>
                    <input type="password" class="form-control" id="clave_actual" name="clave_actual" required>
                </div>
                <div class="mb-3">
                    <label for="clave_nueva" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="clave_nueva" name="clave_nueva" required>
                </div>
                <div class="mb-3">
                    <label for="clave_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="clave_confirmar" name="clave_confirmar" required>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Mi Shopping. Todos los derechos reservados.</p>
        </div>
    </footer>
