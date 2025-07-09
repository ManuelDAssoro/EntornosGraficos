<?php
session_start();
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$codLocal = $_GET['id'] ?? null;

if (!$codLocal || !ctype_digit($codLocal)) {
    die("ID de local inválido.");
}

// Obtener datos para mostrar nombre en confirmación
$stmt = $pdo->prepare("SELECT nombreLocal FROM locales WHERE codLocal = ?");
$stmt->execute([$codLocal]);
$local = $stmt->fetch();

if (!$local) {
    die("Local no encontrado.");
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Confirmación para eliminar
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'sí') {
        $stmt = $pdo->prepare("DELETE FROM locales WHERE codLocal = ?");
        $resultado = $stmt->execute([$codLocal]);

        if ($resultado) {
            header("Location: admin_locales.php?mensaje=eliminado");
            exit;
        } else {
            $error = "No se pudo eliminar el local.";
        }
    } else {
        // Si no confirma, redirige al listado
        header("Location: admin_locales.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Local - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/local-eliminar.css">
</head>
<body class="bg-light">
    <!-- Navigation -->
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
                    <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
                    <a class="nav-link" href="admin_locales.php">Locales</a>
                </div>
                <div class="d-flex">
                    <?php include 'layout/header.php'; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-trash-alt"></i> Eliminar Local
                    </h1>
                    <p class="mb-0 mt-2">Confirma la eliminación del local</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="admin_locales.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Error Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Confirmation Card -->
        <div class="confirmation-card">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h3 class="danger-text mb-4">¡Acción Irreversible!</h3>
            
            <p class="lead">¿Estás seguro que quieres eliminar el siguiente local?</p>
            
            <div class="local-name">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="fas fa-store text-danger me-3 fa-2x"></i>
                    <div>
                        <h4 class="mb-0 danger-text"><?= htmlspecialchars($local['nombrelocal']) ?></h4>
                        <small class="text-muted">ID: #<?= $codLocal ?></small>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning mt-4" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. El local será eliminado permanentemente de la base de datos.
            </div>

            <form method="POST" class="mt-4">
                <div class="d-flex justify-content-center gap-3">
                    <button type="submit" name="confirm" value="no" class="btn btn-outline-secondary btn-action">
                        <i class="fas fa-times"></i> No, Cancelar
                    </button>
                    <button type="submit" name="confirm" value="sí" class="btn btn-danger btn-action" 
                            onclick="return confirm('¿Estás completamente seguro? Esta acción no se puede deshacer.')">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                </div>
            </form>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> 
                    Siempre puedes volver al listado sin realizar cambios
                </small>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Mi Shopping. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>
