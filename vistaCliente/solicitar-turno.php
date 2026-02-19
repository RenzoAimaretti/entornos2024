<?php
session_start();
$ruta_base = "../";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <title>Veterinaria San Antón - Solicitar Turno</title>
  <?php require_once '../shared/head.php'; ?>
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">

    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-5">
      <h1 class="mb-0 font-weight-bold">Solicitar Turno</h1>
      <p class="mb-0 mt-2" style="font-size: 1.1rem; opacity: 0.9;">
        ¿Cómo deseas buscar tu turno hoy?
      </p>
    </div>

    <div class="row justify-content-center">

      <div class="col-md-5 mb-4">
        <a href="solicitar-turno-profesional.php" class="card action-card shadow-sm h-100 text-center py-5">
          <div class="card-body">
            <i class="fas fa-user-md icon-large"></i>
            <h3 class="card-title mt-2">Por Profesional</h3>
            <p class="card-text text-muted px-4">
              Elige a tu veterinario de confianza y visualiza su disponibilidad horaria.
            </p>
            <span class="btn btn-outline-success rounded-pill mt-3 font-weight-bold px-4">
              Buscar Profesional
            </span>
          </div>
        </a>
      </div>

      <div class="col-md-5 mb-4">
        <a href="solicitar-turno-servicio.php" class="card action-card shadow-sm h-100 text-center py-5">
          <div class="card-body">
            <i class="fas fa-briefcase-medical icon-large"></i>
            <h3 class="card-title mt-2">Por Servicio</h3>
            <p class="card-text text-muted px-4">
              Selecciona el tipo de atención (Consulta, Vacunación, etc.) y mira los horarios disponibles.
            </p>
            <span class="btn btn-outline-info rounded-pill mt-3 font-weight-bold px-4"
              style="color: #00897b; border-color: #00897b;">
              Ver Servicios
            </span>
          </div>
        </a>
      </div>

    </div>

    <div class="text-center mt-4">
      <a href="autogestion-turnos.php" class="btn btn-outline-secondary btn-back shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Volver al menú anterior
      </a>
    </div>

  </div>

  <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

  <?php require_once '../shared/footer.php'; ?>
</body>

</html>