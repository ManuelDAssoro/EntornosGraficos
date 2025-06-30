<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$page_title = 'Gestionar Locales - Mi Shopping';
$custom_css = 'admin-locales.css';

$nombre = trim($_GET['nombre'] ?? '');
$ubicacion = trim($_GET['ubicacion'] ?? '');
$rubro = trim($_GET['rubro'] ?? '');

$where = [];
$params = [];

if ($nombre !== '') {
    $where[] = "l.nombreLocal LIKE ?";
    $params[] = "%$nombre%";
}
if ($ubicacion !== '') {
    $where[] = "l.ubicacionLocal LIKE ?";
    $params[] = "%$ubicacion%";
}
if ($rubro !== '') {
    $where[] = "l.rubroLocal LIKE ?";
    $params[] = "%$rubro%";
}

$sql = "
    SELECT l.*, u.nombreUsuario
    FROM locales l
    LEFT JOIN usuarios u ON l.codUsuario = u.codUsuario
";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY l.nombreLocal ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$locales = $stmt->fetchAll();

$mensaje = $_GET['mensaje'] ?? '';
$mensajes_exito = [
    'creado' => 'El local fue creado correctamente.',
    'editado' => 'El local fue editado correctamente.',
    'eliminado' => 'El local fue eliminado correctamente.'
];

include 'layout/header.php';
?>

<!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="bi bi-building"></i> Administrar Locales
                    </h1>
                    <p class="mb-0 mt-2">Gestiona todos los locales del shopping</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="stats-card">
                        <h3 class="text-primary mb-0"><?= count($locales) ?></h3>
                        <small class="text-muted">Total Locales</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($mensajes_exito[$mensaje])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= $mensajes_exito[$mensaje] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="filter-card">
            <h5 class="mb-3">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>

            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre del Local</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shop"></i></span>
                        <input type="text" id="nombre" name="nombre" class="form-control" 
                               placeholder="Buscar por nombre..." value="<?= htmlspecialchars($nombre) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" 
                               placeholder="Buscar por ubicación..." value="<?= htmlspecialchars($ubicacion) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="rubro" class="form-label">Rubro</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tags"></i></span>
                        <input type="text" id="rubro" name="rubro" class="form-control" 
                               placeholder="Buscar por rubro..." value="<?= htmlspecialchars($rubro) ?>">
                    </div>
                </div>
                <div class="col-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="admin_locales.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Resultados de la búsqueda</h5>
            <a href="local_nuevo.php" class="btn btn-success btn-lg">
                <i class="bi bi-plus"></i> Nuevo Local
            </a>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark" >
                      <tr>
                        <th><i class="bi bi-shop"></i> Nombre</th>
                        <th><i class="bi bi-geo-alt"></i> Ubicación</th>
                        <th><i class="bi bi-tags"></i> Rubro</th>
                        <th><i class="bi bi-person"></i> Dueño</th>
                        <th style="width: 200px;" class="text-center"><i class="bi bi-gear"></i> Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                        <?php if (count($locales) > 0): ?>
                            <?php foreach ($locales as $local): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($local['nombreLocal']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= htmlspecialchars($local['ubicacionLocal']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($local['rubroLocal']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($local['nombreUsuario']): ?>
                                            <i class="bi bi-person-check text-success"></i> 
                                            <?= htmlspecialchars($local['nombreUsuario']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-person-x"></i> Sin asignar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="local_editar.php?id=<?= $local['codLocal'] ?>" 
                                               class="btn btn-sm btn-warning btn-action" 
                                               title="Editar local">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <a href="local_eliminar.php?id=<?= $local['codLocal'] ?>" 
                                               class="btn btn-sm btn-danger btn-action" 
                                               title="Eliminar local">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-search display-1 mb-3"></i>
                                        <h5>No se encontraron locales</h5>
                                        <p>Intenta modificar los filtros de búsqueda o crea un nuevo local.</p>
                                        <a href="local_nuevo.php" class="btn btn-primary">
                                            <i class="bi bi-plus"></i> Crear Primer Local
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'layout/footer.php'; ?>


