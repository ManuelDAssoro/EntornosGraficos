<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';
require_once 'categoria_functions.php';

$codUsuarioDueno = $_SESSION['usuario_id'];
$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    $idUso = $_POST['id_uso'] ?? '';
    $comentario = $_POST['comentario'] ?? '';
    
    if ($accion && $idUso) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                SELECT up.*, p.codlocal, l.codusuario as dueno_id, u.nombreusuario as cliente_nombre
                FROM uso_promociones up
                JOIN promociones p ON up.codpromo = p.codpromo
                JOIN locales l ON p.codlocal = l.codlocal
                JOIN usuarios u ON up.codusuario = u.codusuario
                WHERE up.id = ? AND l.codusuario = ? AND up.estado = 'pendiente'
            ");
            $stmt->execute([$idUso, $codUsuarioDueno]);
            $uso = $stmt->fetch();
            
            if (!$uso) {
                throw new Exception("No tienes permisos para procesar este uso o ya fue procesado.");
            }
            
            $nuevoEstado = ($accion === 'aprobar') ? 'aceptada' : 'rechazada';
            
            $stmt = $pdo->prepare("
                UPDATE uso_promociones 
                SET estado = ?, fecha_aprobacion = CURRENT_DATE, comentario_dueno = ?
                WHERE id = ?
            ");
            $stmt->execute([$nuevoEstado, $comentario, $idUso]);
            
            if ($nuevoEstado === 'aceptada') {
                actualizarCategoriaCliente($uso['codusuario'], $pdo);
            }
            
            $pdo->commit();
            
            $mensajeTexto = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
            header("Location: aprobar_promociones.php?mensaje=uso_{$mensajeTexto}");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$usosPendientes = [];
try {
    $stmt = $pdo->prepare("
        SELECT up.*, p.textopromo, p.codpromo, l.nombrelocal, l.codlocal, u.nombreusuario as cliente_nombre
        FROM uso_promociones up
        JOIN promociones p ON up.codpromo = p.codpromo
        JOIN locales l ON p.codlocal = l.codlocal
        JOIN usuarios u ON up.codusuario = u.codusuario
        WHERE l.codusuario = ? AND up.estado = 'pendiente'
        ORDER BY up.fecha_uso ASC
    ");
    $stmt->execute([$codUsuarioDueno]);
    $usosPendientes = $stmt->fetchAll();
} catch (PDOException $e) {
    $usosPendientes = [];
}

$historialAprobaciones = [];
try {
    $stmt = $pdo->prepare("
        SELECT up.*, p.textopromo, l.nombrelocal, u.nombreusuario as cliente_nombre
        FROM uso_promociones up
        JOIN promociones p ON up.codpromo = p.codpromo
        JOIN locales l ON p.codlocal = l.codlocal
        JOIN usuarios u ON up.codusuario = u.codusuario
        WHERE l.codusuario = ? AND up.estado IN ('aceptada', 'rechazada') AND up.fecha_aprobacion IS NOT NULL
        ORDER BY up.fecha_aprobacion DESC
        LIMIT 10
    ");
    $stmt->execute([$codUsuarioDueno]);
    $historialAprobaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    $historialAprobaciones = [];
}

$mensajes = [
    'uso_aprobado' => 'Uso de promoción aprobado exitosamente.',
    'uso_rechazado' => 'Uso de promoción rechazado exitosamente.'
];

$page_title = 'Aprobar Promociones - Mi Shopping';
include 'layout/header.php';
?>

<div class="hero-section bg-primary text-white">
    <div class="container">
        <div class="row align-items-center py-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="bi bi-check-circle"></i> Aprobar Promociones
                </h1>
                <p class="lead mb-0">Gestiona las solicitudes de uso de promociones de tus locales</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-primary fs-6">
                    <i class="bi bi-hourglass-split"></i> <?= count($usosPendientes) ?> pendientes
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($mensaje && isset($mensajes[$mensaje])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $mensajes[$mensaje] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-hourglass-split"></i> Usos Pendientes de Aprobación
                        <span class="badge bg-dark"><?= count($usosPendientes) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($usosPendientes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Promoción</th>
                                        <th>Local</th>
                                        <th>Fecha de Uso</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usosPendientes as $index => $uso): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($uso['cliente_nombre']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($uso['textopromo']) ?>
                                                <br><small class="text-muted">Código: <?= htmlspecialchars($uso['codpromo']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($uso['nombrelocal']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($uso['fecha_uso'])) ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-sm me-2 btn-aprobar" data-index="<?= $index ?>">
                                                    <i class="bi bi-check-lg"></i> Aprobar
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-rechazar" data-index="<?= $index ?>">
                                                    <i class="bi bi-x-lg"></i> Rechazar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">No hay usos pendientes</h4>
                            <p class="text-muted">Todos los usos de promociones han sido procesados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($historialAprobaciones) > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Historial Reciente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Promoción</th>
                                        <th>Local</th>
                                        <th>Fecha Uso</th>
                                        <th>Fecha Decisión</th>
                                        <th>Estado</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historialAprobaciones as $historial): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($historial['cliente_nombre']) ?></td>
                                            <td><?= htmlspecialchars($historial['textopromo']) ?></td>
                                            <td><?= htmlspecialchars($historial['nombrelocal']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($historial['fecha_uso'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($historial['fecha_aprobacion'])) ?></td>
                                            <td>
                                                <?php if ($historial['estado'] === 'aceptada'): ?>
                                                    <span class="badge bg-success">Aprobada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rechazada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($historial['comentario_dueno'])): ?>
                                                    <small><?= htmlspecialchars($historial['comentario_dueno']) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">Sin comentarios</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalAprobacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_uso" id="idUso">
                    <input type="hidden" name="accion" id="accion">
                    
                    <div class="mb-3">
                        <strong>Cliente:</strong> <span id="clienteNombre"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Promoción:</strong> <span id="promocionTexto"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario (opcional):</label>
                        <textarea class="form-control" name="comentario" id="comentario" rows="3" 
                                  placeholder="Agregar un comentario sobre la decisión..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" id="btnConfirmar"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const usosPendientes = <?= json_encode($usosPendientes) ?>;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-aprobar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            const uso = usosPendientes[index];
            mostrarModalAprobacion(uso.id, uso.cliente_nombre, uso.textopromo, 'aprobar');
        });
    });
    
    document.querySelectorAll('.btn-rechazar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            const uso = usosPendientes[index];
            mostrarModalAprobacion(uso.id, uso.cliente_nombre, uso.textopromo, 'rechazar');
        });
    });
});

function mostrarModalAprobacion(idUso, clienteNombre, promocionTexto, accion) {
    document.getElementById('idUso').value = idUso;
    document.getElementById('accion').value = accion;
    document.getElementById('clienteNombre').textContent = clienteNombre;
    document.getElementById('promocionTexto').textContent = promocionTexto;
    
    const modal = document.getElementById('modalAprobacion');
    const title = document.getElementById('modalTitle');
    const btnConfirmar = document.getElementById('btnConfirmar');
    
    if (accion === 'aprobar') {
        title.textContent = 'Aprobar Uso de Promoción';
        btnConfirmar.textContent = 'Aprobar';
        btnConfirmar.className = 'btn btn-success';
    } else {
        title.textContent = 'Rechazar Uso de Promoción';
        btnConfirmar.textContent = 'Rechazar';
        btnConfirmar.className = 'btn btn-danger';
    }
    
    new bootstrap.Modal(modal).show();
}
</script>

<?php include 'layout/footer.php'; ?>
