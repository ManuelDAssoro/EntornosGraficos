<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

$codUsuario = $_SESSION['codUsuario'];


$stmt = $pdo->prepare("SELECT codLocal FROM locales WHERE codUsuario = ?");
$stmt->execute([$codUsuario]);
$local = $stmt->fetch();

if (!$local) {
    die("No tenés un local asignado.");
}

$codLocal = $local['codLocal'];


$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM uso_promociones u 
            WHERE u.codPromo = p.codPromo AND u.estado = 'usado') AS totalUsos
    FROM promociones p
    WHERE p.codLocal = ?
    ORDER BY p.fechaDesdePromo DESC
");
$stmt->execute([$codLocal]);
$promociones = $stmt->fetchAll();


$mensaje = $_GET['mensaje'] ?? '';
$mensajes = [
    'creada' => 'La promoción fue creada exitosamente.',
    'eliminada' => 'La promoción fue eliminada correctamente.'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Promociones</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard_dueno.php">
            <i class="bi bi-shop"></i> Mi Local
        </a>
    </div>
</nav>

<div class="container mt-4">
    <h1><i class="bi bi-megaphone"></i> Mis Promociones</h1>

    <?php if (isset($mensajes[$mensaje])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $mensajes[$mensaje] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="promocion_nueva.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Promoción
        </a>
    </div>

    <!-- Tabla de promociones -->
    <div class="table-responsive mb-5">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Descripción</th>
                    <th>Vigencia</th>
                    <th>Días</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Usos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promociones as $promo): ?>
                    <tr>
                        <td><?= htmlspecialchars($promo['textoPromo']) ?></td>
                        <td><?= $promo['fechaDesdePromo'] ?> a <?= $promo['fechaHastaPromo'] ?></td>
                        <td><?= $promo['diasSemana'] ?></td>
                        <td><?= $promo['categoriaCliente'] ?></td>
                        <td><span class="badge bg-info"><?= ucfirst($promo['estadoPromo']) ?></span></td>
                        <td><?= $promo['totalUsos'] ?></td>
                        <td>
                            <a href="promocion_eliminar.php?id=<?= $promo['codPromo'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar esta promoción?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
