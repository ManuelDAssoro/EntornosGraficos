<?php
require_once 'auth.php';
require_once '../config/db.php';
require_once 'categoria_functions.php';

$usuario_logueado = isLoggedIn();
$categoria_cliente = 'unlogged';

if ($usuario_logueado) {
    $categoria_cliente = strtolower($_SESSION['categoriaCliente'] ?? 'inicial');
}

function canAccessNews($userCategory, $newsCategory) {
    $hierarchy = ['unlogged' => 1, 'inicial' => 2, 'medium' => 3, 'premium' => 4];
    $userLevel = $hierarchy[$userCategory] ?? 1;
    $newsLevel = $hierarchy[$newsCategory] ?? 1;
    return $userLevel >= $newsLevel;
}

$noticias = [];

try {
    $stmt = $pdo->prepare("
        SELECT *, 'novedad' as tipo_noticia, fecha_publicacion as fecha_noticia
        FROM novedades 
        WHERE estado = 'activa' 
        AND fecha_publicacion <= CURRENT_DATE
        ORDER BY fecha_publicacion DESC
        LIMIT 20
    ");
    $stmt->execute();
    $novedades_db = $stmt->fetchAll();
    
    foreach ($novedades_db as $novedad) {
        if (canAccessNews($categoria_cliente, $novedad['categoria_minima'])) {
            $noticias[] = $novedad;
        }
    }
} catch (PDOException $e) {
    $noticias = [];
}

try {
    $categoriaFilter = getCategoriaFilterSQL($categoria_cliente === 'unlogged' ? 'inicial' : $categoria_cliente, 'p');
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombreLocal, l.rubroLocal,
               'promocion' as tipo_noticia,
               p.fechaDesdePromo as fecha_noticia
        FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.estadoPromo = 'activa'
        AND p.fechaDesdePromo >= (CURRENT_DATE - INTERVAL '30 days')
        AND $categoriaFilter
        ORDER BY p.fechaDesdePromo DESC
        LIMIT 10
    ");
    $stmt->execute();
    $promociones_recientes = $stmt->fetchAll();
    
    foreach ($promociones_recientes as $promo) {
        $noticias[] = $promo;
    }
} catch (PDOException $e) {
    // Skip promotion news if there's an error
}

try {
    $stmt = $pdo->prepare("
        SELECT l.*, 'nuevo_local' as tipo_noticia, CURRENT_DATE as fecha_noticia
        FROM locales l
        ORDER BY l.codLocal DESC
        LIMIT 5
    ");
    $stmt->execute();
    $locales_recientes = $stmt->fetchAll();
    
    foreach ($locales_recientes as $local) {
        $noticias[] = $local;
    }
} catch (PDOException $e) {
    // Skip local news if there's an error
}

usort($noticias, function($a, $b) {
    return strtotime($b['fecha_noticia']) - strtotime($a['fecha_noticia']);
});

$page_title = 'Novedades del Shopping - Mi Shopping';
$custom_css = 'novedades.css';
include 'layout/header.php';
?>

<div class="hero-section bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-newspaper"></i> Novedades del Shopping
                </h1>
                <p class="lead mb-0">Mantente al día con las últimas promociones, nuevos locales y eventos especiales</p>
                <?php if ($usuario_logueado): ?>
                    <div class="mt-3">
                        <span class="badge bg-white text-primary fs-6">
                            <i class="bi bi-star"></i> Tu categoría: <?= htmlspecialchars($categoria_cliente) ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-eye"></i> Mostrando novedades básicas
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="hero-icon">
                    <i class="bi bi-megaphone display-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <div class="stats-icon text-success mb-3">
                        <i class="bi bi-percent display-4"></i>
                    </div>
                    <h3 class="text-success"><?= count($promociones_recientes) ?></h3>
                    <p class="text-muted mb-0">Promociones Nuevas</p>
                    <small class="text-muted">Últimos 30 días</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <div class="stats-icon text-info mb-3">
                        <i class="bi bi-shop display-4"></i>
                    </div>
                    <h3 class="text-info"><?= count($locales_recientes) ?></h3>
                    <p class="text-muted mb-0">Locales Destacados</p>
                    <small class="text-muted">Recién llegados</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <div class="stats-icon text-warning mb-3">
                        <i class="bi bi-star display-4"></i>
                    </div>
                    <h3 class="text-warning"><?= count($noticias) ?></h3>
                    <p class="text-muted mb-0">Novedades Totales</p>
                    <small class="text-muted">Para descubrir</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured News -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card featured-news">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-gift display-1 text-primary"></i>
                    </div>
                    <h2 class="text-primary mb-3">¡Bienvenido a las Novedades!</h2>
                    <p class="lead text-muted mb-4">
                        Descubre las últimas promociones, conoce los nuevos locales que se suman al shopping 
                        y no te pierdas ninguna oportunidad de ahorro.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="buscar_descuentos.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Ver Descuentos
                                </a>
                                <a href="buscar_por_codigo.php" class="btn btn-outline-primary">
                                    <i class="bi bi-qr-code"></i> Buscar por Código
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- News Feed -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">
                <i class="bi bi-clock-history"></i> Últimas Novedades
            </h3>
            
            <?php if (empty($noticias)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-newspaper display-1 text-muted mb-4"></i>
                    <h4 class="text-muted">No hay novedades recientes</h4>
                    <p class="text-muted">Vuelve pronto para ver las últimas promociones y noticias del shopping.</p>
                    <a href="buscar_descuentos.php" class="btn btn-primary">
                        <i class="bi bi-search"></i> Explorar Descuentos
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($noticias as $index => $noticia): ?>
                    <?php if ($noticia['tipo_noticia'] === 'novedad'): ?>
                        <div class="card news-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2">
                                        <i class="bi bi-newspaper"></i> Novedad
                                    </span>
                                    <span class="badge bg-<?= $noticia['categoria_minima'] === 'premium' ? 'success' : ($noticia['categoria_minima'] === 'medium' ? 'warning' : ($noticia['categoria_minima'] === 'inicial' ? 'secondary' : 'info')) ?>">
                                        <?= ucfirst($noticia['categoria_minima']) ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y', strtotime($noticia['fecha_noticia'])) ?>
                                </small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-megaphone text-primary"></i>
                                    <?= htmlspecialchars($noticia['titulo']) ?>
                                </h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($noticia['contenido'])) ?></p>
                            </div>
                        </div>
                    <?php elseif ($noticia['tipo_noticia'] === 'promocion'): ?>
                        <div class="card news-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-success me-2">
                                        <i class="bi bi-percent"></i> Nueva Promoción
                                    </span>
                                    <strong><?= htmlspecialchars($noticia['nombreLocal']) ?></strong>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y', strtotime($noticia['fecha_noticia'])) ?>
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-gift text-warning"></i>
                                            <?= htmlspecialchars($noticia['textoPromo']) ?>
                                        </h5>
                                        <div class="news-details">
                                            <?php if (!empty($noticia['rubroLocal'])): ?>
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($noticia['rubroLocal']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge bg-info text-white">
                                                <i class="bi bi-calendar-check"></i>
                                                Hasta <?= date('d/m/Y', strtotime($noticia['fechaHastaPromo'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="usar_promocion.php?codigo=<?= $noticia['codPromo'] ?>" 
                                           class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Usar Promoción
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($noticia['tipo_noticia'] === 'nuevo_local'): ?>
                        <div class="card news-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info me-2">
                                        <i class="bi bi-shop"></i> Nuevo Local
                                    </span>
                                    <strong>¡Bienvenido!</strong>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    Reciente
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-shop text-primary"></i>
                                            <?= htmlspecialchars($noticia['nombreLocal']) ?>
                                        </h5>
                                        <div class="news-details">
                                            <?php if (!empty($noticia['ubicacionLocal'])): ?>
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($noticia['ubicacionLocal']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($noticia['rubroLocal'])): ?>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($noticia['rubroLocal']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="buscar_por_codigo.php?codigo=<?= $noticia['codLocal'] ?>" 
                                           class="btn btn-info">
                                            <i class="bi bi-eye"></i> Ver Local
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card text-center bg-gradient-primary text-white">
                <div class="card-body p-5">
                    <h3 class="mb-3">¿Listo para encontrar tu próximo descuento?</h3>
                    <p class="lead mb-4">
                        Explora todas las promociones disponibles o busca directamente en tu local favorito.
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="buscar_descuentos.php" class="btn btn-light btn-lg">
                            <i class="bi bi-search"></i> Explorar Descuentos
                        </a>
                        <a href="buscar_por_codigo.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-qr-code"></i> Buscar por Código
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
