<?php if (isset($_SESSION['tipoUsuario'])): ?>
    <?php if ($_SESSION['tipoUsuario'] === 'Admin'): ?>
        <a href="dashboard_admin.php" class="btn btn-outline-light me-2">Admin</a>
    <?php elseif ($_SESSION['tipoUsuario'] === 'Dueño'): ?>
        <a href="dashboard_dueno.php" class="btn btn-outline-light me-2">Mi Local</a>
    <?php elseif ($_SESSION['tipoUsuario'] === 'Cliente'): ?>
        <a href="dashboard_cliente.php" class="btn btn-outline-light me-2">Mis Compras</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-light">Salir</a>
<?php else: ?>
    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
    <a href="register.php" class="btn btn-light">Registro</a>
<?php endif; ?>
