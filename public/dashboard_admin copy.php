<?php
require_once 'auth.php';
requireRole('Admin');

include 'layout/header.php';
?>

<h1>Panel de administración</h1>
<p>Acceso completo al sistema.</p>

<?php include 'layout/footer.php'; ?>
