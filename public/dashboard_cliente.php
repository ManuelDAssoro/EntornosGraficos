<?php
require_once 'auth.php';
requireRole('Cliente');

include 'layout/header.php';
?>

<h1>Bienvenido, cliente</h1>
<p>Este es tu panel personal de compras.</p>

<?php include 'layout/footer.php'; ?>
