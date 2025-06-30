<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

// Set page variables for header
$page_title = 'Nueva Promoción - Mi Shopping';
$custom_css = 'promocion-nueva.css';

$codUsuario = $_SESSION['usuario_id'];

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

        header("Location: dashboard_dueno.php?mensaje=creada");
        exit;
    }
}

// Include header
include 'layout/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
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
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <form method="POST" class="row g-3">
            <div class="col-md-12">
                <label class="form-label">
                    <i class="bi bi-megaphone"></i> Texto de la Promoción
                </label>
                <textarea name="textoPromo" class="form-control" rows="3" 
                          placeholder="Ej: 20% de descuento en todos los productos" required></textarea>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-check"></i> Fecha de Inicio
                </label>
                <input type="date" name="fechaDesdePromo" class="form-control" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-x"></i> Fecha de Fin
                </label>
                <input type="date" name="fechaHastaPromo" class="form-control" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-person-badge"></i> Categoría de Cliente
                </label>
                <select name="categoriaCliente" class="form-select" required>
                    <option value="">Seleccionar categoría...</option>
                    <option value="Inicial">Inicial</option>
                    <option value="Medium">Medium</option>
                    <option value="Premium">Premium</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-calendar-week"></i> Días de la Semana
                </label>
                <div class="days-container">
                    <?php 
                    $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                    foreach ($dias as $dia): 
                    ?>
                        <div class="form-check" onclick="toggleCheckbox('<?= $dia ?>')">
                            <input class="form-check-input" type="checkbox" name="diasSemana[]" 
                                   value="<?= $dia ?>" id="<?= $dia ?>">
                            <label class="form-check-label" for="<?= $dia ?>">
                                <?= $dia ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
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
function toggleCheckbox(dayId) {
    const checkbox = document.getElementById(dayId);
    checkbox.checked = !checkbox.checked;
    
    // Trigger change event for any potential validation
    checkbox.dispatchEvent(new Event('change'));
}

// Prevent double-toggle when clicking directly on checkbox or label
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
</script>

<?php 
$custom_js = null; // We have inline JS above
include 'layout/footer.php'; 
?>
