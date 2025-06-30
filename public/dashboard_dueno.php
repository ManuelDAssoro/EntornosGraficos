<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

$codUsuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT nombreUsuario, estado FROM usuarios WHERE codUsuario = ?");
$stmt->execute([$codUsuario]);
$usuario = $stmt->fetch();

$stmt = $pdo->prepare("SELECT l.*, u.nombreUsuario as dueno_nombre FROM locales l LEFT JOIN usuarios u ON l.codUsuario = u.codUsuario WHERE l.codUsuario = ?");
$stmt->execute([$codUsuario]);
$local = $stmt->fetch();

$hasLocal = $local ? true : false;
$promociones = [];

if ($hasLocal) {
    $codLocal = $local['codLocal'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM uso_promociones u 
                    WHERE u.codPromo = p.codPromo AND u.estado = 'usado') AS totalUsos
            FROM promociones p
            WHERE p.codLocal = ?
            ORDER BY p.fechaDesdePromo DESC
        ");
        $stmt->execute([$codLocal]);
        $promociones = $stmt->fetchAll();
    } catch (PDOException $e) {
        $promociones = [];
    }
}

$mensaje = $_GET['mensaje'] ?? '';
$mensajes = [
    'creada' => 'La promoción fue creada exitosamente.',
    'eliminada' => 'La promoción fue eliminada correctamente.'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $hasLocal ? 'Mi Local - Promociones' : 'Mi Panel - Dueño' ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard-dueno.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard_dueno.php">
            <i class="bi bi-shop"></i> Mi Shopping
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link active" href="dashboard_dueno.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <?php if ($hasLocal): ?>
                <a class="nav-link" href="promocion_nueva.php">
                    <i class="bi bi-plus-circle"></i> Nueva Promoción
                </a>
                <?php endif; ?>
            </div>
            <div class="navbar-nav">
                <span class="navbar-text">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nombreUsuario']) ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<?php if (!$hasLocal): ?>
<div class="container-fluid p-0">
    <div class="row min-vh-100 g-0">
        <div class="col-12">
            <div class="hero-section">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <div class="hero-content">
                                <div class="welcome-icon">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <h1 class="hero-title">¡Bienvenido a Mi Shopping!</h1>
                                <p class="hero-subtitle">Tu cuenta ha sido aprobada. Ahora estamos preparando todo para asignarte un local.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="steps-section">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="steps-header text-center mb-5">
                                <h2><i class="bi bi-list-check"></i> Proceso de Asignación de Local</h2>
                                <p class="text-muted">Sigue estos pasos para comenzar a gestionar tu local</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="step-card completed">
                                <div class="step-number">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="step-content">
                                    <h4>Cuenta Aprobada</h4>
                                    <p>Tu solicitud de dueño ha sido revisada y aprobada por nuestro equipo administrativo.</p>
                                    <div class="step-status">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check"></i> Completado
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="step-card in-progress">
                                <div class="step-number">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <div class="step-content">
                                    <h4>Asignación de Local</h4>
                                    <p>Nuestro equipo está seleccionando el local perfecto para tu negocio basado en tu perfil y disponibilidad.</p>
                                    <div class="step-status">
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> En proceso
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="step-card pending">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Gestión Completa</h4>
                                    <p>Una vez asignado tu local, podrás crear promociones, gestionar tu negocio y atraer clientes.</p>
                                    <div class="step-status">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-hourglass"></i> Pendiente
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="info-cards">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Tiempo de Asignación</h5>
                                                <p>Normalmente el proceso de asignación toma entre 1-3 días hábiles. Te notificaremos por email cuando esté listo.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="bi bi-envelope"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Mantenete Informado</h5>
                                                <p>Recibirás actualizaciones en tu email <strong><?= htmlspecialchars($usuario['nombreUsuario']) ?></strong> sobre el estado de tu asignación.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 text-center">
                            <div class="contact-card">
                                <h4><i class="bi bi-arrow-clockwise"></i> Actualizar Estado</h4>
                                <p>¿Crees que tu local ya está listo? Actualiza la página para verificar si ha sido asignado.</p>
                                <div class="contact-buttons">
                                    <button class="btn btn-primary btn-lg" onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise"></i> Verificar Estado
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="container mt-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="bi bi-megaphone"></i> Mi Local: <?= htmlspecialchars($local['nombreLocal']) ?></h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($local['ubicacionLocal']) ?>
                    <?php if ($local['rubroLocal']): ?>
                        | <i class="bi bi-tag"></i> <?= htmlspecialchars($local['rubroLocal']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="promocion_nueva.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Nueva Promoción
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($mensajes[$mensaje])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $mensajes[$mensaje] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon text-primary">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div class="stats-number"><?= count($promociones) ?></div>
                <div class="stats-label">Promociones Activas</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stats-number">
                    <?= array_sum(array_column($promociones, 'totalUsos')) ?>
                </div>
                <div class="stats-label">Total de Usos</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon text-warning">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stats-number">
                    <?= count(array_filter($promociones, function($p) { return $p['estadoPromo'] === 'activa'; })) ?>
                </div>
                <div class="stats-label">Promociones Vigentes</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon text-info">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="stats-number">1</div>
                <div class="stats-label">Local Asignado</div>
            </div>
        </div>
    </div>

    <?php if (count($promociones) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Descripción</th>
                    <th>Vigencia</th>
                    <th>Días</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Usos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promociones as $promo): ?>
                    <tr>
                        <td><?= htmlspecialchars($promo['textoPromo']) ?></td>
                        <td><?= $promo['fechaDesdePromo'] ?> a <?= $promo['fechaHastaPromo'] ?></td>
                        <td><?= $promo['diasSemana'] ?></td>
                        <td><?= $promo['categoriaCliente'] ?></td>
                        <td><span class="badge bg-info"><?= ucfirst($promo['estadoPromo']) ?></span></td>
                        <td><span class="badge bg-success"><?= $promo['totalUsos'] ?></span></td>
                        <td>
                            <a href="promocion_eliminar.php?id=<?= $promo['codPromo'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar esta promoción?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="text-center py-5">
            <i class="bi bi-megaphone display-1 text-muted"></i>
            <h4 class="mt-3">No hay promociones creadas</h4>
            <p class="text-muted">Comienza creando tu primera promoción para atraer clientes a tu local.</p>
            <a href="promocion_nueva.php" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Crear Primera Promoción
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
