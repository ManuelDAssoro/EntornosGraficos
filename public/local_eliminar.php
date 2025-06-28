<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$codLocal = $_GET['id'] ?? null;

if (!$codLocal || !ctype_digit($codLocal)) {
    die("ID de local inválido.");
}

// Obtener datos para mostrar nombre en confirmación
$stmt = $pdo->prepare("SELECT nombreLocal FROM locales WHERE codLocal = ?");
$stmt->execute([$codLocal]);
$local = $stmt->fetch();

if (!$local) {
    die("Local no encontrado.");
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Confirmación para eliminar
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'sí') {
        $stmt = $pdo->prepare("DELETE FROM locales WHERE codLocal = ?");
        $resultado = $stmt->execute([$codLocal]);

        if ($resultado) {
            header("Location: admin_locales.php?mensaje=eliminado");
            exit;
        } else {
            $error = "No se pudo eliminar el local.";
        }
    } else {
        // Si no confirma, redirige al listado
        header("Location: admin_locales.php");
        exit;
    }
}
?>

<?php include 'layout/header.php'; ?>

<h2>Eliminar Local</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<p>¿Estás seguro que quieres eliminar el local <strong><?= htmlspecialchars($local['nombreLocal']) ?></strong>?</p>

<form method="POST">
  <button type="submit" name="confirm" value="sí" class="btn btn-danger">Sí, eliminar</button>
  <button type="submit" name="confirm" value="no" class="btn btn-secondary">No, cancelar</button>
</form>

<?php include 'layout/footer.php'; ?>
