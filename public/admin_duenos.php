<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';


$email = trim($_GET['nombreUsuario'] ?? '');


$where = ["tipoUsuario = 'dueño'", "estado = 'pendiente'"];
$params = [];

if ($email !== '') {
    $where[] = "nombreUsuario LIKE ?";
    $params[] = "%$email%";
}

$sql = "SELECT * FROM usuarios WHERE " . implode(" AND ", $where) . " ORDER BY codUsuario DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$duenos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Dueños - Mi Shopping</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard_admin.php">
            <i class="bi bi-shop"></i> Mi Shopping
        </a>
    </div>
</nav>

<div class="container mt-4">
    <h1><i class="bi bi-person-lines-fill"></i> Solicitudes de Dueños</h1>

    <?php
$mensaje = $_GET['mensaje'] ?? '';
$mensajes_exito = [
    'aprobado' => 'El dueño fue aprobado y notificado por email.',
    'rechazado' => 'El dueño fue rechazado y notificado por email.'
];

if (isset($mensajes_exito[$mensaje])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $mensajes_exito[$mensaje] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


    <p>Gestiona las cuentas de dueños que están pendientes de aprobación.</p>

    <!-- Filtros -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="email" class="form-label">Email del Dueño</label>
            <input type="text" class="form-control" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
        </div>
        <div class="col-md-6 d-flex align-items-end gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
            <a href="admin_duenos.php" class="btn btn-secondary"><i class="bi bi-x"></i> Limpiar</a>
        </div>
    </form>

    <!-- Tabla de resultados -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Email</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($duenos) > 0): ?>
                    <?php foreach ($duenos as $dueno): ?>
                        <tr>
                            <td><?= htmlspecialchars($dueno['nombreUsuario']) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= $dueno['estado'] ?></span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="aprobar_dueno.php?id=<?= $dueno['codUsuario'] ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-circle"></i> Aprobar
                                    </a>
                                    <a href="rechazar_dueno.php?id=<?= $dueno['codUsuario'] ?>" class="btn btn-danger btn-sm">
                                        <i class="bi bi-x-circle"></i> Rechazar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-1"></i>
                            <h5 class="mt-3">No hay solicitudes pendientes</h5>
                            <p>Todos los dueños han sido procesados.</p>
                        </td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
