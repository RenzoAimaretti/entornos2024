<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$turnoExitoso = false;
$errorMascotaOcupada = false; // Variable para controlar el error

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
  $id_pro = $_POST['profesional_id'];
  $fecha_turno = $_POST['fecha_turno'];
  $hora_turno = $_POST['hora_turno'];
  $id_mascota = $_POST['id_mascota'];
  $id_serv = $_POST['id_serv'];
  $modalidad = $_POST['modalidad'];

  $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

  // --- 1. VERIFICAR DISPONIBILIDAD DE LA MASCOTA ---
  $sqlCheck = "SELECT id FROM atenciones WHERE id_mascota = ? AND fecha = ?";
  $stmtCheck = $conn->prepare($sqlCheck);
  $stmtCheck->bind_param("is", $id_mascota, $fecha_datetime);
  $stmtCheck->execute();
  $resultCheck = $stmtCheck->get_result();

  if ($resultCheck->num_rows > 0) {
    // Si ya hay turno, activamos el error y NO insertamos
    $errorMascotaOcupada = true;
  } else {
    // --- 2. SI ESTÁ LIBRE, PROCEDEMOS A INSERTAR ---
    $conn->begin_transaction();

    try {
      $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, ?)";
      $stmtInsert = $conn->prepare($sqlInsert);
      $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
      $stmtInsert->execute();

      // --- Lógica de Envío de Mail ---
      $sqlInfo = "SELECT u_cli.email as mail_cliente, u_cli.nombre as nombre_cliente, 
                               m.nombre as nombre_mascota, u_pro.nombre as nombre_pro, s.nombre as nombre_serv
                        FROM usuarios u_cli
                        INNER JOIN mascotas m ON m.id_cliente = u_cli.id
                        INNER JOIN usuarios u_pro ON u_pro.id = ?
                        INNER JOIN servicios s ON s.id = ?
                        WHERE m.id = ? AND u_cli.id = ?";
      $stmtInfo = $conn->prepare($sqlInfo);
      $userId = $_SESSION['usuario_id'];
      $stmtInfo->bind_param("iiii", $id_pro, $id_serv, $id_mascota, $userId);
      $stmtInfo->execute();
      $infoMail = $stmtInfo->get_result()->fetch_assoc();

      if ($infoMail) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($_ENV['MAIL_USERNAME'], 'Veterinaria San Antón');
        $mail->addAddress($infoMail['mail_cliente'], $infoMail['nombre_cliente']);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Turno - San Antón';
        $mail->Body = "
                    <div style='font-family: Arial, sans-serif;'>
                        <h2>¡Turno Confirmado!</h2>
                        <p>Hola <strong>{$infoMail['nombre_cliente']}</strong>,</p>
                        <p>Se ha registrado un nuevo turno para tu mascota:</p>
                        <ul>
                            <li><strong>Mascota:</strong> {$infoMail['nombre_mascota']}</li>
                            <li><strong>Servicio:</strong> {$infoMail['nombre_serv']}</li>
                            <li><strong>Profesional:</strong> {$infoMail['nombre_pro']}</li>
                            <li><strong>Fecha y Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_datetime)) . "</li>
                            <li><strong>Modalidad:</strong> $modalidad</li>
                        </ul>
                        <p>¡Te esperamos!</p>
                    </div>
                ";
        $mail->send();
      }

      $conn->commit();
      $_SESSION['turno_exitoso'] = true;
      header("Location: solicitar-turno-servicio.php?service_id=" . $id_serv);
      exit();

    } catch (Exception $e) {
      $conn->rollback();
      echo "<div class='alert alert-danger'>Error: {$mail->ErrorInfo}</div>";
    } catch (mysqli_sql_exception $e) {
      $conn->rollback();
      echo "<div class='alert alert-danger'>Error DB: {$e->getMessage()}</div>";
    }
  }
  $stmtCheck->close();
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
  $turnoExitoso = true;
  unset($_SESSION['turno_exitoso']);
}

// ... (El resto de tu código de carga de servicios y profesionales se mantiene igual) ...
$service_id_selected = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
$servicios = [];
$profesionales = [];
$horariosPorProfesional = [];
$mascotas = [];
$servicio_seleccionado = null;

if ($service_id_selected) {
  // ... (Tu código existente para cargar datos) ...
  $sqlServicio = "SELECT id, nombre, precio, id_esp FROM servicios WHERE id = ?";
  $stmtServ = $conn->prepare($sqlServicio);
  $stmtServ->bind_param("i", $service_id_selected);
  $stmtServ->execute();
  $resServ = $stmtServ->get_result();
  $servicio_seleccionado = $resServ->fetch_assoc();
  $stmtServ->close();

  $sqlProfesionales = "SELECT p.id, u.nombre, e.nombre AS especialidad, e.id AS id_esp
                         FROM profesionales p
                         INNER JOIN usuarios u ON p.id = u.id
                         INNER JOIN especialidad e ON p.id_esp = e.id
                         WHERE e.id = ?";
  $stmtProf = $conn->prepare($sqlProfesionales);
  $stmtProf->bind_param("i", $servicio_seleccionado['id_esp']);
  $stmtProf->execute();
  $profesionales = $stmtProf->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtProf->close();

  $profesionales_ids = array_column($profesionales, 'id');
  if (!empty($profesionales_ids)) {
    $prof_ids_str = implode(',', $profesionales_ids);
    $sqlHorarios = "SELECT idPro, diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro IN ($prof_ids_str)";
    $resultHorarios = $conn->query($sqlHorarios);
    while ($row = $resultHorarios->fetch_assoc()) {
      $horariosPorProfesional[$row['idPro']][] = $row;
    }
  }

  $sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
  $stmtMasc = $conn->prepare($sqlMascotas);
  $stmtMasc->bind_param("i", $_SESSION['usuario_id']);
  $stmtMasc->execute();
  $resMasc = $stmtMasc->get_result();
  $mascotas = $resMasc->fetch_all(MYSQLI_ASSOC);
  $stmtMasc->close();

} else {
  $sqlServicios = "SELECT id, nombre, precio FROM servicios";
  $resultServ = $conn->query($sqlServicios);
  $servicios = $resultServ->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <style>
    .card-profesional {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <section class="container my-4">
    <?php if ($errorMascotaOcupada): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>¡Atención!</strong> Esta mascota ya tiene un turno asignado para ese mismo día y horario. Por favor,
        seleccione otro horario o mascota.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>

    <?php if ($service_id_selected && $servicio_seleccionado): ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Profesionales para: <?= htmlspecialchars($servicio_seleccionado['nombre']) ?></h3>
        <a href="solicitar-turno-servicio.php" class="btn btn-secondary">← Volver a Servicios</a>
      </div>

      <p class="lead">Precio del servicio: $<?= number_format($servicio_seleccionado['precio'], 2) ?></p>

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
                  data-id-serv="<?= $servicio_seleccionado['id'] ?>"
                  data-service-nombre="<?= htmlspecialchars($servicio_seleccionado['nombre']) ?>"
                  data-service-precio="<?= $servicio_seleccionado['precio'] ?>" style="display:none;">
                  <div class="form-group">
                    <label for="fecha-<?= $profesional['id'] ?>">Fecha del turno:</label>
                    <input type="date" class="form-control" id="fecha-<?= $profesional['id'] ?>" name="fecha_turno"
                      min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="hora-<?= $profesional['id'] ?>">Hora del turno:</label>
                    <select class="form-control" id="hora-<?= $profesional['id'] ?>" name="hora_turno" required disabled>
                      <option value="" disabled selected>Seleccione hora</option>
                    </select>
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
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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
          <a href="solicitar-turno-servicio.php" class="btn btn-secondary">Sacar otro turno</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      const diasSemana = { 'Lun': 1, 'Mar': 2, 'Mie': 3, 'Jue': 4, 'Vie': 5, 'Sab': 6, 'Dom': 0 };
      const horariosProfesionales = <?php echo json_encode($horariosPorProfesional); ?>;
      const servicioSeleccionado = <?php echo json_encode($servicio_seleccionado); ?>;

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
          form.find('input[type="date"]').val('');
          form.find('select[name="hora_turno"]').empty().prop('disabled', true).append('<option value="" disabled selected>Seleccione hora</option>');
          form.find('.sacar-turno-btn').prop('disabled', true);
          form.find('.form-text').hide();
        });
      });

      $('.booking-form').on('change', 'input[type="date"]', function () {
        const form = $(this).closest('.booking-form');
        const proId = form.data('id-pro');
        const fecha = $(this).val();
        const horaSelect = form.find('select[name="hora_turno"]');
        const sacarTurnoBtn = form.find('.sacar-turno-btn');
        const errorSpan = form.find('.form-text');

        horaSelect.prop('disabled', true).empty().append('<option value="" disabled selected>Seleccione hora</option>');
        sacarTurnoBtn.prop('disabled', true);
        errorSpan.hide().text('');

        if (fecha) {
          const fechaObj = new Date(fecha.replace(/-/g, '/'));
          const diaSemanaNum = fechaObj.getDay();
          const diaSemanaStr = Object.keys(diasSemana).find(key => diasSemana[key] === diaSemanaNum);

          const horariosPro = horariosProfesionales[proId];
          if (horariosPro) {
            const horarioAtencion = horariosPro.find(h => h.diaSem === diaSemanaStr);
            if (horarioAtencion) {
              $.ajax({
                url: 'verificar-turno-disponible-servicio.php',
                method: 'POST',
                dataType: 'json',
                data: { id_pro: proId, fecha: fecha },
                success: function (disponibles) {
                  if (disponibles.length > 0) {
                    disponibles.forEach(horaDisponible => {
                      horaSelect.append(`<option value="${horaDisponible}">${horaDisponible.substring(0, 5)}</option>`);
                    });
                    horaSelect.prop('disabled', false);
                  } else {
                    errorSpan.text('No hay horarios disponibles para este día.').show();
                  }
                },
                error: function () {
                  errorSpan.text('Error al verificar los horarios disponibles.').show();
                }
              });
            } else {
              errorSpan.text('Este profesional no atiende el día seleccionado.').show();
            }
          } else {
            errorSpan.text('Este profesional no tiene horarios asignados.').show();
          }
        }
      });

      $('.booking-form').on('change', 'select[name="hora_turno"]', function () {
        const sacarTurnoBtn = $(this).closest('.booking-form').find('.sacar-turno-btn');
        sacarTurnoBtn.prop('disabled', false);
      });

      $('.sacar-turno-btn').on('click', function () {
        const form = $(this).closest('.booking-form');
        const proId = form.data('id-pro');
        const proNombre = form.data('pro-nombre');
        const fecha = form.find('input[name="fecha_turno"]').val();
        const hora = form.find('select[name="hora_turno"]').val();

        $('#summary-profesional').text(proNombre);
        $('#summary-fecha').text(fecha);
        $('#summary-hora').text(hora);

        const serviceNombre = form.data('service-nombre');
        const servicePrecio = form.data('service-precio');

        $('#summary-servicio-nombre').text(serviceNombre);
        $('#summary-precio').text(servicePrecio ? `$${servicePrecio}` : '');

        $('#form-profesional-id').val(proId);
        $('#form-fecha').val(fecha);
        $('#form-hora').val(hora);
        $('#form-service-id').val(form.data('id-serv'));

        $('#confirmacionModal').modal('show');
      });

      var turnoExitoso = <?php echo json_encode($turnoExitoso); ?>;
      if (turnoExitoso) {
        $('#turnoExitosoModal').modal('show');
      }
    });
  </script>
  <?php require_once '../shared/footer.php'; ?>
</body>

</html>