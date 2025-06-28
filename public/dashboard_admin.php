<?php
session_start();
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

// Obtener estadísticas del dashboard
$stats = [];

// Total de locales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM locales");
$stats['total_locales'] = $stmt->fetchColumn();

// Locales con dueño asignado
$stmt = $pdo->query("SELECT COUNT(*) as total FROM locales WHERE codUsuario IS NOT NULL");
$stats['locales_asignados'] = $stmt->fetchColumn();

// Total de usuarios dueños
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipoUsuario = 'Dueño'");
$stats['total_duenos'] = $stmt->fetchColumn();

// Total de usuarios clientes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipoUsuario = 'Cliente'");
$stats['total_clientes'] = $stmt->fetchColumn();

// Locales recientes (últimos 5)
$stmt = $pdo->query("
    SELECT l.nombreLocal, l.rubroLocal, u.nombreUsuario, l.codLocal
    FROM locales l
    LEFT JOIN usuarios u ON l.codUsuario = u.codUsuario
    ORDER BY l.codLocal DESC
    LIMIT 5
");
$locales_recientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .stats-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .action-btn {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            font-weight: 600;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            box-shadow: 0 0 0 1px #000;
            text-decoration: none;
        }
        .welcome-section {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard_admin.php">
                <i class="bi bi-shop"></i> Mi Shopping
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link active" href="dashboard_admin.php">Dashboard</a>
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
            <div class="welcome-section">
                <h1 class="mb-3">
                    <i class="bi bi-speedometer2"></i> Dashboard Administrativo
                </h1>
                <p class="lead mb-0">Panel de control y gestión del shopping</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="stats-number text-primary"><?= $stats['total_locales'] ?></div>
                    <h6 class="text-muted">Total Locales</h6>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-success">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stats-number text-success"><?= $stats['locales_asignados'] ?></div>
                    <h6 class="text-muted">Locales Asignados</h6>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon text-warning">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stats-number text-warning"><?= $stats['total_duenos'] ?></div>
                    <h6 class="text-muted">Dueños Registrados</h6>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
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
            <!-- Quick Actions -->
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
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
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
                                            <h6 class="mb-1"><?= htmlspecialchars($local['nombreLocal']) ?></h6>
                                            <small class="text-muted">
                                                <?= $local['rubroLocal'] ? htmlspecialchars($local['rubroLocal']) : 'Sin rubro' ?>
                                                <?php if ($local['nombreUsuario']): ?>
                                                    • Dueño: <?= htmlspecialchars($local['nombreUsuario']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="local_editar.php?id=<?= $local['codLocal'] ?>" 
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

    <!-- Footer -->
    <footer class="bg-dark text-light text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Mi Shopping. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
