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
  <?php require_once 'shared/navbar.php'; ?>

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