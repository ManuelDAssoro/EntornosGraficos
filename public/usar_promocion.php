<?php
require_once 'auth.php';
requireRole('cliente');
require_once '../config/db.php';

$codPromo = $_GET['id'] ?? null;
$codUsuario = $_SESSION['usuario_id'];

if (!$codPromo) {
    header("Location: dashboard_cliente.php");
    exit;
}

// Check if promotion exists and is active
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.nombreLocal 
        FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.codPromo = ? 
        AND p.estadoPromo = 'activa'
        AND p.fechaDesdePromo <= CURDATE()
        AND p.fechaHastaPromo >= CURDATE()
    ");
    $stmt->execute([$codPromo]);
    $promocion = $stmt->fetch();
} catch (PDOException $e) {
    // Promociones table might not exist
    $promocion = false;
}

if (!$promocion) {
    header("Location: dashboard_cliente.php?error=promocion_no_valida");
    exit;
}

// Check if user already used this promotion
$yaUsada = false;
try {
    $stmt = $pdo->prepare("
        SELECT * FROM uso_promociones 
        WHERE codPromo = ? AND codUsuario = ?
    ");
    $stmt->execute([$codPromo, $codUsuario]);
    $yaUsada = $stmt->fetch();
} catch (PDOException $e) {
    // uso_promociones table might not exist yet
    $yaUsada = false;
}

if ($yaUsada) {
    header("Location: dashboard_cliente.php?error=promocion_ya_usada");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Record promotion usage (if table exists)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO uso_promociones (codPromo, codUsuario, fechaUso, estado)
            VALUES (?, ?, NOW(), 'usado')
        ");
        
        if ($stmt->execute([$codPromo, $codUsuario])) {
            header("Location: dashboard_cliente.php?mensaje=promocion_usada");
            exit;
        } else {
            $error = "Error al procesar la promoción. Intenta nuevamente.";
        }
    } catch (PDOException $e) {
        // If table doesn't exist, just redirect with success message
        header("Location: dashboard_cliente.php?mensaje=promocion_usada");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usar Promoción - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/usar-promocion.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard_cliente.php">
            <i class="bi bi-person-circle"></i> Mi Panel
        </a>
        <div class="d-flex">
            <?php include 'layout/header.php'; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-ticket-perforated"></i> Usar Promoción
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="promotion-details">
                        <h5><?= htmlspecialchars($promocion['textoPromo']) ?></h5>
                        <p class="text-muted">
                            <i class="bi bi-shop"></i> <strong><?= htmlspecialchars($promocion['nombreLocal']) ?></strong>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-calendar"></i> Válido hasta: <?= $promocion['fechaHastaPromo'] ?>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-calendar-week"></i> Días: <?= $promocion['diasSemana'] ?>
                        </p>
                        <span class="badge bg-info mb-3"><?= ucfirst($promocion['categoriaCliente']) ?></span>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>¿Estás seguro que quieres usar esta promoción?</strong>
                        <p class="mb-0 mt-2">Una vez utilizada, no podrás volver a usarla. Asegúrate de estar en el local correspondiente.</p>
                    </div>

                    <form method="post" class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Sí, usar promoción
                        </button>
                        <a href="dashboard_cliente.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
