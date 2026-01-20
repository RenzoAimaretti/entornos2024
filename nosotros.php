<?php
session_start()
  ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Nosotros</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <?php require_once 'shared/navbar.php'; ?>

  <!-- Contenido Principal -->
  <div class="container mt-5 mb-4">
    <h1>Nosotros</h1>
    <div class="row">
      <div class="col-md-6">
        <h2>Con vos, todo el día</h2>
        <p>Brindamos soluciones integrales a cada evento que se presente durante la vida de tus mascotas.</p>
      </div>
      <div class="col-md-6">
        <h2>Somos San Antón</h2>
        <p>Un servicio innovador de cuidados veterinarios, que busca prevenir y solucionar todos los aspectos
          relacionados con la salud de nuestras mascotas. Creamos Mascota24 para aportar nuestro esfuerzo y mejorar la
          calidad de vida de nuestros amigos de cuatro patas.</p>
      </div>
    </div>
  </div>

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php require_once 'shared/footer.php'; ?>
</body>

</html>