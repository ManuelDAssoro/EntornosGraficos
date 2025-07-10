<?php
require_once 'auth.php';
require_once '../config/db.php';
require_once 'categoria_functions.php';

$usuario_logueado = isLoggedIn();
$categoria_cliente = 'inicial'; 

if ($usuario_logueado) {
    $categoria_cliente = $_SESSION['categoriaCliente'] ?? 'inicial';
}

$filtro_rubro = $_GET['rubro'] ?? '';
$filtro_busqueda = $_GET['busqueda'] ?? '';
$mensaje = $_GET['mensaje'] ?? '';


if ($usuario_logueado) {
    $categoriaFilter = getCategoriaFilterSQL($categoria_cliente, 'p');
} else {
    $categoriaFilter = "1=1"; 
} 

$query = "
    SELECT p.*, l.nombrelocal, l.ubicacionlocal, l.rubrolocal
    FROM promociones p
    JOIN locales l ON p.codlocal = l.codlocal
    WHERE p.estadopromo = 'activa'
    AND (p.fechadesdepromo <= CURRENT_DATE AND p.fechahastapromo >= CURRENT_DATE)
    AND $categoriaFilter
";

$params = [];

if (!empty($filtro_rubro)) {
    $query .= " AND l.rubrolocal ILIKE ?";
    $params[] = "%$filtro_rubro%";
}

if (!empty($filtro_busqueda)) {
    $query .= " AND (l.nombrelocal ILIKE ? OR p.textopromo ILIKE ?)";

    $params[] = "%$filtro_busqueda%";
    $params[] = "%$filtro_busqueda%";
}

$query .= " ORDER BY l.nombrelocal, p.fechahastapromo";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$promociones = $stmt->fetchAll();

$stmt = $pdo->query("SELECT DISTINCT rubrolocal FROM locales WHERE rubrolocal IS NOT NULL AND rubrolocal != '' ORDER BY rubrolocal");
$rubros = $stmt->fetchAll();

$page_title = 'Buscar Descuentos - Mi Shopping';
$custom_css = 'buscar-descuentos.css';
include 'layout/header.php';
?>

<div class="hero-section bg-primary text-white">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-search"></i> Buscar Descuentos
                </h1>
                <p class="lead mb-0">Encuentra las mejores ofertas y promociones en todos los locales del shopping
                </p>
                <?php if ($usuario_logueado): ?>
                <div class="mt-3">
                    <span class="badge bg-white text-primary fs-6">
                        <i class="bi bi-star"></i> Tu categoría: <?= htmlspecialchars($categoria_cliente) ?>
                    </span>
                </div>
                <?php else: ?>
                <div class="mt-3">
                    <span class="badge bg-warning text-dark fs-6">
                        <i class="bi bi-eye"></i> Viendo todas las promociones - ¡Regístrate para usar!
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="hero-icon">
                    <i class="bi bi-percent display-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($mensaje === 'promocion_usada'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>¡Excelente!</strong> Promoción utilizada exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="busqueda" name="busqueda"
                            value="<?= htmlspecialchars($filtro_busqueda) ?>"
                            placeholder="Nombre del local o promoción">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="rubro" class="form-label">Rubro</label>
                    <select class="form-select" id="rubro" name="rubro">
                        <option value="">Todos los rubros</option>
                        <?php foreach ($rubros as $rubro): ?>
                        <option value="<?= htmlspecialchars($rubro['rubrolocal']) ?>"
                            <?= $filtro_rubro === $rubro['rubrolocal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rubro['rubrolocal']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="buscar_descuentos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Promotions Results -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>
                    <i class="bi bi-tag-fill text-warning"></i>
                    Promociones Disponibles
                    <span class="badge bg-primary ms-2"><?= count($promociones) ?></span>
                </h3>
                <a href="buscar_por_codigo.php" class="btn btn-outline-primary">
                    <i class="bi bi-qr-code"></i> Buscar por Código
                </a>
            </div>

            <?php if (empty($promociones)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-search display-1 text-muted"></i>
                </div>
                <h4 class="text-muted">No se encontraron promociones</h4>
                <p class="text-muted">
                    <?php if (!empty($filtro_busqueda) || !empty($filtro_rubro)): ?>
                    Intenta cambiar los filtros de búsqueda para encontrar más resultados.
                    <?php else: ?>
                    No hay promociones activas en este momento.
                    <?php endif; ?>
                </p>
                <a href="buscar_descuentos.php" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Ver todas las promociones
                </a>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($promociones as $promo): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 promotion-card">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-shop"></i>
                                <?= htmlspecialchars($promo['nombrelocal']) ?>
                            </h6>
                            <?php if (!empty($promo['rubrolocal'])): ?>
                            <small class="opacity-75">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($promo['rubrolocal']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="promotion-text mb-3">
                                <i class="bi bi-percent text-warning"></i>
                                <?= htmlspecialchars($promo['textopromo']) ?>
                            </div>

                            <?php if (!empty($promo['ubicacionlocal'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($promo['ubicacionlocal']) ?>
                                </small>
                            </div>
                            <?php endif; ?>

                            <div class="promotion-dates mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check"></i>
                                    Válido hasta: <?= date('d/m/Y', strtotime($promo['fechahastapromo'])) ?>
                                </small>
                            </div>

                            <?php if (!empty($promo['diassemana'])): ?>
                            <div class="days-available mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    Días: <?= htmlspecialchars($promo['diassemana']) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <?php if ($usuario_logueado): ?>
                            <a href="usar_promocion.php?codigo=<?= $promo['codpromo'] ?>"
                                class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Usar Promoción
                            </a>
                            <?php else: ?>
                            <div class="text-center">
                                <a href="register.php" class="btn btn-warning w-100">
                                    <i class="bi bi-person-plus"></i> Registrarse para usar
                                </a>
                                <small class="text-muted d-block mt-1">
                                    ¡Regístrate gratis para usar las promociones!
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <i class="bi bi-qr-code display-4 text-primary mb-3"></i>
                    <h5>¿Tenés un código?</h5>
                    <p class="text-muted">Ingresá el código del local para ver sus promociones</p>
                    <a href="buscar_por_codigo.php" class="btn btn-primary">
                        <i class="bi bi-qr-code"></i> Ingresar Código
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <i class="bi bi-newspaper display-4 text-info mb-3"></i>
                    <h5>Novedades del Shopping</h5>
                    <p class="text-muted">Mantente al día con las últimas noticias y eventos</p>
                    <a href="novedades.php" class="btn btn-info">
                        <i class="bi bi-newspaper"></i> Ver Novedades
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
