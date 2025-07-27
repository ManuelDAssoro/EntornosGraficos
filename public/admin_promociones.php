<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$seccion = $_GET['seccion'] ?? 'pendientes';
$mensaje = $_GET['mensaje'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $promoId = $_POST['promoId'] ?? '';
    
    if ($action && $promoId) {
        try {
            switch ($action) {
                case 'aprobar':
                    $stmt = $pdo->prepare("UPDATE promociones SET estadopromo = 'activa' WHERE codpromo = ?");
                    $stmt->execute([$promoId]);
                    header("Location: admin_promociones.php?seccion=$seccion&mensaje=aprobada");
                    exit;
                    
                case 'rechazar':
                    $stmt = $pdo->prepare("UPDATE promociones SET estadopromo = 'rechazada' WHERE codpromo = ?");
                    $stmt->execute([$promoId]);
                    header("Location: admin_promociones.php?seccion=$seccion&mensaje=rechazada");
                    exit;
            }
        } catch (Exception $e) {
            $error = "Error al procesar la acción: " . $e->getMessage();
        }
    }
}

$where = [];
$params = [];

if ($seccion === 'pendientes') {
    $where[] = "p.estadopromo = 'pendiente'";
} elseif ($seccion === 'activas') {
    $where[] = "p.estadopromo = 'activa'";
} elseif ($seccion === 'rechazadas') {
    $where[] = "p.estadopromo = 'rechazada'";
}

$sql = "
    SELECT p.*, l.nombrelocal, u.nombreusuario as dueno_email
    FROM promociones p
    JOIN locales l ON p.codlocal = l.codlocal
    LEFT JOIN usuarios u ON l.codusuario = u.codusuario
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.fechadesdepromo DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$promociones = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) FROM promociones WHERE estadopromo = 'pendiente'");
$pendientes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM promociones WHERE estadopromo = 'activa'");
$activas = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM promociones WHERE estadopromo = 'rechazada'");
$rechazadas = $stmt->fetchColumn();

$mensajes_exito = [
    'aprobada' => 'La promoción fue aprobada exitosamente.',
    'rechazada' => 'La promoción fue rechazada.',
];

$page_title = 'Administrar Promociones - Mi Shopping';
include 'layout/header.php';
?>

<div class="container my-4">
    <div class="page-header mb-4">
        <h1><i class="bi bi-megaphone"></i> Administrar Promociones</h1>
        <p class="lead">Gestiona las promociones enviadas por los dueños de locales</p>
    </div>

    <?php if (isset($mensajes_exito[$mensaje])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $mensajes_exito[$mensaje] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= $pendientes ?></h3>
                    <p class="text-muted">Pendientes Aprobación</p>
                    <a href="?seccion=pendientes" class="btn btn-warning btn-sm">Ver Pendientes</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= $activas ?></h3>
                    <p class="text-muted">Promociones Activas</p>
                    <a href="?seccion=activas" class="btn btn-success btn-sm">Ver Activas</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger"><?= $rechazadas ?></h3>
                    <p class="text-muted">Promociones Rechazadas</p>
                    <a href="?seccion=rechazadas" class="btn btn-danger btn-sm">Ver Rechazadas</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?= $seccion === 'pendientes' ? 'active' : '' ?>" 
                       href="?seccion=pendientes">
                        Pendientes (<?= $pendientes ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $seccion === 'activas' ? 'active' : '' ?>" 
                       href="?seccion=activas">
                        Activas (<?= $activas ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $seccion === 'rechazadas' ? 'active' : '' ?>" 
                       href="?seccion=rechazadas">
                        Rechazadas (<?= $rechazadas ?>)
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <?php if (empty($promociones)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="mt-3">No hay promociones <?= $seccion ?></h5>
                    <p class="text-muted">No se encontraron promociones en esta categoría.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Local</th>
                                <th>Dueño</th>
                                <th>Promoción</th>
                                <th>Vigencia</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <?php if ($seccion === 'pendientes'): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promociones as $promo): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($promo['nombrelocal']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($promo['dueno_email']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($promo['textopromo']) ?>
                                        <?php if (!empty($promo['diassemana'])): ?>
                                            <br><small class="text-muted">Días: <?= htmlspecialchars($promo['diassemana']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('d/m/Y', strtotime($promo['fechadesdepromo'])) ?><br>
                                            hasta<br>
                                            <?= date('d/m/Y', strtotime($promo['fechahastapromo'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= ucfirst($promo['categoriacliente']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($promo['estadopromo'] === 'pendiente'): ?>
                                            <span class="badge bg-warning">Pendiente</span>
                                        <?php elseif ($promo['estadopromo'] === 'activa'): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php elseif ($promo['estadopromo'] === 'rechazada'): ?>
                                            <span class="badge bg-danger">Rechazada</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($seccion === 'pendientes'): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="aprobar">
                                                    <input type="hidden" name="promoId" value="<?= $promo['codpromo'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('¿Aprobar esta promoción?')">
                                                        <i class="bi bi-check-circle"></i> Aprobar
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="rechazar">
                                                    <input type="hidden" name="promoId" value="<?= $promo['codpromo'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('¿Rechazar esta promoción?')">
                                                        <i class="bi bi-x-circle"></i> Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>