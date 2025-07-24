<?php
session_start();
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$page_title = 'Menu Admin - Mi Shopping';
$custom_css = 'dashboard-admin.css';

$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM locales");
$stats['total_locales'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM locales WHERE codUsuario IS NOT NULL");
$stats['locales_asignados'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipoUsuario = 'dueno'");
$stats['total_duenos'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipoUsuario = 'cliente'");
$stats['total_clientes'] = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT l.nombreLocal, l.rubroLocal, u.nombreUsuario, l.codLocal
    FROM locales l
    LEFT JOIN usuarios u ON l.codUsuario = u.codUsuario
    ORDER BY l.codLocal DESC
    LIMIT 5
");
$locales_recientes = $stmt->fetchAll();

include 'layout/header.php';
?>
    <!-- Page Header -->
<div class="page-header">
        <div class="container">
            <div class="welcome-section">
                <h1 class="mb-3">
                    <i class="bi bi-speedometer2"></i> Menu Administrativo
                </h1>
                <p class="lead mb-0">Panel de control y gestión del shopping</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="stats-number text-primary"><?= $stats['total_locales'] ?></div>
                    <h6 class="text-muted">Total Locales</h6>
                </div>
            </div>
            <!-- <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-success">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stats-number text-success"><?= $stats['locales_asignados'] ?></div>
                    <h6 class="text-muted">Locales Asignados</h6>
                </div>
            </div> -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-warning">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stats-number text-warning"><?= $stats['total_duenos'] ?></div>
                    <h6 class="text-muted">Dueños Registrados</h6>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-info">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-number text-info"><?= $stats['total_clientes'] ?></div>
                    <h6 class="text-muted">Clientes Registrados</h6>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="quick-actions">
                    <h4 class="mb-4">
                        <i class="bi bi-lightning text-warning"></i> Acciones Rápidas
                    </h4>
                    <div class="row g-3">
                        <div class="col-12">
                            <a href="local_nuevo.php" class="action-btn btn btn-success w-100">
                                <i class="bi bi-plus-circle me-2"></i>
                                Crear Nuevo Local
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="admin_locales.php" class="action-btn btn btn-primary w-100">
                                <i class="bi bi-list me-2"></i>
                                Gestionar Locales
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="admin_locales.php?ubicacion=" class="action-btn btn btn-info w-100">
                                <i class="bi bi-search me-2"></i>
                                Buscar Locales
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="admin_novedades.php" class="action-btn btn btn-warning w-100">
                                <i class="bi bi-newspaper me-2"></i>
                                Gestionar Novedades
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="recent-activity">
                    <h4 class="mb-4">
                        <i class="bi bi-clock text-info"></i> Actividad Reciente
                    </h4>
                    <?php if (count($locales_recientes) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($locales_recientes as $local): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-shop text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($local['nombrelocal'] ?? '') ?></h6>
                                            <small class="text-muted">
                                                <?= $local['rubrolocal'] ? htmlspecialchars($local['rubrolocal']) : 'Sin rubro' ?>
                                                <?php if ($local['nombreusuario']): ?>
                                                    • Dueño: <?= htmlspecialchars($local['nombreusuario']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="local_editar.php?id=<?= $local['codlocal'] ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="admin_locales.php" class="btn btn-outline-primary">
                                Ver Todos los Locales
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-1 mb-3"></i>
                            <h5>No hay locales registrados</h5>
                            <p>Comienza creando tu primer local</p>
                            <a href="local_nuevo.php" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Crear Primer Local
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include 'layout/footer.php'; ?>
