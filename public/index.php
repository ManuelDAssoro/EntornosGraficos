<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entornos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">🛍️ Mi Shopping</a>
      <div class="d-flex">
        <?php if (!isset($_SESSION['usuario_id'])): ?>
          <a href="login.php" class="btn btn-outline-light me-2">Login</a>
          <a href="register.php" class="btn btn-light">Registro</a>
        <?php else: ?>
          <a href="dashboard.php" class="btn btn-success me-2">Dashboard</a>
          <a href="logout.php" class="btn btn-outline-light">Salir</a>
        <?php endif; ?>
      </div>
    </div> 
  </nav>

  <div class="container text-center mt-5">
    <h1>Bienvenido a Mi Shopping</h1>
    <p class="lead">Encuentra los mejores productos al mejor precio.</p>
    <a href="register.php" class="btn btn-primary btn-lg mt-3">Comenzar ahora</a>
  </div>
</body>
</html>
