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

// --- Manejo del formulario de confirmación ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
  $id_pro = $_POST['profesional_id'];
  $fecha_turno = $_POST['fecha_turno'];
  $hora_turno = $_POST['hora_turno'];
  $id_mascota = $_POST['id_mascota'];
  $id_serv = $_POST['id_serv'];
  $id_horario = $_POST['id_horario'];
  $modalidad = $_POST['modalidad']; // Capturamos la modalidad

  $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

  $conn->begin_transaction();
  $turnoExitoso = false;

  try {
    $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle)
                  VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
    $stmtInsert->execute();

    $sqlUpdate = "UPDATE profesionales_horarios SET ocupado = 1 
                  WHERE id_pro = ? AND id_horario = ? AND fecha = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("iis", $id_pro, $id_horario, $fecha_turno);
    $stmtUpdate->execute();

    $conn->commit();
    $turnoExitoso = true;
  } catch (mysqli_sql_exception $e) {
    $conn->rollback();
    echo "<div class='alert alert-danger'>Error al registrar el turno: {$e->getMessage()}</div>";
  }

  if ($turnoExitoso) {
    echo "<div class='alert alert-success'>Turno registrado con éxito.</div>";
  }

  $stmtInsert->close();
  $stmtUpdate->close();
}

// --- Consultas para cargar datos ---
$sql = "SELECT profesionales.id, usuarios.nombre, especialidad.nombre AS especialidad
        FROM profesionales
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN especialidad ON profesionales.id_esp = especialidad.id";
$result = $conn->query($sql);
$profesionales = $result->fetch_all(MYSQLI_ASSOC);

$horariosPorProfesional = [];
$sqlHorarios = "SELECT ph.id_pro, ph.fecha, ph.id_horario, h.hora
                FROM profesionales_horarios ph
                INNER JOIN horarios_turnos h ON ph.id_horario = h.id
                WHERE ph.ocupado = 0";
$resultHorarios = $conn->query($sqlHorarios);
while ($row = $resultHorarios->fetch_assoc()) {
  $horariosPorProfesional[$row['id_pro']][] = [
    'fecha' => $row['fecha'],
    'hora' => $row['hora'],
    'id_horario' => $row['id_horario']
  ];
}

$sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
$stmtMasc = $conn->prepare($sqlMascotas);
$stmtMasc->bind_param("i", $_SESSION['usuario_id']);
$stmtMasc->execute();
$resMasc = $stmtMasc->get_result();
$mascotas = $resMasc->fetch_all(MYSQLI_ASSOC);
$stmtMasc->close();

$sqlServicios = "SELECT id, nombre, precio FROM servicios";
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
    <div class="row">
      <?php foreach ($profesionales as $profesional): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-profesional">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($profesional['nombre']) ?></h5>
              <p class="card-text">Especialidad: <?= htmlspecialchars($profesional['especialidad']) ?></p>
              <button class="btn btn-info btn-sm" data-toggle="modal"
                data-target="#horariosModal-<?= $profesional['id'] ?>">Ver Horarios Disponibles</button>
            </div>
          </div>
        </div>

        <div class="modal fade" id="horariosModal-<?= $profesional['id'] ?>" tabindex="-1" role="dialog"
          aria-labelledby="horariosModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Horarios disponibles de <?= htmlspecialchars($profesional['nombre']) ?></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <?php if (isset($horariosPorProfesional[$profesional['id']])): ?>
                  <ul class="list-group">
                    <?php foreach ($horariosPorProfesional[$profesional['id']] as $horario): ?>
                      <a href="#" class="selectable-hour" data-profesional-id="<?= $profesional['id'] ?>"
                        data-profesional-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                        data-fecha="<?= htmlspecialchars($horario['fecha']) ?>"
                        data-hora="<?= htmlspecialchars($horario['hora']) ?>"
                        data-horario-id="<?= htmlspecialchars($horario['id_horario']) ?>" data-toggle="modal"
                        data-target="#confirmacionModal">
                        <li class="list-group-item d-flex justify-content-between align-items-center selectable">
                          Fecha: <?= htmlspecialchars($horario['fecha']) ?>, Hora: <?= htmlspecialchars($horario['hora']) ?>
                        </li>
                      </a>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="alert alert-warning">No hay horarios disponibles.</div>
                <?php endif; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar Turno</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
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
            <input type="hidden" name="id_horario" id="form-horario-id">

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
              <select class="form-control" name="id_serv" id="id_serv" required>
                <option value="">Selecciona un servicio</option>
                <?php foreach ($servicios as $s): ?>
                  <option value="<?= $s['id'] ?>" data-precio="<?= $s['precio'] ?>"><?= $s['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="modalidad">Selecciona la modalidad de la consulta:</label>
              <select class="form-control" name="modalidad" id="modalidad" required>
                <option value="">Selecciona una modalidad</option>
                <option value="Presencial">Presencial</option>
                <option value="A domicilio">A domicilio</option>
              </select>
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

  <script>
    $(document).ready(function () {
      $('.selectable-hour').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.modal').modal('hide');

        $('#summary-profesional').text($(this).data('profesional-nombre'));
        $('#summary-fecha').text($(this).data('fecha'));
        $('#summary-hora').text($(this).data('hora'));

        $('#form-profesional-id').val($(this).data('profesional-id'));
        $('#form-fecha').val($(this).data('fecha'));
        $('#form-hora').val($(this).data('hora'));
        $('#form-horario-id').val($(this).data('horario-id'));
      });

      $('#id_serv').on('change', function () {
        const precio = $(this).find(':selected').data('precio');
        $('#summary-precio').text(precio ? `$${precio}` : '');
      });
    });
  </script>
</body>

</html>