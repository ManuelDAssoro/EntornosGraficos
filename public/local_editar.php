<?php
session_start();
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$codLocal = $_GET['id'] ?? null;

if (!$codLocal || !ctype_digit($codLocal)) {
    die("ID de local inválido.");
}

// Obtener datos actuales del local
$stmt = $pdo->prepare("SELECT * FROM locales WHERE codLocal = ?");
$stmt->execute([$codLocal]);
$local = $stmt->fetch();

if (!$local) {
    die("Local no encontrado.");
}

// Traer usuarios tipo "Dueño"
$duenosStmt = $pdo->query("SELECT codUsuario, nombreUsuario FROM usuarios WHERE tipoUsuario = 'dueno' ORDER BY nombreUsuario");
$duenos = $duenosStmt->fetchAll();

// Inicializar variables con datos actuales
$nombreLocal = $local['nombrelocal'];
$ubicacionLocal = $local['ubicacionlocal'];
$rubroLocal = $local['rubrolocal'];
$codUsuario = $local['codusuario'];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del form
    $nombreLocal = trim($_POST['nombrelocal'] ?? '');
    $ubicacionLocal = trim($_POST['ubicacionLocal'] ?? '');
    $rubroLocal = trim($_POST['rubroLocal'] ?? '');
    $codUsuario = trim($_POST['codUsuario'] ?? '');

    // Validar
    if (empty($nombreLocal)) {
        $errores[] = "El nombre del local es obligatorio.";
    }

    if (!empty($codUsuario) && !ctype_digit($codUsuario)) {
        $errores[] = "Selecciona un dueño válido.";
    }

    // Si todo ok → actualizar
    if (empty($errores)) {
        $stmt = $pdo->prepare("
            UPDATE locales 
            SET nombreLocal = ?, ubicacionLocal = ?, rubroLocal = ?, codUsuario = ?
            WHERE codLocal = ?
        ");

        $resultado = $stmt->execute([
            $nombreLocal,
            $ubicacionLocal ?: null,
            $rubroLocal ?: null,
            $codUsuario ?: null,
            $codLocal
        ]);

        if ($resultado) {
            header("Location: admin_locales.php?mensaje=editado");
            exit;
        } else {
            $errores[] = "No se pudo actualizar el local.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Local - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/local-editar.css">
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
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-edit"></i> Editar Local
                    </h1>
                    <p class="mb-0 mt-2">Modifica la información del local</p>
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
        <div class="local-info">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle text-warning fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">Editando: <?= htmlspecialchars($local['nombrelocal']) ?></h5>
                    <small class="text-muted">ID del Local: #<?= $codLocal ?></small>
                </div>
            </div>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="text-warning mb-3">
                        <i class="fas fa-pencil-alt"></i> Información del Local
                    </h4>
                    <p class="text-muted">Modifica los datos del local. Los campos marcados con * son obligatorios.</p>
                </div>
            </div>

            <form method="POST" class="row g-4">
                <div class="col-md-6">
                    <label for="nombreLocal" class="form-label required-field">Nombre del Local</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-store"></i></span>
                        <input type="text" class="form-control" id="nombreLocal" name="nombreLocal" 
                               value="<?= htmlspecialchars($nombreLocal) ?>" required
                               placeholder="Ej: Tienda de Ropa Moderna">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="ubicacionLocal" class="form-label">Ubicación</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control" id="ubicacionLocal" name="ubicacionLocal" 
                               value="<?= htmlspecialchars($ubicacionLocal) ?>"
                               placeholder="Ej: Planta Baja - Local 15">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="rubroLocal" class="form-label">Rubro</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tags"></i></span>
                        <input type="text" class="form-control" id="rubroLocal" name="rubroLocal" 
                               value="<?= htmlspecialchars($rubroLocal) ?>"
                               placeholder="Ej: Indumentaria, Tecnología, Gastronomía">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="codUsuario" class="form-label">Dueño del Local</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <select name="codUsuario" id="codUsuario" class="form-select">
                            <option value="">-- Sin asignar --</option>
                            <?php foreach ($duenos as $dueno): ?>
                                <option value="<?= $dueno['codusuario'] ?>" <?= $codUsuario == $dueno['codusuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dueno['nombreusuario']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> Puedes cambiar el dueño o dejarlo sin asignar
                    </div>
                </div>
                
                <div class="col-12">
                    <hr class="my-4">
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="admin_locales.php" class="btn btn-outline-secondary btn-action">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-action">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
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
