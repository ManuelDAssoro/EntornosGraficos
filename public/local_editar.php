<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$codLocal = $_GET['id'] ?? null;

if (!$codLocal || !ctype_digit($codLocal)) {
    die("ID de local inválido.");
}

// Obtener datos actuales del local
$stmt = $pdo->prepare("SELECT * FROM locales WHERE codLocal = ?");
$stmt->execute([$codLocal]);
$local = $stmt->fetch();

if (!$local) {
    die("Local no encontrado.");
}

// Traer usuarios tipo "Dueño"
$duenosStmt = $pdo->query("SELECT codUsuario, nombreUsuario FROM usuarios WHERE tipoUsuario = 'Dueño' ORDER BY nombreUsuario");
$duenos = $duenosStmt->fetchAll();

// Inicializar variables con datos actuales
$nombreLocal = $local['nombreLocal'];
$ubicacionLocal = $local['ubicacionLocal'];
$rubroLocal = $local['rubroLocal'];
$codUsuario = $local['codUsuario'];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del form
    $nombreLocal = trim($_POST['nombreLocal'] ?? '');
    $ubicacionLocal = trim($_POST['ubicacionLocal'] ?? '');
    $rubroLocal = trim($_POST['rubroLocal'] ?? '');
    $codUsuario = trim($_POST['codUsuario'] ?? '');

    // Validar
    if (empty($nombreLocal)) {
        $errores[] = "El nombre del local es obligatorio.";
    }

    if (!empty($codUsuario) && !ctype_digit($codUsuario)) {
        $errores[] = "Selecciona un dueño válido.";
    }

    // Si todo ok → actualizar
    if (empty($errores)) {
        $stmt = $pdo->prepare("
            UPDATE locales 
            SET nombreLocal = ?, ubicacionLocal = ?, rubroLocal = ?, codUsuario = ?
            WHERE codLocal = ?
        ");

        $resultado = $stmt->execute([
            $nombreLocal,
            $ubicacionLocal ?: null,
            $rubroLocal ?: null,
            $codUsuario ?: null,
            $codLocal
        ]);

        if ($resultado) {
            header("Location: admin_locales.php?mensaje=editado");
            exit;
        } else {
            $errores[] = "No se pudo actualizar el local.";
        }
    }
}
?>

<?php include 'layout/header.php'; ?>

<h2>Editar Local</h2>

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
    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="admin_locales.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include 'layout/footer.php'; ?>
