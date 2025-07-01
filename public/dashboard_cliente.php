<?php
require_once 'auth.php';
requireRole('cliente');
require_once '../config/db.php';

$codUsuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT nombreUsuario FROM usuarios WHERE codUsuario = ?");
$stmt->execute([$codUsuario]);
$usuario = $stmt->fetch();
$nombreUsuario = $usuario['nombreUsuario'] ?? 'Usuario';

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

$mensajes = [
    'promocion_usada' => 'Promoción utilizada exitosamente.'
];

$errores = [
    'promocion_no_valida' => 'La promoción no es válida o ha expirado.',
    'promocion_ya_usada' => 'Ya has utilizado esta promoción anteriormente.'
];

$promocionesUsadas = [];
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'uso_promociones'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("
            SELECT up.*, p.textoPromo, l.nombreLocal, l.ubicacionLocal as ubicacion
            FROM uso_promociones up
            JOIN promociones p ON up.codPromo = p.codPromo
            JOIN locales l ON p.codLocal = l.codLocal
            WHERE up.codUsuario = ?
            ORDER BY up.fechaUso DESC
            LIMIT 10
        ");
        $stmt->execute([$codUsuario]);
        $promocionesUsadas = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // uso_promociones might not exist yet
    $promocionesUsadas = [];
}

$promocionesDisponibles = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombreLocal, l.ubicacionLocal as ubicacion
        FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.estadoPromo = 'activa'
        AND p.fechaDesdePromo <= CURDATE()
        AND p.fechaHastaPromo >= CURDATE()
        ORDER BY p.fechaHastaPromo ASC
        LIMIT 6
    ");
    $stmt->execute();
    $promocionesDisponibles = $stmt->fetchAll();
} catch (PDOException $e) {
    $promocionesDisponibles = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel - Cliente</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard-cliente.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard_cliente.php">
            <i class="bi bi-person-circle"></i> Mi Panel
        </a>
        <div class="d-flex">
            <?php include 'layout/header.php'; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="bi bi-house-door"></i> Bienvenido, <?= htmlspecialchars($nombreUsuario) ?></h1>
            <p class="lead">Descubre las mejores promociones disponibles en el shopping.</p>
            
            <?php if (isset($mensajes[$mensaje])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?= $mensajes[$mensaje] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errores[$error])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= $errores[$error] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="card-title mb-0"><?= count($promocionesUsadas) ?></h5>
                            <p class="card-text text-muted">Promociones Usadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success">
                            <i class="bi bi-gift"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="card-title mb-0"><?= count($promocionesDisponibles) ?></h5>
                            <p class="card-text text-muted">Disponibles Ahora</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h3><i class="bi bi-gift"></i> Promociones Disponibles</h3>
            <p>Aprovecha estas ofertas especiales antes de que expiren.</p>
        </div>
    </div>

    <div class="row">
        <?php if (count($promocionesDisponibles) > 0): ?>
            <?php foreach ($promocionesDisponibles as $promo): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card promo-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-shop"></i> <?= htmlspecialchars($promo['nombreLocal']) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($promo['textoPromo']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($promo['ubicacion']) ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Válido hasta: <?= $promo['fechaHastaPromo'] ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-week"></i> Días: <?= $promo['diasSemana'] ?>
                                </small>
                            </p>
                            <span class="badge bg-info"><?= ucfirst($promo['categoriaCliente']) ?></span>
                        </div>
                        <div class="card-footer">
                            <a href="usar_promocion.php?id=<?= $promo['codPromo'] ?>" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check-circle"></i> Usar Promoción
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-inbox display-1"></i>
                    <h5 class="mt-3">No hay promociones disponibles</h5>
                    <p>Vuelve pronto para ver nuevas ofertas.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($promocionesUsadas) > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3><i class="bi bi-clock-history"></i> Promociones Recientes</h3>
                <p>Historial de tus últimas promociones utilizadas.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Promoción</th>
                                <th>Local</th>
                                <th>Fecha de Uso</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promocionesUsadas as $usado): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usado['textoPromo']) ?></td>
                                    <td>
                                        <i class="bi bi-shop"></i> <?= htmlspecialchars($usado['nombreLocal']) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($usado['ubicacion']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($usado['fechaUso'])) ?></td>
                                    <td>
                                        <span class="badge bg-success"><?= ucfirst($usado['estado']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
