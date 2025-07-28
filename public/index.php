<?php
session_start();
require_once '../config/db.php';
require_once 'categoria_functions.php';

$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener promociones activas para el carrusel 
$stmt = $pdo->query("
    SELECT p.*, l.nombrelocal, l.ubicacionlocal, l.rubrolocal
    FROM promociones p
    JOIN locales l ON p.codlocal = l.codlocal
    WHERE p.estadopromo = 'activa'
    AND (p.fechadesdepromo <= CURRENT_DATE AND p.fechahastapromo >= CURRENT_DATE)
    ORDER BY p.fechadesdepromo DESC
    LIMIT 5
");
$promociones_destacadas = $stmt->fetchAll();

// Obtener novedades 
$stmt = $pdo->query("
    SELECT * FROM novedades 
    WHERE estado = 'activa' 
    AND categoria_minima IN ('unlogged', 'inicial')
    AND fecha_publicacion <= CURRENT_DATE
    ORDER BY fecha_publicacion DESC 
    LIMIT 4
");
$novedades_recientes = $stmt->fetchAll();

// Obtener locales
$stmt = $pdo->query("
    SELECT l.*, COUNT(p.codpromo) as total_promociones
    FROM locales l
    LEFT JOIN promociones p ON l.codlocal = p.codlocal AND p.estadopromo = 'activa'
    GROUP BY l.codlocal
    ORDER BY total_promociones DESC, l.nombrelocal
    LIMIT 6
");
$locales_destacados = $stmt->fetchAll();

// Datos del shopping - ESTÁTICOS
$datos_shopping = [
    'nombre' => 'Shopping',
    'descripcion' => 'El centro comercial más completo de la ciudad con las mejores marcas y servicios',
    'direccion' => 'Calle 1234, Centro, Rosario',
    'telefono' => '+54 123 1234-5678',
    'email' => 'info@shopping.com',
    'horarios' => 'Lunes a Domingo: 10:00 - 22:00 hs',
    'total_locales' => $pdo->query("SELECT COUNT(*) FROM locales")->fetchColumn(),
    'total_promociones' => $pdo->query("SELECT COUNT(*) FROM promociones WHERE estadopromo = 'activa'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/index-carousel.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-shop"></i> Shopping
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="buscar_descuentos.php">
                    <i class="bi bi-search"></i> Promociones
                </a>
                <a class="nav-link" href="buscar_por_codigo.php">
                    <i class="bi bi-shop"></i> Locales
                </a>
                <a class="nav-link" href="novedades.php">
                    <i class="bi bi-newspaper"></i> Novedades
                </a>
            </div>
            <div class="navbar-nav">
                <?php if (!$usuario_logueado): ?>
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                    <a class="nav-link" href="register.php">
                        <i class="bi bi-person-plus"></i> Registrarse
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="dashboard_cliente.php">
                        <i class="bi bi-speedometer2"></i> Mi Panel
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Carrusel -->
<div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="6000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
    </div>
    
    <div class="carousel-inner">
        <!-- Slide 1: Bienvenida al Shopping -->
        <div class="carousel-item active carousel-bienvenida">
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <div class="hero-icon mb-4">
                                <i class="bi bi-shop display-1"></i>
                            </div>
                            <h1 class="display-3 fw-bold mb-4">¡Bienvenido al Shopping!</h1>
                            <p class="lead mb-4">El centro comercial más completo de la ciudad con las mejores marcas y servicios</p>
                            <div class="row text-center mb-4">
                                <div class="col-md-6">
                                    <div class="stat-number"><?= $datos_shopping['total_locales'] ?>+</div>
                                    <p class="stat-label">Locales </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-number"><?= $datos_shopping['total_promociones'] ?>+</div>
                                    <p class="stat-label">Promociones </p>
                                </div>
                            </div>
                            <div class="hero-buttons">
                                <a href="buscar_descuentos.php" class="btn btn-light btn-lg me-3">
                                    <i class="bi bi-search"></i> Ver Promociones
                                </a>
                                <a href="#info-shopping" class="btn btn-outline-light btn-lg">
                                    <i class="bi bi-info-circle"></i> Más Información
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: Promociones  -->
        <div class="carousel-item carousel-promociones">
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-10">
                            <div class="hero-icon mb-4">
                                <i class="bi bi-percent display-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold mb-4">¡Promociones Increíbles!</h2>
                            <p class="lead mb-5">Descubre las mejores ofertas y descuentos exclusivos</p>
                            
                            <?php if (!empty($promociones_destacadas)): ?>
                                <div class="row g-4 mb-5">
                                    <?php foreach (array_slice($promociones_destacadas, 0, 3) as $promo): ?>
                                        <div class="col-md-4">
                                            <div class="promo-card">
                                                <div class="promo-header">
                                                    <i class="bi bi-shop"></i>
                                                    <span><?= htmlspecialchars($promo['nombrelocal']) ?></span>
                                                </div>
                                                <div class="promo-content">
                                                    <h5><?= htmlspecialchars($promo['textopromo']) ?></h5>
                                                    <small>Válido hasta: <?= date('d/m/Y', strtotime($promo['fechahastapromo'])) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="buscar_descuentos.php" class="btn btn-light btn-lg">
                                <i class="bi bi-eye"></i> Ver Todas las Promociones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: Novedades -->
        <div class="carousel-item carousel-novedades">
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-10">
                            <div class="hero-icon mb-4">
                                <i class="bi bi-newspaper display-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold mb-4">Últimas Novedades</h2>
                            <p class="lead mb-5">Mantente al día con las últimas noticias y eventos del shopping</p>
                            
                            <?php if (!empty($novedades_recientes)): ?>
                                <div class="row g-4 mb-5">
                                    <?php foreach (array_slice($novedades_recientes, 0, 2) as $novedad): ?>
                                        <div class="col-md-6">
                                            <div class="news-card">
                                                <div class="news-header">
                                                    <i class="bi bi-megaphone"></i>
                                                    <span>Novedad</span>
                                                </div>
                                                <div class="news-content">
                                                    <h5><?= htmlspecialchars($novedad['titulonovedad']) ?></h5>
                                                    <p><?= htmlspecialchars(substr($novedad['textonovedad'], 0, 100)) ?>...</p>
                                                    <small><?= date('d/m/Y', strtotime($novedad['fechanovedad'])) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="novedades.php" class="btn btn-light btn-lg">
                                <i class="bi bi-newspaper"></i> Todas las Novedades
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 4: Locales  -->
        <div class="carousel-item carousel-locales">
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-10">
                            <div class="hero-icon mb-4">
                                <i class="bi bi-building display-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold mb-4">Nuestros Locales</h2>
                            <p class="lead mb-5">Descubre la gran variedad de tiendas y servicios</p>
                            
                            <?php if (!empty($locales_destacados)): ?>
                                <div class="row g-4 mb-5">
                                    <?php foreach (array_slice($locales_destacados, 0, 3) as $local): ?>
                                        <div class="col-md-4">
                                            <div class="local-card">
                                                <div class="local-header">
                                                    <i class="bi bi-shop"></i>
                                                    <span><?= htmlspecialchars($local['nombrelocal']) ?></span>
                                                </div>
                                                <div class="local-content">
                                                    <?php if (!empty($local['ubicacionlocal'])): ?>
                                                        <p><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($local['ubicacionlocal']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($local['rubrolocal'])): ?>
                                                        <span class="badge bg-primary"><?= htmlspecialchars($local['rubrolocal']) ?></span>
                                                    <?php endif; ?>
                                                    <small class="text-success d-block mt-2">
                                                        <?= $local['total_promociones'] ?> promociones activas
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="buscar_por_codigo.php" class="btn btn-light btn-lg">
                                <i class="bi bi-search"></i> Buscar Locales
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>

<!-- Estadísticas del Shopping -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-shop display-4"></i>
                    </div>
                    <div class="stat-number"><?= $datos_shopping['total_locales'] ?>+</div>
                    <h5>Locales</h5>
                    <p class="text-muted">Tiendas y servicios</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="bi bi-percent display-4"></i>
                    </div>
                    <div class="stat-number"><?= $datos_shopping['total_promociones'] ?>+</div>
                    <h5>Promociones</h5>
                    <p class="text-muted">Ofertas activas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-calendar display-4"></i>
                    </div>
                    <div class="stat-number">25</div>
                    <h5>Años</h5>
                    <p class="text-muted">De experiencia</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-info">
                        <i class="bi bi-people display-4"></i>
                    </div>
                    <div class="stat-number">250.000</div>
                    <h5>Visitantes</h5>
                    <p class="text-muted">Mensuales</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Información del Shopping -->
<section id="info-shopping" class="info-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 mb-4">Shopping</h2>
                <p class="lead mb-4">El centro comercial más completo de la ciudad con las mejores marcas y servicios</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6><i class="bi bi-geo-alt me-2"></i>Dirección</h6>
                        <p>Calle 1234, Centro, Rosario</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><i class="bi bi-clock me-2"></i>Horarios</h6>
                        <p>Lunes a Domingo: 10:00 - 22:00 hs</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><i class="bi bi-telephone me-2"></i>Teléfono</h6>
                        <p>+54 123 1234-5678</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><i class="bi bi-envelope me-2"></i>Email</h6>
                        <p>info@shopping.com</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="cta-buttons">
                    <?php if (!$usuario_logueado): ?>
                        <a href="register.php" class="btn btn-light btn-lg mb-3 w-100">
                            <i class="bi bi-person-plus"></i> Registrarse
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    <?php else: ?>
                        <a href="dashboard_cliente.php" class="btn btn-light btn-lg mb-3 w-100">
                            <i class="bi bi-speedometer2"></i> Mi Panel
                        </a>
                        <a href="buscar_descuentos.php" class="btn btn-outline-light btn-lg w-100">
                            <i class="bi bi-search"></i> Buscar Promociones
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/carousel.js"></script>
</body>
</html>
