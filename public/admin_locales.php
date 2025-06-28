<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

// Captura filtros (todos opcionales)
$nombre = trim($_GET['nombre'] ?? '');
$ubicacion = trim($_GET['ubicacion'] ?? '');
$rubro = trim($_GET['rubro'] ?? '');

// Armado de WHERE dinámico
$where = [];
$params = [];

if ($nombre !== '') {
    $where[] = "l.nombreLocal LIKE ?";
    $params[] = "%$nombre%";
}
if ($ubicacion !== '') {
    $where[] = "l.ubicacionLocal LIKE ?";
    $params[] = "%$ubicacion%";
}
if ($rubro !== '') {
    $where[] = "l.rubroLocal LIKE ?";
    $params[] = "%$rubro%";
}

// Consulta SQL con join para mostrar nombre del dueño
$sql = "
    SELECT l.*, u.nombreUsuario
    FROM locales l
    LEFT JOIN usuarios u ON l.codUsuario = u.codUsuario
";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY l.nombreLocal ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$locales = $stmt->fetchAll();

// Mensajes de éxito
$mensaje = $_GET['mensaje'] ?? '';
$mensajes_exito = [
    'creado' => 'El local fue creado correctamente.',
    'editado' => 'El local fue editado correctamente.',
    'eliminado' => 'El local fue eliminado correctamente.'
];
?>

<?php include 'layout/header.php'; ?>

<h2>Administrar Locales</h2>

<?php if (isset($mensajes_exito[$mensaje])): ?>
  <div class="alert alert-success">
    <?= $mensajes_exito[$mensaje] ?>
  </div>
<?php endif; ?>

<!-- Filtro de búsqueda -->
<form method="GET" class="row g-3 mb-4">
  <div class="col-md-3">
    <input type="text" name="nombre" class="form-control" placeholder="Nombre del Local" value="<?= htmlspecialchars($nombre) ?>">
  </div>
  <div class="col-md-3">
    <input type="text" name="ubicacion" class="form-control" placeholder="Ubicación" value="<?= htmlspecialchars($ubicacion) ?>">
  </div>
  <div class="col-md-3">
    <input type="text" name="rubro" class="form-control" placeholder="Rubro" value="<?= htmlspecialchars($rubro) ?>">
  </div>
  <div class="col-md-3 d-flex align-items-center">
    <button type="submit" class="btn btn-primary me-2">Buscar</button>
    <a href="admin_locales.php" class="btn btn-secondary">Limpiar</a>
  </div>
</form>

<!-- Botón de alta -->
<div class="mb-3">
  <a href="local_nuevo.php" class="btn btn-success">➕ Nuevo Local</a>
</div>

<!-- Tabla de resultados -->
<table class="table table-bordered table-striped">
  <thead class="table-dark">
    <tr>
      <th>Nombre</th>
      <th>Ubicación</th>
      <th>Rubro</th>
      <th>Dueño</th>
      <th style="width: 180px;">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($locales) > 0): ?>
      <?php foreach ($locales as $local): ?>
        <tr>
          <td><?= htmlspecialchars($local['nombreLocal']) ?></td>
          <td><?= htmlspecialchars($local['ubicacionLocal']) ?></td>
          <td><?= htmlspecialchars($local['rubroLocal']) ?></td>
          <td><?= htmlspecialchars($local['nombreUsuario']) ?: '<em>Sin asignar</em>' ?></td>
          <td>
            <a href="local_editar.php?id=<?= $local['codLocal'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
            <a href="local_eliminar.php?id=<?= $local['codLocal'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro que deseas eliminar este local?')">🗑️ Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="5" class="text-center">No se encontraron locales.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include 'layout/footer.php'; ?>
