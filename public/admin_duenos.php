<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$page_title = 'Gestionar Dueños - Mi Shopping';
$custom_css = 'admin-duenos.css';

$email = trim($_GET['email'] ?? '');
$seccion = $_GET['seccion'] ?? 'solicitudes'; // 'solicitudes' or 'gestion'
$estado = $_GET['estado'] ?? 'todos';
$activo = $_GET['activo'] ?? 'todos';

$where = ["tipoUsuario = 'dueno'"];
$params = [];

if ($seccion === 'solicitudes') {
    $where[] = "estado = 'pendiente'";
} else {
    $where[] = "estado != 'pendiente'";
}

if ($email !== '') {
    $where[] = "nombreUsuario ILIKE ?";
    $params[] = "%$email%";
}

if ($seccion === 'gestion') {
    if ($estado !== 'todos') {
        $where[] = "estado = ?";
        $params[] = $estado;
    }

    if ($activo !== 'todos') {
        if ($activo === 'activo') {
            $where[] = "estado = 'aprobado'";
        } else {
            $where[] = "estado = 'rechazado'";
        }
    }
}

$sql = "SELECT u.*, l.nombreLocal, l.codLocal 
        FROM usuarios u 
        LEFT JOIN locales l ON u.codUsuario = l.codUsuario 
        WHERE " . implode(" AND ", $where) . " 
        ORDER BY u.estado DESC, u.codUsuario DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$duenos = $stmt->fetchAll();

try {
    $sql_stats = "SELECT 
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
        SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
        COUNT(*) as activos
        FROM usuarios WHERE tipoUsuario = 'dueno'";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch();
} catch (Exception $e) {
    $stats = ['pendientes' => 0, 'aprobados' => 0, 'rechazados' => 0, 'activos' => 0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['userId'] ?? '';
    
    if ($action && $userId) {
        try {
            switch ($action) {
                case 'aprobar':
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'aprobado' WHERE codUsuario = ?");
                    $stmt->execute([$userId]);
                    header("Location: admin_duenos.php?seccion=$seccion&mensaje=aprobado");
                    exit;
                    
                case 'rechazar':
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'rechazado' WHERE codUsuario = ?");
                    $stmt->execute([$userId]);
                    header("Location: admin_duenos.php?seccion=$seccion&mensaje=rechazado");
                    exit;
                    
                case 'activar':
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'aprobado' WHERE codUsuario = ?");
                    $stmt->execute([$userId]);
                    header("Location: admin_duenos.php?seccion=$seccion&mensaje=activado");
                    exit;
                    
                case 'desactivar':
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'rechazado' WHERE codUsuario = ?");
                    $stmt->execute([$userId]);
                    header("Location: admin_duenos.php?seccion=$seccion&mensaje=desactivado");
                    exit;
                    
                case 'eliminar':
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'rechazado' WHERE codUsuario = ?");
                    $stmt->execute([$userId]);
                    header("Location: admin_duenos.php?seccion=$seccion&mensaje=eliminado");
                    exit;
            }
        } catch (Exception $e) {
            $error = "Error al procesar la acción: " . $e->getMessage();
        }
    }
}

$mensaje = $_GET['mensaje'] ?? '';
$mensajes_exito = [
    'aprobado' => 'El dueño fue aprobado exitosamente.',
    'rechazado' => 'El dueño fue rechazado.',
    'activado' => 'El dueño fue reactivado (aprobado).',
    'desactivado' => 'El dueño fue desactivado (rechazado).',
    'eliminado' => 'El dueño fue eliminado (rechazado).'
];

$secciones = [
    'solicitudes' => [
        'titulo' => 'Solicitudes de Dueños',
        'descripcion' => 'Gestiona las solicitudes pendientes de aprobación',
        'icono' => 'bi bi-clock-history',
        'color' => 'warning'
    ],
    'gestion' => [
        'titulo' => 'Gestión de Dueños',
        'descripcion' => 'Administra todos los dueños aprobados y rechazados',
        'icono' => 'bi bi-people-fill',
        'color' => 'primary'
    ]
];

include 'layout/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">
                    <i class="<?= $secciones[$seccion]['icono'] ?>"></i> 
                    <?= $secciones[$seccion]['titulo'] ?>
                </h1>
                <p class="mb-0 mt-2"><?= $secciones[$seccion]['descripcion'] ?></p>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-6">
                        <div class="stats-card">
                            <h3 class="text-<?= $seccion === 'solicitudes' ? 'warning' : 'primary' ?> mb-0">
                                <?= $seccion === 'solicitudes' ? $stats['pendientes'] : count($duenos) ?>
                            </h3>
                            <small class="text-muted">
                                <?= $seccion === 'solicitudes' ? 'Pendientes' : 'Total' ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="section-selector">
                            <label for="seccion-select" class="form-label text-white">Sección:</label>
                            <select id="seccion-select" class="form-select form-select-sm" onchange="cambiarSeccion(this.value)">
                                <option value="solicitudes" <?= $seccion === 'solicitudes' ? 'selected' : '' ?>>
                                    Solicitudes
                                </option>
                                <option value="gestion" <?= $seccion === 'gestion' ? 'selected' : '' ?>>
                                    Gestión
                                </option>
                            </select>
                        </div>
                    </div>
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
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="overview-card bg-warning">
                <div class="overview-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="overview-content">
                    <h4><?= $stats['pendientes'] ?></h4>
                    <p>Solicitudes Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="overview-card bg-success">
                <div class="overview-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="overview-content">
                    <h4><?= $stats['aprobados'] ?></h4>
                    <p>Dueños Aprobados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="overview-card bg-danger">
                <div class="overview-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="overview-content">
                    <h4><?= $stats['rechazados'] ?></h4>
                    <p>Dueños Rechazados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="overview-card bg-info">
                <div class="overview-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="overview-content">
                    <h4><?= $stats['activos'] ?></h4>
                    <p>Usuarios Activos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <h5 class="mb-3">
            <i class="bi bi-funnel"></i> Filtros de Búsqueda
        </h5>

        <form method="get" class="row g-3">
            <input type="hidden" name="seccion" value="<?= $seccion ?>">
            
            <div class="col-md-4">
                <label for="email" class="form-label">Email del Dueño</label>
                <input type="text" class="form-control" name="email" id="email" 
                       value="<?= htmlspecialchars($email) ?>" placeholder="Buscar por email...">
            </div>
            
            <?php if ($seccion === 'gestion'): ?>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado de Solicitud</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos los estados</option>
                        <option value="aprobado" <?= $estado === 'aprobado' ? 'selected' : '' ?>>Aprobados</option>
                        <option value="rechazado" <?= $estado === 'rechazado' ? 'selected' : '' ?>>Rechazados</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="activo" class="form-label">Filtrar por Estado</label>
                    <select name="activo" id="activo" class="form-select">
                        <option value="todos" <?= $activo === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="activo" <?= $activo === 'activo' ? 'selected' : '' ?>>Solo Aprobados</option>
                        <option value="inactivo" <?= $activo === 'inactivo' ? 'selected' : '' ?>>Solo Rechazados</option>
                    </select>
                </div>
            <?php else: ?>
                <div class="col-md-6"></div>
            <?php endif; ?>
            
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
        
        <div class="mt-3">
            <a href="admin_duenos.php?seccion=<?= $seccion ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-x"></i> Limpiar Filtros
            </a>
        </div>
    </div>

    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="<?= $secciones[$seccion]['icono'] ?>"></i>
                Lista de <?= $seccion === 'solicitudes' ? 'Solicitudes' : 'Dueños' ?>
            </h5>
            <div class="text-muted">
                Total: <strong><?= count($duenos) ?></strong> 
                <?= $seccion === 'solicitudes' ? 'solicitudes' : 'dueños' ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Local Asignado</th>
                        <th>Estado Usuario</th>
                        <th>ID Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($duenos) > 0): ?>
                        <?php foreach ($duenos as $dueno): ?>
                            <tr class="<?= $dueno['estado'] === 'rechazado' ? 'table-secondary' : '' ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2 text-muted"></i>
                                        <?= htmlspecialchars($dueno['nombreUsuario']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($dueno['estado']) {
                                        'pendiente' => 'bg-warning text-dark',
                                        'aprobado' => 'bg-success',
                                        'rechazado' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= ucfirst($dueno['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($dueno['nombreLocal']): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-shop me-2 text-primary"></i>
                                            <?= htmlspecialchars($dueno['nombreLocal']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-dash-circle me-1"></i>
                                            Sin local asignado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($dueno['estado'] === 'aprobado'): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-hash me-1"></i>
                                        <?= $dueno['codUsuario'] ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($seccion === 'solicitudes' && $dueno['estado'] === 'pendiente'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="aprobar">
                                                <input type="hidden" name="userId" value="<?= $dueno['codUsuario'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        title="Aprobar Solicitud"
                                                        onclick="return confirm('¿Aprobar esta solicitud de dueño?')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="rechazar">
                                                <input type="hidden" name="userId" value="<?= $dueno['codUsuario'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        title="Rechazar Solicitud"
                                                        onclick="return confirm('¿Rechazar esta solicitud de dueño?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($seccion === 'gestion'): ?>
                                            <?php if ($dueno['estado'] === 'aprobado'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="desactivar">
                                                    <input type="hidden" name="userId" value="<?= $dueno['codUsuario'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                            title="Desactivar Usuario (Rechazar)"
                                                            onclick="return confirm('¿Desactivar este usuario? (Cambiar a Rechazado)')">
                                                        <i class="bi bi-pause-circle"></i>
                                                    </button>
                                                </form>
                                            <?php elseif ($dueno['estado'] === 'rechazado'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="activar">
                                                    <input type="hidden" name="userId" value="<?= $dueno['codUsuario'] ?>">
                                                    <button type="submit" class="btn btn-info btn-sm" 
                                                            title="Reactivar Usuario (Aprobar)">
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-1"></i>
                                <h5 class="mt-3">
                                    No se encontraron <?= $seccion === 'solicitudes' ? 'solicitudes' : 'dueños' ?>
                                </h5>
                                <p>
                                    <?php if ($seccion === 'solicitudes'): ?>
                                        No hay solicitudes pendientes de aprobación.
                                    <?php else: ?>
                                        No hay dueños que coincidan con los filtros aplicados.
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cambiarSeccion(seccion) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('seccion', seccion);
    if (seccion === 'solicitudes') {
        urlParams.delete('estado');
        urlParams.delete('activo');
    }
    window.location.href = '?' + urlParams.toString();
}
</script>

<?php include 'layout/footer.php'; ?>
