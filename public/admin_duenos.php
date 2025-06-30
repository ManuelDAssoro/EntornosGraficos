<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

// Set page variables for header
$page_title = 'Gestionar Dueños - Mi Shopping';
$custom_css = 'admin-duenos.css';

$email = trim($_GET['email'] ?? '');

$where = ["tipoUsuario = 'dueno'", "estado = 'pendiente'"];
$params = [];

if ($email !== '') {
    $where[] = "nombreUsuario LIKE ?";
    $params[] = "%$email%";
}

$sql = "SELECT * FROM usuarios WHERE " . implode(" AND ", $where) . " ORDER BY codUsuario DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$duenos = $stmt->fetchAll();

// Messages for success notifications
$mensaje = $_GET['mensaje'] ?? '';
$mensajes_exito = [
    'aprobado' => 'El dueño fue aprobado y notificado por email.',
    'rechazado' => 'El dueño fue rechazado y notificado por email.'
];

// Include header
include 'layout/header.php';
?>

<!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="bi bi-person-lines-fill"></i> Solicitudes de Dueños
                    </h1>
                    <p class="mb-0 mt-2">Gestiona las cuentas de dueños pendientes de aprobación</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="stats-card">
                        <h3 class="text-primary mb-0"><?= count($duenos) ?></h3>
                        <small class="text-muted">Solicitudes Pendientes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <!-- Success Messages -->
    <?php if (isset($mensajes_exito[$mensaje])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $mensajes_exito[$mensaje] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="filter-card">
        <h5 class="mb-3">
            <i class="bi bi-funnel"></i> Filtros de Búsqueda
        </h5>

        <form method="get" class="row g-3">
            <div class="col-md-6">
                <label for="email" class="form-label">Email del Dueño</label>
                <input type="text" class="form-control" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                <a href="admin_duenos.php" class="btn btn-secondary"><i class="bi bi-x"></i> Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($duenos) > 0): ?>
                        <?php foreach ($duenos as $dueno): ?>
                            <tr>
                                <td><?= htmlspecialchars($dueno['nombreUsuario']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= $dueno['estado'] ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="aprobar_dueno.php?id=<?= $dueno['codUsuario'] ?>" class="btn btn-success btn-sm btn-action">
                                            <i class="bi bi-check-circle"></i> Aprobar
                                        </a>
                                        <a href="rechazar_dueno.php?id=<?= $dueno['codUsuario'] ?>" class="btn btn-danger btn-sm btn-action">
                                            <i class="bi bi-x-circle"></i> Rechazar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-1"></i>
                                <h5 class="mt-3">No hay solicitudes pendientes</h5>
                                <p>Todos los dueños han sido procesados.</p>
                            </td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
