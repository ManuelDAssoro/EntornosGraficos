<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
            <label>Contraseña</label>
            <div class="input-group">
              <input type="password" name="claveUsuario" id="claveUsuario" class="form-control" required />
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-lock"></i>
              </button>
              <div class="invalid-feedback">
                La contraseña debe tener al menos 8 caracteres.
            </div>
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

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordField = document.getElementById('claveUsuario');
      const icon = this.querySelector('i');
      const isPassword = passwordField.type === 'password';
      passwordField.type = isPassword ? 'text' : 'password';

      // Alternar icono entre candado cerrado y abierto
      icon.classList.toggle('bi-lock');
      icon.classList.toggle('bi-unlock');
    });
  </script>
</body>
</html>