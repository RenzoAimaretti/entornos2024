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

// --- Lógica para procesar la confirmación del turno ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
  $id_pro = $_POST['profesional_id'];
  $fecha_turno = $_POST['fecha_turno'];
  $hora_turno = $_POST['hora_turno'];
  $id_mascota = $_POST['id_mascota'];
  $id_serv = $_POST['id_serv'];
  $id_horario = $_POST['id_horario'];

  $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

  $conn->begin_transaction();
  $turnoExitoso = false;

  try {
    // 1. Insertar el nuevo turno en 'atenciones'
    $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) 
                      VALUES (?, ?, ?, ?, NULL)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iiis", $id_mascota, $id_serv, $id_pro, $fecha_datetime);
    $stmtInsert->execute();

    // 2. Actualizar el estado 'ocupado' en 'profesionales_horarios'
    $sqlUpdate = "UPDATE profesionales_horarios SET ocupado = 1 WHERE id_pro = ? AND id_horario = ? AND fecha = ?";
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
}

// --- Lógica para mostrar las vistas (lista de servicios o lista de horarios) ---
$service_id_selected = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
$servicios = [];
$profesionales_con_horarios = [];
$servicio_seleccionado = null;
$mascotas = [];

if ($service_id_selected) {
  // Vista para mostrar profesionales y horarios de un servicio

  // Obtener información del servicio seleccionado
  $sqlServicio = "SELECT id, nombre, precio, id_esp FROM servicios WHERE id = ?";
  $stmtServ = $conn->prepare($sqlServicio);
  $stmtServ->bind_param("i", $service_id_selected);
  $stmtServ->execute();
  $resServ = $stmtServ->get_result();
  $servicio_seleccionado = $resServ->fetch_assoc();
  $stmtServ->close();

  // Obtener profesionales que ofrecen este servicio
  $sqlProfesionales = "SELECT p.id AS id_pro, u.nombre AS nombre_pro
                         FROM profesionales p
                         INNER JOIN usuarios u ON p.id = u.id
                         WHERE p.id_esp = ?";
  $stmtProf = $conn->prepare($sqlProfesionales);
  $stmtProf->bind_param("i", $servicio_seleccionado['id_esp']);
  $stmtProf->execute();
  $profesionales_del_servicio = $stmtProf->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtProf->close();

  $profesionales_map = array_column($profesionales_del_servicio, 'nombre_pro', 'id_pro');

  // Obtener todos los horarios disponibles para esos profesionales
  if (!empty($profesionales_del_servicio)) {
    $prof_ids = implode(',', array_column($profesionales_del_servicio, 'id_pro'));
    $sqlHorarios = "SELECT ph.id_pro, ph.fecha, ph.id_horario, h.hora
                        FROM profesionales_horarios ph
                        INNER JOIN horarios_turnos h ON ph.id_horario = h.id
                        WHERE ph.ocupado = 0 AND ph.id_pro IN ($prof_ids)
                        ORDER BY ph.fecha, h.hora";
    $resultHorarios = $conn->query($sqlHorarios);
    if ($resultHorarios) {
      $profesionales_con_horarios = $resultHorarios->fetch_all(MYSQLI_ASSOC);
    }
  }

  // Obtener mascotas del cliente logueado para el formulario de confirmación
  $sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
  $stmtMasc = $conn->prepare($sqlMascotas);
  $stmtMasc->bind_param("i", $_SESSION['usuario_id']);
  $stmtMasc->execute();
  $resMasc = $stmtMasc->get_result();
  $mascotas = $resMasc->fetch_all(MYSQLI_ASSOC);
  $stmtMasc->close();

} else {
  // Vista inicial: lista de todos los servicios
  $sqlServicios = "SELECT id, nombre, precio FROM servicios";
  $resultServ = $conn->query($sqlServicios);
  $servicios = $resultServ->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .list-group-item.selectable {
      cursor: pointer;
    }

    .list-group-item.selectable:hover {
      background-color: #f8f9fa;
    }

    .bg-green {
      background-color: #1abc9c;
    }
  </style>
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <section class="container my-4">
    <?php if ($service_id_selected && $servicio_seleccionado): ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Horarios disponibles para: <?= htmlspecialchars($servicio_seleccionado['nombre']) ?></h3>
        <a href="solicitar-turno-servicio.php" class="btn btn-secondary">← Volver a Servicios</a>
      </div>

      <p class="lead">Precio del servicio: $<?= number_format($servicio_seleccionado['precio'], 2) ?></p>

      <?php if (!empty($profesionales_con_horarios)): ?>
        <ul class="list-group">
          <?php foreach ($profesionales_con_horarios as $horario): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center selectable" data-toggle="modal"
              data-target="#confirmacionModal" data-profesional-id="<?= htmlspecialchars($horario['id_pro']) ?>"
              data-profesional-nombre="<?= htmlspecialchars($profesionales_map[$horario['id_pro']]) ?>"
              data-fecha="<?= htmlspecialchars($horario['fecha']) ?>" data-hora="<?= htmlspecialchars($horario['hora']) ?>"
              data-horario-id="<?= htmlspecialchars($horario['id_horario']) ?>"
              data-service-id="<?= htmlspecialchars($servicio_seleccionado['id']) ?>"
              data-service-nombre="<?= htmlspecialchars($servicio_seleccionado['nombre']) ?>"
              data-service-precio="<?= htmlspecialchars($servicio_seleccionado['precio']) ?>">
              <div>
                <span class="font-weight-bold">Fecha:</span> <?= htmlspecialchars($horario['fecha']) ?>,
                <span class="font-weight-bold">Hora:</span> <?= htmlspecialchars($horario['hora']) ?>
              </div>
              <div>
                <span class="font-weight-bold">Profesional:</span>
                <?= htmlspecialchars($profesionales_map[$horario['id_pro']]) ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="alert alert-warning mt-3">No hay horarios disponibles para este servicio en este momento.</div>
      <?php endif; ?>

    <?php else: ?>
      <div class="text-center my-4">
        <h3>Seleccione el servicio que necesita</h3>
      </div>
      <div class="list-group">
        <?php if (!empty($servicios)): ?>
          <?php foreach ($servicios as $s): ?>
            <a href="solicitar-turno-servicio.php?service_id=<?= $s['id'] ?>"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              <span><?= htmlspecialchars($s['nombre']) ?></span>
              <span class="badge badge-primary badge-pill">$<?= number_format($s['precio'], 2) ?></span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-warning">No se encontraron servicios.</div>
        <?php endif; ?>
      </div>
      <div class="text-center mt-3">
        <a href="solicitar-turno.php" class="btn btn-secondary">Volver</a>
      </div>
    <?php endif; ?>
  </section>

  <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmacionModalLabel">Confirmar Turno</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
              aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <h5>Resumen del Turno</h5>
          <p><strong>Servicio:</strong> <span id="summary-servicio-nombre"></span></p>
          <p><strong>Profesional:</strong> <span id="summary-profesional"></span></p>
          <p><strong>Fecha:</strong> <span id="summary-fecha"></span></p>
          <p><strong>Hora:</strong> <span id="summary-hora"></span></p>
          <p><strong>Precio:</strong> <span id="summary-precio"></span></p>

          <form method="POST" id="confirmacionForm">
            <input type="hidden" name="profesional_id" id="form-profesional-id">
            <input type="hidden" name="fecha_turno" id="form-fecha">
            <input type="hidden" name="hora_turno" id="form-hora">
            <input type="hidden" name="id_horario" id="form-horario-id">
            <input type="hidden" name="id_serv" id="form-service-id">

            <div class="form-group">
              <label for="id_mascota">Selecciona tu mascota:</label>
              <select class="form-control" name="id_mascota" required>
                <?php if (!empty($mascotas)): ?>
                  <?php foreach ($mascotas as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= $m['nombre'] ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="" disabled>No tienes mascotas registradas.</option>
                <?php endif; ?>
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

  <section class="bg-green text-white py-2 text-center mt-4">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <footer class="bg-light py-4">
    <div class="container text-center">
      <p>Teléfono de contacto: 115673346</p>
      <p>Mail: sananton24@gmail.com</p>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function () {
      // Manejador para el clic en los horarios
      $('.selectable').on('click', function () {
        const profesionalId = $(this).data('profesional-id');
        const profesionalNombre = $(this).data('profesional-nombre');
        const fecha = $(this).data('fecha');
        const hora = $(this).data('hora');
        const horarioId = $(this).data('horario-id');
        const serviceId = $(this).data('service-id');
        const serviceNombre = $(this).data('service-nombre');
        const servicePrecio = $(this).data('service-precio');

        // Llenar el resumen en la modal de confirmación
        $('#summary-servicio-nombre').text(serviceNombre);
        $('#summary-profesional').text(profesionalNombre);
        $('#summary-fecha').text(fecha);
        $('#summary-hora').text(hora);
        $('#summary-precio').text(`$${servicePrecio}`);

        // Llenar los campos ocultos del formulario
        $('#form-profesional-id').val(profesionalId);
        $('#form-fecha').val(fecha);
        $('#form-hora').val(hora);
        $('#form-horario-id').val(horarioId);
        $('#form-service-id').val(serviceId);
      });
    });
  </script>
</body>

</html>