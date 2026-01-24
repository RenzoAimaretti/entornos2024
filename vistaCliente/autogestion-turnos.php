<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Autogestión</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="../styles.css" rel="stylesheet">
  <style>
    /* Estilos para las tarjetas de acción */
    .action-card {
      border: none;
      transition: all 0.3s ease;
      text-decoration: none;
      /* Quitar subrayado del link */
      color: inherit;
    }

    .action-card:hover {
      transform: translateY(-5px);
      /* Efecto de flotar hacia arriba */
      box-shadow: 0 10px 20px rgba(0, 137, 123, 0.2) !important;
      text-decoration: none;
      color: #00897b;
    }

    .action-card:hover .icon-large {
      transform: scale(1.1);
    }

    .icon-large {
      font-size: 4rem;
      color: #00897b;
      transition: transform 0.3s ease;
      margin-bottom: 15px;
    }

    .card-title {
      font-weight: bold;
      font-size: 1.25rem;
    }
  </style>
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">

    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-5">
      <h1 class="mb-0 font-weight-bold">Autogestión de Turnos</h1>
      <p class="mb-0 mt-2" style="font-size: 1.1rem; opacity: 0.9;">
        Gestiona tus citas de forma rápida y sencilla
      </p>
    </div>

    <div class="row justify-content-center">

      <div class="col-md-5 mb-4">
        <a href="solicitar-turno.php" class="card action-card shadow-sm h-100 text-center py-5">
          <div class="card-body">
            <i class="fas fa-calendar-plus icon-large"></i>
            <h3 class="card-title mt-3">Solicitar Nuevo Turno</h3>
            <p class="card-text text-muted px-4">
              Agenda una nueva cita para tu mascota con nuestros especialistas.
            </p>
            <span class="btn btn-outline-success rounded-pill mt-3 font-weight-bold px-4">
              Comenzar
            </span>
          </div>
        </a>
      </div>

      <div class="col-md-5 mb-4">
        <a href="mis-turnos.php" class="card action-card shadow-sm h-100 text-center py-5">
          <div class="card-body">
            <i class="fas fa-list-alt icon-large"></i>
            <h3 class="card-title mt-3">Mis Turnos Agendados</h3>
            <p class="card-text text-muted px-4">
              Revisa el historial, consulta horarios o cancela tus citas pendientes.
            </p>
            <span class="btn btn-outline-info rounded-pill mt-3 font-weight-bold px-4"
              style="color: #00897b; border-color: #00897b;">
              Ver Listado
            </span>
          </div>
        </a>
      </div>

    </div>

    <div class="row mt-4">
      <div class="col-12 text-center">
        <div class="alert alert-light border shadow-sm d-inline-block px-5">
          <i class="fas fa-headset mr-2" style="color: #00897b;"></i>
          ¿Necesitas ayuda con un turno urgente?
          <a href="../contactanos.php" class="font-weight-bold ml-1" style="color: #00897b;">Contáctanos</a>
        </div>
      </div>
    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <?php require_once '../shared/footer.php'; ?>
</body>

</html>