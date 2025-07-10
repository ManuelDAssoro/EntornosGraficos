<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$mensaje = '';
$error = '';

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
            $stmt = $pdo->prepare("INSERT INTO novedades (titulo, contenido, categoria_minima, fecha_publicacion, estado, codUsuario) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $contenido, $categoria_minima, $fecha_publicacion, $estado, $_SESSION['usuario_id']]);
            $mensaje = 'Novedad creada exitosamente.';
            header("Location: admin_novedades.php?mensaje=" . urlencode($mensaje));
            exit;
        } catch (PDOException $e) {
            $error = 'Error al crear la novedad: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("
    SELECT n.*, u.nombreUsuario 
    FROM novedades n 
    LEFT JOIN usuarios u ON n.codUsuario = u.codUsuario 
    ORDER BY n.fecha_creacion DESC
");
$stmt->execute();
$novedades = $stmt->fetchAll();

$page_title = 'Administrar Novedades - Mi Shopping';
include 'layout/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <h1><i class="bi bi-newspaper"></i> Administrar Novedades</h1>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Novedad</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255"
                            value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido *</label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria_minima" class="form-label">Categoría Mínima</label>
                                    <select class="form-select" id="categoria_minima" name="categoria_minima">
                                        <option value="unlogged">Público (Sin registro)</option>
                                        <option value="inicial">Inicial</option>
                                        <option value="medium">Medium</option>
                                        <option value="premium">Premium</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                                    <input type="date" class="form-control" id="fecha_publicacion" name="fecha_publicacion"
                                    value="<?= htmlspecialchars($_POST['fecha_publicacion'] ?? date('Y-m-d')) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="activa">Activa</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Crear Novedad
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Novedades Existentes</h5>
                </div>
                <div class="card-body">
                    <?php if (count($novedades) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoría</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Autor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($novedades as $novedad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($novedad['titulo'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $novedad['categoria_minima'] === 'premium' ? 'success' : ($novedad['categoria_minima'] === 'medium' ? 'warning' : ($novedad['categoria_minima'] === 'inicial' ? 'secondary' : 'info')) ?>">
                                                    <?= ucfirst($novedad['categoria_minima']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $novedad['estado'] === 'activa' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($novedad['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($novedad['fecha_publicacion'])) ?></td>
                                            <td><?= htmlspecialchars($novedad['nombreusuario'] ?? 'Sistema') ?></td>
                                            <td>
                                                <a href="edit_novedad.php?id=<?= $novedad['id'] ?>" class="btn btn-sm btn-outline-primary"
                                                onclick="editarNovedad(<?= $novedad['id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete_novedad.php?id=<?= $novedad['id'] ?>" class="btn btn-sm btn-outline-danger"
                                                onclick="eliminarNovedad(<?= $novedad['id'] ?>)" onclick="return confirm('¿Está seguro?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">No hay novedades creadas aún.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información sobre Categorías</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Jerarquía de acceso:</strong>
                        <ul class="list-unstyled mt-2">
                            <li><span class="badge bg-info">Público</span> - Sin registro</li>
                            <li><span class="badge bg-secondary">Inicial</span> - Usuarios registrados</li>
                            <li><span class="badge bg-warning">Medium</span> - 3+ promociones usadas</li>
                            <li><span class="badge bg-success">Premium</span> - 10+ promociones usadas</li>
                        </ul>
                    </div>
                    <p class="text-muted small">
                        Los usuarios de categoría superior pueden ver novedades de categorías inferiores.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
