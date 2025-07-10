<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$id = $_GET['id'] ?? 0;
$mensaje = '';
$error = '';

if (!$id) {
    header("Location: admin_novedades.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM novedades WHERE id = ?");
$stmt->execute([$id]);
$novedad = $stmt->fetch();

if (!$novedad) {
    header("Location: admin_novedades.php?error=" . urlencode("Novedad no encontrada"));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria_minima = $_POST['categoria_minima'] ?? 'unlogged';
    $fecha_publicacion = $_POST['fecha_publicacion'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? 'activa';
    
    if (empty($titulo) || empty($contenido)) {
        $error = 'El título y contenido son obligatorios.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE novedades SET titulo = ?, contenido = ?, categoria_minima = ?, fecha_publicacion = ?, estado = ? WHERE id = ?");
            $stmt->execute([$titulo, $contenido, $categoria_minima, $fecha_publicacion, $estado, $id]);
            $mensaje = 'Novedad actualizada exitosamente.';
            header("Location: admin_novedades.php?mensaje=" . urlencode($mensaje));
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar la novedad: ' . $e->getMessage();
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST['titulo'] = $novedad['titulo'];
    $_POST['contenido'] = $novedad['contenido'];
    $_POST['categoria_minima'] = $novedad['categoria_minima'];
    $_POST['fecha_publicacion'] = $novedad['fecha_publicacion'];
    $_POST['estado'] = $novedad['estado'];
}

$page_title = 'Editar Novedad - Mi Shopping';
include 'layout/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil"></i> Editar Novedad
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                   value="<?= htmlspecialchars($novedad['titulo'] ?? '') ?>" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido *</label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="5" required><?= htmlspecialchars($novedad['contenido'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria_minima" class="form-label">Categoría Mínima</label>
                                    <select class="form-select" id="categoria_minima" name="categoria_minima">
                                        <option value="unlogged" <?= ($novedad['categoria_minima'] ?? '') === 'unlogged' ? 'selected' : '' ?>>Público (Sin registro)</option>
                                        <option value="inicial" <?= ($novedad['categoria_minima'] ?? '') === 'inicial' ? 'selected' : '' ?>>Inicial</option>
                                        <option value="medium" <?= ($novedad['categoria_minima'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="premium" <?= ($novedad['categoria_minima'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                                    <input type="date" class="form-control" id="fecha_publicacion" name="fecha_publicacion" 
                                           value="<?= htmlspecialchars($novedad['fecha_publicacion'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="activa" <?= ($novedad['estado'] ?? '') === 'activa' ? 'selected' : '' ?>>Activa</option>
                                <option value="inactiva" <?= ($novedad['estado'] ?? '') === 'inactiva' ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar Novedad
                            </button>
                            <a href="admin_novedades.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
