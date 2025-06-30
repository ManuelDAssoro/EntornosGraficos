<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

$codUsuario = $_SESSION['codUsuario'];

$stmt = $pdo->prepare("SELECT codLocal FROM locales WHERE codUsuario = ?");
$stmt->execute([$codUsuario]);
$local = $stmt->fetch();

if (!$local) {
    die("No se encontró un local asociado a este usuario.");
}
$codLocal = $local['codLocal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['textoPromo'] ?? '');
    $desde = $_POST['fechaDesdePromo'] ?? '';
    $hasta = $_POST['fechaHastaPromo'] ?? '';
    $categoria = $_POST['categoriaCliente'] ?? '';
    $dias = $_POST['diasSemana'] ?? [];

    if ($texto && $desde && $hasta && $categoria && count($dias) > 0) {
        $diasSemana = implode(',', $dias);
        $stmt = $pdo->prepare("INSERT INTO promociones 
            (textoPromo, fechaDesdePromo, fechaHastaPromo, categoriaCliente, diasSemana, estadoPromo, codLocal)
            VALUES (?, ?, ?, ?, ?, 'pendiente', ?)");
        $stmt->execute([$texto, $desde, $hasta, $categoria, $diasSemana, $codLocal]);

        header("Location: dueno_promociones.php?mensaje=creada");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Promoción</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h1><i class="bi bi-plus-circle"></i> Nueva Promoción</h1>

    <form method="POST" class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Texto de la Promoción</label>
            <input type="text" name="textoPromo" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Desde</label>
            <input type="date" name="fechaDesdePromo" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Hasta</label>
            <input type="date" name="fechaHastaPromo" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Categoría de Cliente</label>
            <select name="categoriaCliente" class="form-select" required>
                <option value="Inicial">Inicial</option>
                <option value="Medium">Medium</option>
                <option value="Premium">Premium</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Días de la Semana</label><br>
            <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="diasSemana[]" value="<?= $dia ?>" id="<?= $dia ?>">
                    <label class="form-check-label" for="<?= $dia ?>"><?= $dia ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-12">
            <button class="btn btn-success"><i class="bi bi-save"></i> Guardar Promoción</button>
            <a href="dueno_promociones.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
