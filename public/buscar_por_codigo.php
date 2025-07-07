<?php
require_once 'auth.php';
require_once '../config/db.php';
require_once 'categoria_functions.php';

$usuario_logueado = isLoggedIn();
$categoria_cliente = 'Inicial';

if ($usuario_logueado) {
    $categoria_cliente = $_SESSION['categoriaCliente'] ?? 'Inicial';
}

$codigo_local = $_POST['codigo_local'] ?? $_GET['codigo'] ?? '';
$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

$local_encontrado = null;
$promociones_local = [];

if (!empty($codigo_local)) {
    $stmt = $pdo->prepare("
        SELECT * FROM locales 
        WHERE codLocal = ? 
        OR nombreLocal ILIKE ? 
        OR ubicacionLocal ILIKE ?
        LIMIT 1
    ");
    $stmt->execute([$codigo_local, "%$codigo_local%", "%$codigo_local%"]);
    $local_encontrado = $stmt->fetch();
    
    if ($local_encontrado) {
        $categoriaFilter = getCategoriaFilterSQL($categoria_cliente, '');
        $stmt = $pdo->prepare("
            SELECT * FROM promociones 
            WHERE codLocal = ? 
            AND estadoPromo = 'activa'
            AND (fechaDesdePromo <= CURRENT_DATE AND fechaHastaPromo >= CURRENT_DATE)
            AND $categoriaFilter
            ORDER BY fechaHastaPromo
        ");
        $stmt->execute([$local_encontrado['codLocal']]);
        $promociones_local = $stmt->fetchAll();
    }
}

$stmt = $pdo->prepare("
    SELECT l.*, COUNT(p.codPromo) as total_promociones
    FROM locales l
    LEFT JOIN promociones p ON l.codLocal = p.codLocal 
        AND p.estadoPromo = 'activa'
        AND (p.fechaDesdePromo <= CURRENT_DATE AND p.fechaHastaPromo >= CURRENT_DATE)
    GROUP BY l.codLocal
    ORDER BY l.nombreLocal
");
$stmt->execute();
$todos_locales = $stmt->fetchAll();

$page_title = 'Buscar por Código - Mi Shopping';
$custom_css = 'buscar-por-codigo.css';
include 'layout/header.php';
?>

<div class="hero-section bg-info text-white">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-qr-code"></i> Buscar por Código
                </h1>
                <p class="lead mb-0">Ingresá el código del local para acceder a sus promociones exclusivas</p>
                <?php if ($usuario_logueado): ?>
                    <div class="mt-3">
                        <span class="badge bg-white text-info fs-6">
                            <i class="bi bi-star"></i> Tu categoría: <?= htmlspecialchars($categoria_cliente) ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-eye"></i> Mostrando promociones básicas
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="hero-icon">
                    <i class="bi bi-search display-1"></i>
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

    <?php if ($error === 'local_no_encontrado'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Local no encontrado.</strong> Verifica que el código sea correcto e intenta nuevamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card search-card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-qr-code display-1 text-info"></i>
                    </div>
                    <h3 class="mb-4">Ingresá el Código del Local</h3>
                    <p class="text-muted mb-4">
                        Podés usar el código del local, el nombre o la ubicación para encontrar las promociones disponibles.
                    </p>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="codigo_local" 
                                   value="<?= htmlspecialchars($codigo_local) ?>"
                                   placeholder="Código, nombre o ubicación del local"
                                   autofocus>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingresa un código válido.
                        </div>
                    </form>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="example-code">
                                <i class="bi bi-123 text-muted"></i>
                                <small class="text-muted d-block">Código: 15</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="example-code">
                                <i class="bi bi-shop text-muted"></i>
                                <small class="text-muted d-block">Nombre: "Tienda Tech"</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="example-code">
                                <i class="bi bi-geo-alt text-muted"></i>
                                <small class="text-muted d-block">Ubicación: "Local A-101"</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($codigo_local) && !$local_encontrado): ?>
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
                        <h4>Local no encontrado</h4>
                        <p class="text-muted">
                            No se encontró ningún local con el código "<strong><?= htmlspecialchars($codigo_local) ?></strong>".
                        </p>
                        <div class="mt-3">
                            <a href="buscar_descuentos.php" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Ver Todos los Descuentos
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('input[name=codigo_local]').focus()">
                                <i class="bi bi-arrow-repeat"></i> Intentar Nuevamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($local_encontrado): ?>
        <!-- Local Found -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card local-info-card">
                    <div class="card-header bg-success text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-0">
                                    <i class="bi bi-shop"></i> <?= htmlspecialchars($local_encontrado['nombreLocal']) ?>
                                </h4>
                                <?php if (!empty($local_encontrado['ubicacionLocal'])): ?>
                                    <small class="opacity-75">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($local_encontrado['ubicacionLocal']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if (!empty($local_encontrado['rubroLocal'])): ?>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-tag"></i> <?= htmlspecialchars($local_encontrado['rubroLocal']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-percent text-warning"></i>
                            Promociones Disponibles
                            <span class="badge bg-primary ms-2"><?= count($promociones_local) ?></span>
                        </h5>
                        
                        <?php if (empty($promociones_local)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay promociones activas</h5>
                                <p class="text-muted">Este local no tiene promociones disponibles en este momento.</p>
                                <a href="buscar_descuentos.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Ver Otros Descuentos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($promociones_local as $promo): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 promotion-card">
                                            <div class="card-body">
                                                <div class="promotion-text mb-3">
                                                    <i class="bi bi-percent text-warning me-2"></i>
                                                    <?= htmlspecialchars($promo['textoPromo']) ?>
                                                </div>
                                                
                                                <div class="promotion-details">
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-check"></i>
                                                            Válido hasta: <?= date('d/m/Y', strtotime($promo['fechaHastaPromo'])) ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <?php if (!empty($promo['diasSemana'])): ?>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock"></i>
                                                                Días: <?= htmlspecialchars($promo['diasSemana']) ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($promo['categoriaCliente'])): ?>
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="bi bi-people"></i>
                                                                Categoría: <?= htmlspecialchars($promo['categoriaCliente']) ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <?php if ($usuario_logueado): ?>
                                                    <a href="usar_promocion.php?codigo=<?= $promo['codPromo'] ?>" 
                                                       class="btn btn-success w-100">
                                                        <i class="bi bi-check-circle"></i> Usar Promoción
                                                    </a>
                                                <?php else: ?>
                                                    <div class="text-center">
                                                        <p class="text-muted mb-2">
                                                            <i class="bi bi-info-circle"></i>
                                                            Registrate para usar esta promoción
                                                        </p>
                                                        <a href="register.php" class="btn btn-primary me-2">
                                                            <i class="bi bi-person-plus"></i> Registrarse
                                                        </a>
                                                        <a href="login.php" class="btn btn-outline-primary">
                                                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                                        </a>
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
            </div>
        </div>
    <?php endif; ?>

    <!-- Available Locales -->
    <div class="row mb-5 mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-building"></i> Locales Disponibles
                        <span class="badge bg-primary ms-2"><?= count($todos_locales) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Estos son todos los locales disponibles en el shopping. Podés hacer clic en cualquiera para ver sus promociones.
                    </p>
                    
                    <?php if (empty($todos_locales)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-building-x display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">No hay locales registrados</h5>
                            <p class="text-muted">Aún no se han registrado locales en el sistema.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($todos_locales as $local): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 local-card" 
                                         style="cursor: pointer;" 
                                         onclick="buscarLocal('<?= htmlspecialchars($local['codLocal'] ?? '') ?>')">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-1">
                                                    <i class="bi bi-shop text-primary"></i>
                                                    <?= htmlspecialchars($local['nombreLocal'] ?? '') ?>
                                                </h6>
                                                <span class="badge bg-light text-dark">
                                                    ID: <?= htmlspecialchars($local['codLocal'] ?? '') ?>
                                                </span>
                                            </div>
                                            
                                            <?php if (!empty($local['ubicacionLocal'])): ?>
                                                <p class="card-text small text-muted mb-2">
                                                    <i class="bi bi-geo-alt"></i>
                                                    <?= htmlspecialchars($local['ubicacionLocal'] ?? '') ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($local['rubroLocal'])): ?>
                                                <p class="card-text small mb-2">
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-tag"></i>
                                                        <?= htmlspecialchars($local['rubroLocal'] ?? '') ?>
                                                    </span>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="promotion-count">
                                                <small class="text-muted">
                                                    <i class="bi bi-percent"></i>
                                                    <?= htmlspecialchars($local['total_promociones'] ?? '0') ?> promociones activas
                                                </small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-primary">
                                                <i class="bi bi-cursor-fill"></i>
                                                Hacer clic para buscar promociones
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <i class="bi bi-search display-4 text-primary mb-3"></i>
                    <h5>Explorar Descuentos</h5>
                    <p class="text-muted">Descubre todas las promociones disponibles</p>
                    <a href="buscar_descuentos.php" class="btn btn-primary">
                        <i class="bi bi-search"></i> Ver Todos los Descuentos
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <i class="bi bi-arrow-left display-4 text-secondary mb-3"></i>
                    <h5>Volver al Dashboard</h5>
                    <p class="text-muted">Regresa a tu panel principal</p>
                    <a href="dashboard_cliente.php" class="btn btn-secondary">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function buscarLocal(codLocal) {
    const input = document.querySelector('input[name="codigo_local"]');
    if (input) {
        input.value = codLocal;
        input.closest('form').submit();
    }
}

(function() {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    const localCards = document.querySelectorAll('.local-card');
    localCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
            this.style.transition = 'all 0.2s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php include 'layout/footer.php'; ?>
