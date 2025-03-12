<?php
session_start()
  ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <?php if (isset($_SESSION['usuario_nombre'])): ?>
    <script>
      localStorage.setItem('usuario_id', '<?php echo $_SESSION['usuario_id']; ?>');
      localStorage.setItem('usuario_nombre', '<?php echo $_SESSION['usuario_nombre']; ?>');
    </script>
  <?php endif; ?>

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
            <a class="nav-link active" href="index.php">Inicio</a>
          </li>
          <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <li class="nav-item d-flex align-items-center">
              <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Usuario" width="40" height="40"
                class="mr-2">
              <span><?php echo $_SESSION['usuario_nombre']; ?></span>
              <a href="logout.php" class="btn btn-danger ml-3">Cerrar sesión</a>
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
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Secciones
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item" href="contactanos.php">Contacto</a>
            
            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
              <a class="dropdown-item" href="./vistaAdmin/gestionar-especialistas.php">Especialistas</a>
              <a class="dropdown-item" href="./vistaAdmin/gestionar-clientes.php">Gestionar clientes</a>
            <?php endif; ?>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Encabezado -->
   <?php if (isset($_SESSION['usuario_nombre'])): ?>
  <h2 class="text-center my-4" style="background-color: #a8d08d; width=100%">Vista de tipo:
    <?php echo $_SESSION['usuario_tipo']; ?>
  </h2>
  <?php endif; ?>

  <header class="container text-center my-4">
    <div class="row">
      <div class="col">
        <div class="header-images">
          <img
            src="https://cdn.goconqr.com/uploads/media/image/23700948/desktop_bcca9a25-c871-4ad6-bb7c-2cec7bffdcd8.jpg"
            alt="Mascota 1" class="img-fluid header-img">
          <img src="https://www.shutterstock.com/image-photo/vet-doctor-cute-domestic-dog-260nw-1955838685.jpg"
            alt="Mascota 2" class="img-fluid header-img">
          <img
            src="https://media.istockphoto.com/id/1353103116/es/foto/veterinario-examinando-lindo-perro-pug-y-gato-en-la-cl%C3%ADnica-primer-plano-d%C3%ADa-de-vacunaci%C3%B3n.jpg?s=612x612&w=0&k=20&c=y8RP8tBmuAApVU6Ga6OkizZoAnuHHjimBgtSRoAJBEI="
            alt="Mascota 3" class="img-fluid header-img">
        </div>
        <p class="lead">Somos la mejor veterinaria de tu ciudad, conoce nuestros servicios y trae a tu mascota</p>
      </div>
    </div>
  </header>

  <!-- Sección de Autogestión de Turnos -->
  <?php if (isset($_SESSION['usuario_nombre'])): ?>
    <section class="container text-center my-4">
      <h3>Autogestión de Turnos</h3>
      <p>Solicita y administra tus turnos de forma rápida y sencilla.</p>
      <a href="autogestion-turnos.php" class="btn btn-primary">Acceder</a>
    </section>
  <?php endif; ?>

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Somos la mejor veterinaria de tu ciudad, conoce nuestros servicios y trae a tu mascota</p>
    </div>
  </section>

  <!-- Sección de Bienvenida -->
  <section class="container my-4">
    <div class="row">
      <div class="col-md-8">
        <p>Desde su apertura en 1986 la veterinaria San Antón se dedicó a brindar servicios y productos de primerísima
          calidad para vos y tus mascotas, para ello contamos con los mejores profesionales y con equipamiento médico de
          última generación.</p>
      </div>
      <div class="col-md-4">
        <p>Encontranos en:</p>
        <img
          src="https://st2.depositphotos.com/4242631/6430/v/450/depositphotos_64302369-stock-illustration-map-icon-with-pin-pointer.jpg"
          alt="Mapa" class="img-fluid">
      </div>
    </div>
  </section>

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>