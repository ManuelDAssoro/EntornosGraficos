<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

$page_title = 'Nueva Promoción - Mi Shopping';
$custom_css = 'promocion-nueva.css';

$codUsuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT codlocal FROM locales WHERE codusuario = ?");
$stmt->execute([$codUsuario]);
$local = $stmt->fetch();

if (!$local) {
    die("No se encontró un local asociado a este usuario.");
}
$codLocal = $local['codlocal'];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['textopromo'] ?? '');
    $desde = $_POST['fechadesdepromo'] ?? '';
    $hasta = $_POST['fechahastapromo'] ?? '';
    $categoria = $_POST['categoriacliente'] ?? '';
    $dias = $_POST['diasSemana'] ?? [];
    
    $hoy = date('Y-m-d');

    if (empty($texto)) {
        $errores[] = "El texto de la promoción es obligatorio.";
    } elseif (strlen($texto) > 100) {
        $errores[] = "El texto de la promoción no puede superar los 100 caracteres.";
    }

    if (empty($desde)) {
        $errores[] = "La fecha de inicio es obligatoria.";
    } elseif ($desde < $hoy) {
        $errores[] = "La fecha de inicio no puede ser anterior a hoy.";
    }

    if (empty($hasta)) {
        $errores[] = "La fecha de fin es obligatoria.";
    } elseif ($hasta < $hoy) {
        $errores[] = "La fecha de fin no puede ser anterior a hoy.";
    }

    if (!empty($desde) && !empty($hasta) && $hasta < $desde) {
        $errores[] = "La fecha de fin debe ser mayor o igual a la fecha de inicio.";
    }

    if (empty($categoria)) {
        $errores[] = "Debe seleccionar una categoría de cliente.";
    }

    if (empty($dias)) {
        $errores[] = "Debe seleccionar al menos un día de la semana.";
    }

    if (empty($errores)) {
        try {
            $diasSemana = implode(',', $dias);
            
            $stmt = $pdo->prepare("
                INSERT INTO promociones (
                    textopromo, 
                    fechadesdepromo, 
                    fechahastapromo, 
                    categoriacliente, 
                    diassemana, 
                    estadopromo, 
                    codlocal
                ) VALUES (?, ?, ?, ?, ?, 'activa', ?)
            ");
            
            $resultado = $stmt->execute([
                $texto, 
                $desde, 
                $hasta, 
                $categoria, 
                $diasSemana, 
                $codLocal
            ]);

            if ($resultado) {
                header("Location: dashboard_dueno.php?mensaje=creada");
                exit;
            } else {
                $errores[] = "Error al crear la promoción. Intenta nuevamente.";
            }
        } catch (PDOException $e) {
            error_log("Error al crear promoción: " . $e->getMessage());
            $errores[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

include 'layout/header.php';
?>

<div class="container mt-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Nueva Promoción
                </h1>
                <p class="mb-0 mt-2">Crea una nueva promoción para atraer clientes a tu local</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="dashboard_dueno.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Menu
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" class="row g-3">
            <div class="col-md-12">
                <label class="form-label">
                    <i class="bi bi-megaphone"></i> Texto de la Promoción *
                </label>
                <input type="text" name="textopromo" value="<?= htmlspecialchars($_POST['textopromo'] ?? '') ?>" 
                       class="form-control" maxlength="100" required
                       placeholder="Ej: 20% de descuento en todos los productos">
                <div class="form-text">
                    <small class="text-muted">Máximo 100 caracteres. Actual: <span id="contador">0</span></small>
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-check"></i> Fecha de Inicio *
                </label>
                <input type="date" name="fechadesdepromo" value="<?= htmlspecialchars($_POST['fechadesdepromo'] ?? '') ?>" 
                       class="form-control" required min="<?= date('Y-m-d') ?>">
                <div class="form-text">
                    <small class="text-muted">Debe ser desde hoy en adelante</small>
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-x"></i> Fecha de Fin *
                </label>
                <input type="date" name="fechahastapromo" value="<?= htmlspecialchars($_POST['fechahastapromo'] ?? '') ?>" 
                       class="form-control" required min="<?= date('Y-m-d') ?>">
                <div class="form-text">
                    <small class="text-muted">Debe ser mayor o igual a la fecha de inicio</small>
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-person-badge"></i> Categoría de Cliente *
                </label>
                <select name="categoriacliente" class="form-select" required>
                    <option value="">Seleccionar categoría...</option>
                    <option value="inicial" <?= ($_POST['categoriacliente'] ?? '') === 'inicial' ? 'selected' : '' ?>>Inicial</option>
                    <option value="medium" <?= ($_POST['categoriacliente'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="premium" <?= ($_POST['categoriacliente'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-week"></i> Días de la Semana *
                </label>
                <div class="days-container">
                    <?php 
                    $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                    $diasSeleccionados = $_POST['diasSemana'] ?? [];
                    foreach ($dias as $dia): 
                    ?>
                        <div class="form-check" onclick="toggleCheckbox('<?= $dia ?>')">
                            <input class="form-check-input" type="checkbox" name="diasSemana[]" 
                                   value="<?= $dia ?>" id="<?= $dia ?>" 
                                   <?= in_array($dia, $diasSeleccionados) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $dia ?>">
                                <?= $dia ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text">
                    <small class="text-muted">Selecciona al menos un día</small>
                </div>
            </div>
            
            <div class="col-12">
                <hr class="my-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-save"></i> Crear Promoción
                    </button>
                    <a href="dashboard_dueno.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector('input[name="textopromo"]');
    const contador = document.getElementById('contador');
    
    function actualizarContador() {
        const longitud = input.value.length;
        contador.textContent = longitud;
        contador.style.color = longitud > 90 ? 'red' : (longitud > 80 ? 'orange' : 'inherit');
    }
    
    input.addEventListener('input', actualizarContador);
    actualizarContador();
});

function toggleCheckbox(dayId) {
    const checkbox = document.getElementById(dayId);
    checkbox.checked = !checkbox.checked;
    checkbox.dispatchEvent(new Event('change'));
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.form-check-input');
    const labels = document.querySelectorAll('.form-check-label');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    labels.forEach(label => {
        label.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const fechaDesde = document.querySelector('input[name="fechadesdepromo"]');
    const fechaHasta = document.querySelector('input[name="fechahastapromo"]');
    const hoy = new Date().toISOString().split('T')[0];
    
    fechaDesde.addEventListener('change', function() {
        if (this.value < hoy) {
            this.value = hoy;
        }
        fechaHasta.min = this.value;
        if (fechaHasta.value && fechaHasta.value < this.value) {
            fechaHasta.value = this.value;
        }
    });
    
    fechaHasta.addEventListener('change', function() {
        if (this.value < hoy) {
            this.value = hoy;
        }
        if (fechaDesde.value && this.value < fechaDesde.value) {
            this.value = fechaDesde.value;
        }
    });
});
</script>

<?php 
$custom_js = null;
include 'layout/footer.php'; 
?>
