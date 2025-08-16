<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$turnoExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
  $id_pro = $_POST['profesional_id'];
  $fecha_turno = $_POST['fecha_turno'];
  $hora_turno = $_POST['hora_turno'];
  $id_mascota = $_POST['id_mascota'];
  $id_serv = $_POST['id_serv'];
  $modalidad = $_POST['modalidad'];

  $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

  $conn->begin_transaction();

  try {
    $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle)
                      VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
    $stmtInsert->execute();

    $conn->commit();
    $_SESSION['turno_exitoso'] = true;
  } catch (mysqli_sql_exception $e) {
    $conn->rollback();
    echo "<div class='alert alert-danger'>Error al registrar el turno: {$e->getMessage()}</div>";
  }

  if (isset($stmtInsert))
    $stmtInsert->close();
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
  $turnoExitoso = true;
  unset($_SESSION['turno_exitoso']);
}

$sql = "SELECT profesionales.id, usuarios.nombre, especialidad.nombre AS especialidad, especialidad.id AS id_esp
        FROM profesionales
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN especialidad ON profesionales.id_esp = especialidad.id";
$result = $conn->query($sql);
$profesionales = $result->fetch_all(MYSQLI_ASSOC);

$horariosPorProfesional = [];
$sqlHorarios = "SELECT idPro, diaSem, horaIni, horaFin FROM profesionales_horarios";
$resultHorarios = $conn->query($sqlHorarios);
while ($row = $resultHorarios->fetch_assoc()) {
  $horariosPorProfesional[$row['idPro']][] = $row;
}

$sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
$stmtMasc = $conn->prepare($sqlMascotas);
$stmtMasc->bind_param("i", $_SESSION['usuario_id']);
$stmtMasc->execute();
$resMasc = $stmtMasc->get_result();
$mascotas = $resMasc->fetch_all(MYSQLI_ASSOC);
$stmtMasc->close();

$sqlServicios = "SELECT id, nombre, precio, id_esp FROM servicios";
$resServ = $conn->query($sqlServicios);
$servicios = $resServ->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <style>
    .card-profesional {
      margin-bottom: 20px;
    }

    .list-group-item.selectable {
      cursor: pointer;
    }

    .list-group-item.selectable:hover {
      background-color: #f8f9fa;
    }
  </style>
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5">
    <h1>Seleccionar Profesional y Horario</h1>

    <div class="form-group mb-4">
      <input type="text" id="filtroProfesionales" class="form-control"
        placeholder="Buscar por nombre del profesional...">
    </div>

    <div class="row">
      <?php foreach ($profesionales as $profesional): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-profesional">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($profesional['nombre']) ?></h5>
              <p class="card-text">Especialidad: <?= htmlspecialchars($profesional['especialidad']) ?></p>
              <hr>
              <h6>Días y Horarios de atención:</h6>
              <ul class="list-group list-group-flush mb-3">
                <?php
                $diasAtencion = $horariosPorProfesional[$profesional['id']] ?? [];
                if (!empty($diasAtencion)): ?>
                  <?php foreach ($diasAtencion as $horario): ?>
                    <li class="list-group-item">
                      <?= $horario['diaSem'] ?>: de <?= $horario['horaIni'] ?> a <?= $horario['horaFin'] ?>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="list-group-item text-muted">Sin horarios asignados.</li>
                <?php endif; ?>
              </ul>

              <hr>
              <div class="text-center">
                <button type="button" class="btn btn-info btn-block mostrar-formulario-btn">Sacar Turno</button>
              </div>

              <form class="booking-form mt-3" data-id-pro="<?= $profesional['id'] ?>"
                data-pro-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                data-id-esp="<?= $profesional['id_esp'] ?>" style="display:none;">
                <div class="form-group">
                  <label for="fecha-<?= $profesional['id'] ?>">Fecha del turno:</label>
                  <input type="date" class="form-control" id="fecha-<?= $profesional['id'] ?>" name="fecha_turno"
                    min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                  <label for="hora-<?= $profesional['id'] ?>">Hora del turno:</label>
                  <input type="time" class="form-control" id="hora-<?= $profesional['id'] ?>" name="hora_turno" required>
                  <small id="horaError-<?= $profesional['id'] ?>" class="form-text text-danger"
                    style="display:none;"></small>
                </div>
                <button type="button" class="btn btn-primary btn-block sacar-turno-btn" disabled>Confirmar Turno</button>
                <button type="button" class="btn btn-secondary btn-block mt-2 cancelar-turno-btn">Cancelar</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmacionModalLabel">Confirmar Turno</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h5>Resumen del Turno</h5>
          <p><strong>Profesional:</strong> <span id="summary-profesional"></span></p>
          <p><strong>Fecha:</strong> <span id="summary-fecha"></span></p>
          <p><strong>Hora:</strong> <span id="summary-hora"></span></p>
          <p><strong>Precio:</strong> <span id="summary-precio"></span></p>

          <form method="POST" id="confirmacionForm">
            <input type="hidden" name="profesional_id" id="form-profesional-id">
            <input type="hidden" name="fecha_turno" id="form-fecha">
            <input type="hidden" name="hora_turno" id="form-hora">
            <input type="hidden" name="id_serv" id="form-service-id">

            <div class="form-group">
              <label for="id_mascota">Selecciona tu mascota:</label>
              <select class="form-control" name="id_mascota" required>
                <?php foreach ($mascotas as $m): ?>
                  <option value="<?= $m['id'] ?>"><?= $m['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="id_serv">Selecciona el servicio:</label>
              <select class="form-control" name="id_serv" id="id_serv_modal" required>
                <option value="">Selecciona un servicio</option>
              </select>
            </div>
            <div class="form-group">
              <label>Modalidad:</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="modalidad" id="tipoPresencial" value="Presencial"
                  checked>
                <label class="form-check-label" for="tipoPresencial">Presencial</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="modalidad" id="tipoDomicilio" value="A domicilio">
                <label class="form-check-label" for="tipoDomicilio">A domicilio</label>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Confirmar Turno</button>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="turnoExitosoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">¡Turno Registrado con Éxito!</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <p>Tu turno ha sido agendado exitosamente.</p>
        </div>
        <div class="modal-footer">
          <a href="mis-turnos.php" class="btn btn-success">Ver mis turnos</a>
          <a href="solicitar-turno-profesional.php" class="btn btn-secondary">Sacar otro turno</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      const diasSemana = { 'Dom': 0, 'Lun': 1, 'Mar': 2, 'Mie': 3, 'Jue': 4, 'Vie': 5, 'Sab': 6 };
      const horariosProfesionales = <?php echo json_encode($horariosPorProfesional); ?>;
      const servicios = <?php echo json_encode($servicios); ?>;
      const profesionales = <?php echo json_encode($profesionales); ?>;

      const serviciosPorEspecialidad = {};
      servicios.forEach(s => {
        if (!serviciosPorEspecialidad[s.id_esp]) {
          serviciosPorEspecialidad[s.id_esp] = [];
        }
        serviciosPorEspecialidad[s.id_esp].push(s);
      });

      $('#filtroProfesionales').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('.card-profesional').each(function () {
          const nombrePro = $(this).find('.card-title').text().toLowerCase();
          if (nombrePro.includes(searchTerm)) {
            $(this).closest('.col-md-6').show();
          } else {
            $(this).closest('.col-md-6').hide();
          }
        });
      });

      $('.mostrar-formulario-btn').on('click', function () {
        const cardBody = $(this).closest('.card-body');
        const form = cardBody.find('.booking-form');
        $(this).hide();
        form.slideDown();
      });

      $('.cancelar-turno-btn').on('click', function () {
        const cardBody = $(this).closest('.card-body');
        const form = cardBody.find('.booking-form');
        const mostrarBtn = cardBody.find('.mostrar-formulario-btn');

        form.slideUp(function () {
          mostrarBtn.show();
          form.find('input[type="date"], input[type="time"]').val('');
          form.find('.sacar-turno-btn').prop('disabled', true);
          form.find('.form-text').hide();
        });
      });

      $('.booking-form').on('change', 'input[type="date"], input[type="time"]', function () {
        const form = $(this).closest('.booking-form');
        const proId = form.data('id-pro');
        const fecha = form.find('input[name="fecha_turno"]').val();
        const hora = form.find('input[name="hora_turno"]').val();
        const sacarTurnoBtn = form.find('.sacar-turno-btn');
        const errorSpan = form.find('.form-text');

        sacarTurnoBtn.prop('disabled', true);
        errorSpan.hide().text('');

        if (fecha && hora) {
          const fechaObj = new Date(fecha.replace(/-/g, '/') + ' ' + hora);
          const diaSemanaNum = fechaObj.getDay();
          const diaSemanaStr = Object.keys(diasSemana).find(key => diasSemana[key] === diaSemanaNum);

          const horariosPro = horariosProfesionales[proId];
          if (horariosPro) {
            let esDiaValido = false;
            let esHoraValida = false;

            horariosPro.forEach(horario => {
              if (horario.diaSem === diaSemanaStr) {
                esDiaValido = true;
                if (hora >= horario.horaIni && hora < horario.horaFin) {
                  esHoraValida = true;
                }
              }
            });

            if (!esDiaValido) {
              errorSpan.text('Este profesional no atiende el día seleccionado.').show();
            } else if (!esHoraValida) {
              errorSpan.text('La hora seleccionada no está dentro del horario de atención.').show();
            } else {
              $.ajax({
                url: 'verificar-turno-disponible.php',
                method: 'POST',
                dataType: 'json',
                data: { id_pro: proId, fecha: fecha, hora: hora },
                success: function (response) {
                  if (response.disponible) {
                    sacarTurnoBtn.prop('disabled', false);
                  } else {
                    errorSpan.text('El turno ya está reservado.').show();
                  }
                },
                error: function () {
                  errorSpan.text('Error al verificar la disponibilidad del turno.').show();
                }
              });
            }
          } else {
            errorSpan.text('Este profesional no tiene horarios asignados.').show();
          }
        }
      });

      $('.sacar-turno-btn').on('click', function () {
        const form = $(this).closest('.booking-form');
        const proId = form.data('id-pro');
        const proNombre = form.data('pro-nombre');
        const idEsp = form.data('id-esp');
        const fecha = form.find('input[name="fecha_turno"]').val();
        const hora = form.find('input[name="hora_turno"]').val();

        $('#summary-profesional').text(proNombre);
        $('#summary-fecha').text(fecha);
        $('#summary-hora').text(hora);

        $('#form-profesional-id').val(proId);
        $('#form-fecha').val(fecha);
        $('#form-hora').val(hora);

        const serviciosFiltrados = serviciosPorEspecialidad[idEsp] || [];
        const selectServicios = $('#id_serv_modal');
        selectServicios.empty().append('<option value="">Selecciona un servicio</option>');

        if (serviciosFiltrados.length > 0) {
          serviciosFiltrados.forEach(s => {
            selectServicios.append(`<option value="${s.id}" data-precio="${s.precio}">${s.nombre}</option>`);
          });
        }

        $('#confirmacionModal').modal('show');
      });

      $('#id_serv_modal').on('change', function () {
        const precio = $(this).find(':selected').data('precio');
        $('#summary-precio').text(precio ? `$${precio}` : '');
        $('#form-service-id').val($(this).val());
      });

      var turnoExitoso = <?php echo json_encode($turnoExitoso); ?>;
      if (turnoExitoso) {
        $('#turnoExitosoModal').modal('show');
      }
    });
  </script>
</body>

</html>