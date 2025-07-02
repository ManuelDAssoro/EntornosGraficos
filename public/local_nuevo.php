<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$stmt = $pdo->query("SELECT codUsuario, nombreUsuario FROM usuarios WHERE tipoUsuario = 'dueno' ORDER BY nombreUsuario");
$duenos = $stmt->fetchAll();

$errores = [];
$nombreLocal = '';
$ubicacionLocal = '';
$rubroLocal = '';
$codUsuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreLocal = trim($_POST['nombreLocal'] ?? '');
    $ubicacionLocal = trim($_POST['ubicacionLocal'] ?? '');
    $rubroLocal = trim($_POST['rubroLocal'] ?? '');
    $codUsuario = trim($_POST['codUsuario'] ?? '');

    if (empty($nombreLocal)) {
        $errores[] = "El nombre del local es obligatorio.";
    }

    if (!empty($codUsuario) && !ctype_digit($codUsuario)) {
        $errores[] = "Selecciona un dueño válido.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare(
            "INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario)
             VALUES (?, ?, ?, ?)"
        );
        $exito = $stmt->execute([
            $nombreLocal,
            $ubicacionLocal ?: null,
            $rubroLocal ?: null,
            $codUsuario ?: null
        ]);

        if ($exito) {
            header("Location: admin_locales.php?mensaje=creado");
            exit;
        } else {
            $errores[] = "Ocurrió un error al guardar el local.";
        }
    }
}

$page_title = 'Nuevo Local - Mi Shopping';
$custom_css = 'local-nuevo.css';
include 'layout/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Nuevo Local
                </h1>
                <p class="mb-0 mt-2">Agrega un nuevo local al shopping</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="admin_locales.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>

    <div class="container">
        <!-- Error Messages -->
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

        <!-- Form Card -->
        <div class="form-card">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="text-success mb-3">
                        <i class="fas fa-info-circle"></i> Información del Local
                    </h4>
                    <p class="text-muted">Complete los datos del nuevo local. Los campos marcados con * son obligatorios.</p>
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
                                <option value="<?= $dueno['codUsuario'] ?>" <?= $codUsuario == $dueno['codUsuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dueno['nombreUsuario']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Puedes asignar un dueño ahora o hacerlo más tarde
                    </div>
                </div>
                
                <div class="col-12">
                    <hr class="my-4">
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="admin_locales.php" class="btn btn-outline-secondary btn-action">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-action">
                            <i class="bi bi-save"></i> Guardar Local
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php include 'layout/footer.php'; ?>
