<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

// Traer usuarios tipo "Dueño"
$stmt = $pdo->query("SELECT codUsuario, nombreUsuario FROM usuarios WHERE tipoUsuario = 'Dueño' ORDER BY nombreUsuario");
$duenos = $stmt->fetchAll();

// Inicialización
$errores = [];
$nombreLocal = '';
$ubicacionLocal = '';
$rubroLocal = '';
$codUsuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de datos
    $nombreLocal = trim($_POST['nombreLocal'] ?? '');
    $ubicacionLocal = trim($_POST['ubicacionLocal'] ?? '');
    $rubroLocal = trim($_POST['rubroLocal'] ?? '');
    $codUsuario = trim($_POST['codUsuario'] ?? '');

    // Validación
    if (empty($nombreLocal)) {
        $errores[] = "El nombre del local es obligatorio.";
    }

    if (!empty($codUsuario) && !ctype_digit($codUsuario)) {
        $errores[] = "Selecciona un dueño válido.";
    }

    // Si no hay errores → insertar
    if (empty($errores)) {
        $stmt = $pdo->prepare(
            "INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario)
             VALUES (?, ?, ?, ?)"
        );
        $exito = $stmt->execute([
            $nombreLocal,
            $ubicacionLocal ?: null,
            $rubroLocal ?: null,
            $codUsuario ?: null
        ]);

        if ($exito) {
            header("Location: admin_locales.php?mensaje=creado");
            exit;
        } else {
            $errores[] = "Ocurrió un error al guardar el local.";
        }
    }
}
?>

<?php include 'layout/header.php'; ?>

<h2>Nuevo Local</h2>

<?php if (!empty($errores)): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errores as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <label for="nombreLocal" class="form-label">Nombre del Local *</label>
    <input type="text" class="form-control" id="nombreLocal" name="nombreLocal" value="<?= htmlspecialchars($nombreLocal) ?>" required>
  </div>
  <div class="col-md-6">
    <label for="ubicacionLocal" class="form-label">Ubicación</label>
    <input type="text" class="form-control" id="ubicacionLocal" name="ubicacionLocal" value="<?= htmlspecialchars($ubicacionLocal) ?>">
  </div>
  <div class="col-md-6">
    <label for="rubroLocal" class="form-label">Rubro</label>
    <input type="text" class="form-control" id="rubroLocal" name="rubroLocal" value="<?= htmlspecialchars($rubroLocal) ?>">
  </div>
  <div class="col-md-6">
    <label for="codUsuario" class="form-label">Dueño del Local</label>
    <select name="codUsuario" id="codUsuario" class="form-select">
      <option value="">-- Sin asignar --</option>
      <?php foreach ($duenos as $dueno): ?>
        <option value="<?= $dueno['codUsuario'] ?>" <?= $codUsuario == $dueno['codUsuario'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($dueno['nombreUsuario']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="admin_locales.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include 'layout/footer.php'; ?>
