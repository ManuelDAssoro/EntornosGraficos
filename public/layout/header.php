<?php
// Get current page name for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Mi Shopping';
$user_role = $_SESSION['tipoUsuario'] ?? null;
$username = '';

// Get username if user is logged in
if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT nombreUsuario FROM usuarios WHERE codUsuario = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $user = $stmt->fetch();
        $username = $user['nombreusuario'] ?? '';
    } catch (Exception $e) {
        $username = 'Usuario';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php if (isset($custom_css)): ?>
        <link rel="stylesheet" href="css/<?= $custom_css ?>">
    <?php endif; ?>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $user_role === 'administrador' ? 'dashboard_admin.php' : ($user_role === 'dueno' ? 'dashboard_dueno.php' : ($user_role === 'cliente' ? 'dashboard_cliente.php' : 'index.php')) ?>">
            <i class="bi bi-shop"></i> Mi Shopping
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($user_role): ?>
                <!-- Role-based navigation -->
                <div class="navbar-nav me-auto">
                    <?php if ($user_role === 'administrador'): ?>
                        <a class="nav-link <?= $current_page === 'dashboard_admin.php' ? 'active' : '' ?>" href="dashboard_admin.php">
                            <i class="bi bi-house"></i> Menu
                        </a>
                        <a class="nav-link <?= $current_page === 'admin_locales.php' ? 'active' : '' ?>" href="admin_locales.php">
                            <i class="bi bi-shop"></i> Locales
                        </a>
                        <a class="nav-link <?= $current_page === 'admin_duenos.php' ? 'active' : '' ?>" href="admin_duenos.php">
                            <i class="bi bi-person-lines-fill"></i> Dueños
                        </a>
                        <a class="nav-link <?= $current_page === 'admin_novedades.php' ? 'active' : '' ?>" href="admin_novedades.php">
                            <i class="bi bi-newspaper"></i> Novedades
                        </a>
                    <?php elseif ($user_role === 'dueno'): ?>
                        <a class="nav-link <?= $current_page === 'dashboard_dueno.php' ? 'active' : '' ?>" href="dashboard_dueno.php">
                            <i class="bi bi-house"></i> Menu
                        </a>
                        <?php
                        // Check if dueño has a local
                        $hasLocal = false;
                        if (isset($_SESSION['usuario_id'])) {
                            try {
                                $stmt = $pdo->prepare("SELECT codLocal FROM locales WHERE codUsuario = ?");
                                $stmt->execute([$_SESSION['usuario_id']]);
                                $hasLocal = $stmt->fetch() ? true : false;
                            } catch (Exception $e) {
                                $hasLocal = false;
                            }
                        }
                        ?>
                        <?php if ($hasLocal): ?>
                            <a class="nav-link <?= $current_page === 'promocion_nueva.php' ? 'active' : '' ?>" href="promocion_nueva.php">
                                <i class="bi bi-plus-circle"></i> Nueva Promoción
                            </a>
                        <?php endif; ?>
                    <?php elseif ($user_role === 'cliente'): ?>
                        <a class="nav-link <?= $current_page === 'dashboard_cliente.php' ? 'active' : '' ?>" href="dashboard_cliente.php">
                            <i class="bi bi-house"></i> Menu
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- User info and logout -->
                <div class="navbar-nav">
                    <span class="navbar-text">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($username) ?>
                    </span>
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            <?php else: ?>
                <!-- Guest navigation -->
                <div class="navbar-nav ms-auto">
                    <a class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>" href="login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                    <a class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>" href="register.php">
                        <i class="bi bi-person-plus"></i> Registrarse
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
