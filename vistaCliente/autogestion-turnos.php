<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Autogestión de Turnos</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <?php require_once '../shared/navbar.php'; ?>

  <!-- Sección de Autogestión de Turnos -->
  <section class="container text-center my-4">
    <h3>Autogestión de Turnos</h3>
    <img src="https://pacientes.grupocentro.ar/pac/res/turnos.png" alt="Calendario" class="img-fluid my-3">
    <a href="solicitar-turno.php" class="btn btn-primary">Solicitar nuevo turno</a>
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