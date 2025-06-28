<?php
require_once 'auth.php';
requireRole('Dueño');

include 'layout/header.php';
?>

<h1>Bienvenido, dueño</h1>
<p>Gestioná tus productos y pedidos aquí.</p>

<?php include 'layout/footer.php'; ?>
