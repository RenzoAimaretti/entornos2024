<?php require_once '../shared/logica_mis_turnos.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Turnos - Veterinaria San Antón</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="../styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">
    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
      <h1 class="mb-0 font-weight-bold">Mis Turnos</h1>
      <p class="mb-0 mt-1" style="opacity: 0.9;">Gestiona tus próximas visitas a la veterinaria</p>
    </div>

    <?php if (isset($_SESSION['cancelacion_status']) && $_SESSION['cancelacion_status'] === 'ok'): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <strong>¡Éxito!</strong> El turno fue cancelado y se envió un correo de
        confirmación.
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php unset($_SESSION['cancelacion_status']); ?>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <div class="btn-group mb-3 mb-md-0 shadow-sm" role="group">
        <a href="mis-turnos.php?filter=upcoming" class="btn <?php echo $btnUpcomingClass; ?>">
          <i class="fas fa-calendar-alt mr-2"></i> Próximos
        </a>
        <a href="mis-turnos.php?filter=completed" class="btn <?php echo $btnCompletedClass; ?>">
          <i class="fas fa-history mr-2"></i> Historial
        </a>
      </div>
      <a href="solicitar-turno.php" class="btn btn-teal shadow-sm rounded-pill px-4 font-weight-bold">
        <i class="fas fa-plus-circle mr-2"></i> Solicitar Nuevo Turno
      </a>
    </div>

    <?php if (count($turnos) > 0): ?>
      <div class="row">
        <?php foreach ($turnos as $turno): ?>
          <?php
          $cardClass = ($filter === 'upcoming') ? 'card-upcoming' : 'card-completed';
          $fechaFormateada = date('d/m/Y', strtotime($turno['fecha']));
          $horaFormateada = date('H:i', strtotime($turno['fecha']));
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100 <?php echo $cardClass; ?>">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <h5 class="card-title mb-0"><?php echo htmlspecialchars($turno['servicio']); ?></h5>
                  <?php if ($filter === 'upcoming'): ?>
                    <span class="badge badge-success px-2 py-1">Activo</span>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1">Finalizado</span>
                  <?php endif; ?>
                </div>
                <hr>
                <div class="info-row"><i class="fas fa-calendar-day"></i> <strong>Fecha:</strong>
                  <?php echo $fechaFormateada; ?></div>
                <div class="info-row"><i class="fas fa-clock"></i> <strong>Hora:</strong> <?php echo $horaFormateada; ?> hs
                </div>
                <div class="info-row"><i class="fas fa-paw"></i> <strong>Mascota:</strong>
                  <?php echo htmlspecialchars($turno['mascota']); ?></div>
                <div class="info-row"><i class="fas fa-user-md"></i> <strong>Prof:</strong>
                  <?php echo htmlspecialchars($turno['profesional']); ?></div>

                <?php if ($filter === 'upcoming'): ?>
                  <div class="mt-4 text-right">
                    <button class="btn btn-outline-danger btn-sm cancelar-turno rounded-pill"
                      data-id="<?php echo $turno['id']; ?>">
                      <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <div class="mb-3"><i class="fas fa-calendar-times fa-4x text-muted" style="opacity: 0.3;"></i></div>
        <h4 class="text-muted">No hay turnos <?php echo ($filter === 'upcoming') ? 'pendientes' : 'en el historial'; ?>.
        </h4>
        <?php if ($filter === 'upcoming'): ?>
          <p class="text-muted">¿Necesitas una consulta? ¡Agenda una ahora!</p>
          <a href="solicitar-turno.php" class="btn btn-teal mt-2">Agendar Turno</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="modal fade" id="cancelacionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Cancelación</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body text-center p-4">
          <p class="lead mb-0">¿Estás seguro de que deseas cancelar este turno?</p>
          <small class="text-muted">Esta acción no se puede deshacer.</small>
        </div>
        <div class="modal-footer justify-content-center bg-light">
          <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">No, Volver</button>
          <button type="button" class="btn btn-danger px-4" id="confirmar-cancelacion">Sí, Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function () {
      let turnoIdParaCancelar;
      $('.cancelar-turno').on('click', function () {
        turnoIdParaCancelar = $(this).data('id');
        $('#cancelacionModal').modal('show');
      });
      $('#confirmar-cancelacion').on('click', function () {
        const form = $('<form action="cancelar-turno.php" method="post" style="display:none;"></form>');
        form.append($('<input type="hidden" name="id" value="' + turnoIdParaCancelar + '">'));
        $('body').append(form);
        form.submit();
      });
    });
  </script>
  <?php require_once '../shared/footer.php'; ?>
</body>

</html>