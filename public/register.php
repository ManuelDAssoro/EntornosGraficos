<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
    <h2>Registro de Usuario</h2>
    <form action="form_validation.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="nombreUsuario" class="form-label">Email</label>
            <input type="email" class="form-control" id="nombreUsuario" name="nombreUsuario" value="<?= htmlspecialchars($_POST['nombreUsuario'] ?? '') ?>" required>
            <div class="invalid-feedback">
                Ingresa un email válido.
            </div>
        </div>
        <div class="mb-3">
            <label for="claveUsuario" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="claveUsuario" name="claveUsuario" minlength="8" required>
            <div class="invalid-feedback">
                La contraseña debe tener al menos 8 caracteres.
            </div>
        </div>
        <div class="mb-3">
            <label for="tipoUsuario" class="form-label">Tipo de Usuario</label>
            <select class="form-select" id="tipoUsuario" name="tipoUsuario" required>
                <option value="">Selecciona una opción</option>
                <option value="cliente" <?= ($_POST['tipoUsuario'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="dueno" <?= ($_POST['tipoUsuario'] ?? '') === 'dueno' ? 'selected' : '' ?>>Dueño de Local</option>
            </select>
            <div class="invalid-feedback">
                Selecciona el tipo de usuario.
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Registrarse</button>
    </form>

    <form action="index.php" method="get">
      <button type="submit" style="margin-top:20px; padding:10px 20px;">← Volver al inicio</button>
    </form>

</div>
<script src="js/bootstrap.bundle.min.js"></script>
<script>

(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
</body>
</html>