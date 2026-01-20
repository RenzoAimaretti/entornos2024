<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles.css" rel="stylesheet">
  <style>
    .card img {
      width: 100px;
      height: 100px;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <!-- Navegación -->
  <?php require_once '../shared/navbar.php'; ?>

  <!-- Sección de Solicitar Turno -->
  <section class="container text-center my-4">
    <h3>Solicitar turno</h3>
    <div class="row justify-content-center">
      <div class="col-md-4 d-flex justify-content-center">
        <div class="card mb-3">
          <div class="card-body">
            <a href="solicitar-turno-profesional.php" class="btn btn-light btn-block">
              <img src="https://images.emojiterra.com/google/android-oreo/512px/1f468-1f4bc.png" alt="Profesional"
                class="img-fluid mb-2">
              <p>Profesional</p>
            </a>
          </div>
        </div>
      </div>
      <div class="col-md-4 d-flex justify-content-center">
        <div class="card mb-3">
          <div class="card-body">
            <a href="solicitar-turno-servicio.php" class="btn btn-light btn-block">
              <img
                src="https://w7.pngwing.com/pngs/142/988/png-transparent-computer-icons-medical-chart-smiley-emoticon-mask.png"
                alt="Servicio" class="img-fluid mb-2">
              <p>Servicio</p>
            </a>
          </div>
        </div>
      </div>
    </div>
    <a href="autogestion-turnos.php" class="btn btn-secondary mt-3">Volver</a>
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
  <?php require_once '../shared/footer.php'; ?>
</body>

</html>