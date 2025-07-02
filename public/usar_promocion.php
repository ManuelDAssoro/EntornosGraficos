<?php
require_once 'auth.php';
requireRole('cliente');
require_once '../config/db.php';
require_once 'categoria_functions.php';

$codPromo = $_GET['codigo'] ?? $_GET['id'] ?? null;
$codUsuario = $_SESSION['usuario_id'];
$confirmar = $_POST['confirmar'] ?? false;

if (!$codPromo) {
    header("Location: dashboard_cliente.php");
    exit;
}

$stmt = $pdo->prepare("SELECT nombreUsuario, categoriaCliente FROM usuarios WHERE codUsuario = ?");
$stmt->execute([$codUsuario]);
$usuario = $stmt->fetch();
$categoriaCliente = $usuario['categoriaCliente'] ?? 'inicial';

$mensaje = '';
$error = '';

if ($confirmar) {
    $resultado = usarPromocion($codUsuario, $codPromo, $pdo);
    
    if ($resultado['success']) {
        $upgradeMessage = '';
        if ($resultado['nueva_categoria'] !== $resultado['categoria_anterior']) {
            $upgradeMessage = "&upgrade=" . $resultado['nueva_categoria'];
        }
        header("Location: dashboard_cliente.php?mensaje=promocion_usada" . $upgradeMessage);
        exit;
    } else {
        $error = $resultado['error'];
    }
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombreLocal, l.ubicacionLocal, l.rubroLocal
        FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.codPromo = ? 
        AND p.estadoPromo = 'activa'
        AND (p.fechaDesdePromo <= CURDATE() AND p.fechaHastaPromo >= CURDATE())
    ");
    $stmt->execute([$codPromo]);
    $promocion = $stmt->fetch();
} catch (PDOException $e) {
    $promocion = false;
}

if (!$promocion) {
    header("Location: dashboard_cliente.php?error=promocion_no_valida");
    exit;
}

if (!puedeAccederPromocion($categoriaCliente, $promocion['categoriaCliente'])) {
    header("Location: dashboard_cliente.php?error=categoria_insuficiente&requerida=" . $promocion['categoriaCliente']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT codUso FROM uso_promociones WHERE codPromo = ? AND codUsuario = ?");
    $stmt->execute([$codPromo, $codUsuario]);
    $yaUsada = $stmt->fetch();
} catch (PDOException $e) {
    $yaUsada = false;
}

if ($yaUsada) {
    header("Location: dashboard_cliente.php?error=promocion_ya_usada");
    exit;
}

$progreso = getCategoryProgress($codUsuario, $pdo);

$page_title = 'Usar Promoción - Mi Shopping';
$custom_css = 'usar-promocion.css';
include 'layout/header.php';
?>

<div class="hero-section bg-success text-white">
    <div class="container">
        <div class="row align-items-center py-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="bi bi-check-circle"></i> Usar Promoción
                </h1>
                <p class="lead mb-0">Confirma el uso de esta promoción en <?= htmlspecialchars($promocion['nombreLocal']) ?></p>
            </div>
            <div class="col-md-4 text-end">
                <?= getCategoryBadge($categoriaCliente) ?>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- User Category Status -->
            <div class="card mb-4 category-card">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0">
                                <i class="bi bi-person-badge"></i> 
                                Tu Categoría: <?= getCategoryBadge($categoriaCliente) ?>
                            </h6>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">
                                <i class="bi bi-graph-up"></i>
                                <?= $progreso['used'] ?> promociones usadas
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($progreso['next_level']): ?>
                        <p class="mb-2">
                            <strong>Progreso hacia <?= $progreso['next_level_name'] ?>:</strong>
                            <?= $progreso['used'] ?> / <?= $progreso['next_level'] ?> promociones
                        </p>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= $progreso['progress_percent'] ?>%"
                                 aria-valuenow="<?= $progreso['progress_percent'] ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <?= round($progreso['progress_percent']) ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            Te faltan <?= $progreso['next_level'] - $progreso['used'] ?> promociones para alcanzar <?= $progreso['next_level_name'] ?>
                        </small>
                    <?php else: ?>
                        <p class="text-success mb-0">
                            <i class="bi bi-trophy"></i> ¡Felicitaciones! Has alcanzado la categoría máxima.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Promotion Details -->
            <div class="card promotion-card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-shop"></i> <?= htmlspecialchars($promocion['nombreLocal']) ?>
                            </h5>
                            <?php if (!empty($promocion['ubicacionLocal'])): ?>
                                <small class="opacity-75">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($promocion['ubicacionLocal']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto">
                            <?= getCategoryBadge($promocion['categoriaCliente']) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="promotion-content mb-4">
                        <h4 class="text-primary mb-3">
                            <i class="bi bi-percent"></i> <?= htmlspecialchars($promocion['textoPromo']) ?>
                        </h4>
                        
                        <div class="row mb-3">
                            <?php if (!empty($promocion['rubroLocal'])): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="bi bi-tag text-info"></i>
                                        <strong>Rubro:</strong> <?= htmlspecialchars($promocion['rubroLocal']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="bi bi-calendar-check text-warning"></i>
                                    <strong>Válido hasta:</strong> <?= date('d/m/Y', strtotime($promocion['fechaHastaPromo'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($promocion['diasSemana'])): ?>
                            <div class="info-item mb-3">
                                <i class="bi bi-clock text-secondary"></i>
                                <strong>Días válidos:</strong> <?= htmlspecialchars($promocion['diasSemana']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <i class="bi bi-people text-success"></i>
                            <strong>Categoría requerida:</strong> <?= getCategoryBadge($promocion['categoriaCliente']) ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>¿Confirmas el uso de esta promoción?</strong><br>
                        Una vez confirmado, esta promoción se marcará como utilizada y no podrás usarla nuevamente.
                        Además, se contabilizará para tu progreso de categoría.
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <form method="POST" class="d-flex gap-3 justify-content-end">
                        <a href="dashboard_cliente.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" name="confirmar" value="1" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Confirmar Uso
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
