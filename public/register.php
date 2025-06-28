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
            <input type="email" class="form-control" id="nombreUsuario" name="nombreUsuario" required>
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
                <option value="cliente">Cliente</option>
                <option value="dueño">Dueño de Local</option>
            </select>
            <div class="invalid-feedback">
                Selecciona el tipo de usuario.
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Registrarse</button>
    </form>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
<script>

    // Validacion del lado del cliente

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