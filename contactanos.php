<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Contáctanos</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="https://doctoravanevet.com/wp-content/uploads/2020/04/Servicios-vectores-consulta-integral.png"
          alt="Logo" class="logo">
        <span>Veterinaria San Antón</span>
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Inicio</a>
          </li>
          <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <li class="nav-item dropdown d-flex align-items-center">
              <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Usuario" width="40" height="40"
                class="mr-2">
              <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <?php echo $_SESSION['usuario_nombre']; ?>
              </a>
              <div class="dropdown-menu" aria-labelledby="usuarioDropdown">
                <a class="dropdown-item" href="mis-turnos.php">Mis Turnos</a>
                <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
              </div>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="iniciar-sesion.php">Iniciar sesión</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="registrarse.php">Registrarse</a>
            </li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Secciones
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item active" href="contactanos.php">Contacto</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Barra de Navegación Secundaria -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
      <li class="breadcrumb-item"><a href="iniciar-sesion.php">Iniciar sesión</a></li>
      <li class="breadcrumb-item"><a href="registrarse.php">Registrarse</a></li>
      <li class="breadcrumb-item"><a href="servicios.php">Servicios</a></li>
      <li class="breadcrumb-item"><a href="nosotros.php">Nosotros</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contacto</li>
    </ol>
  </nav>

  <!-- Contenido Principal -->
  <div class="container mt-5">
    <h1>Contactanos y te asesoramos</h1>
    <p>Te ayudamos a seleccionar el plan adecuado para tu mascota</p>
    <h3>Asesoramiento comercial</h3>

    <!-- Bloques clicables -->
    <div class="row mt-4">
      <div class="col-md-6">
        <a href="https://wa.me/5493412754750" class="btn btn-success btn-block">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/640px-WhatsApp.svg.png"
            alt="WhatsApp" class="icon"> Contáctanos por WhatsApp
        </a>
      </div>
      <div class="col-md-6">
        <a href="tel:+5415673346" class="btn btn-info btn-block">
          <img
            src="https://c0.klipartz.com/pngpicture/421/683/gratis-png-iconos-de-computadora-telefonos-moviles-telefono-correo-electronico-telefonos-para-el-hogar-y-negocios-icono-de-telefono-thumbnail.png"
            alt="Teléfono" class="icon"> Llamar a nuestro teléfono
        </a>
      </div>
    </div>
  </div>

  <!-- Pie de Página -->
  <footer class="bg-light text-center text-lg-start mt-5">
    <div class="container p-4">
      <p>Teléfono de contacto: 115673346</p>
      <p>Mail: sananton24@gmail.com</p>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>