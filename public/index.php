<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entornos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">🛍️ Mi Shopping</a>
      <div class="d-flex">
        <?php if (!isset($_SESSION['usuario_id'])): ?>
          <a href="login.php" class="btn btn-outline-light me-2">Ingresar</a>
          <a href="register.php" class="btn btn-light">Registro</a>
        <?php else: ?>
          <a href="dashboard.php" class="btn btn-success me-2">Menu</a>
          <a href="logout.php" class="btn btn-outline-light">Salir</a>
        <?php endif; ?>
      </div>
    </div> 
  </nav>

  <div class="container text-center mt-5">
    <h1>Bienvenido a Mi Shopping</h1>
    <p class="lead">Encuentra los mejores productos al mejor precio.</p>
    
    <?php if (!isset($_SESSION['usuario_id'])): ?>
      <div class="row mt-5">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <i class="bi bi-search display-4 text-primary mb-3"></i>
              <h5>Explorar Promociones</h5>
              <p>Descubre todas las ofertas disponibles</p>
              <a href="buscar_descuentos.php" class="btn btn-primary">Ver Promociones</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <i class="bi bi-shop display-4 text-info mb-3"></i>
              <h5>Buscar por Local</h5>
              <p>Encuentra promociones específicas del local</p>
              <a href="buscar_por_codigo.php" class="btn btn-info">Buscar Local</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <i class="bi bi-newspaper display-4 text-success mb-3"></i>
              <h5>Novedades</h5>
              <p>Mantente al día con las últimas noticias</p>
              <a href="novedades.php" class="btn btn-success">Ver Novedades</a>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <p class="text-muted">¿Quieres usar las promociones?</p>
        <a href="register.php" class="btn btn-primary btn-lg">Registrarse</a>
        <span class="mx-2">o</span>
        <a href="login.php" class="btn btn-outline-primary btn-lg">Iniciar Sesión</a>
      </div>
    <?php else: ?>
      <a href="dashboard.php" class="btn btn-primary btn-lg mt-3">Ir al Menu</a>
    <?php endif; ?>
  </div>
</body>
</html>
