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
$errorMascotaOcupada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
    $id_pro = $_POST['profesional_id'];
    $fecha_turno = $_POST['fecha_turno'];
    $hora_turno = $_POST['hora_turno'];
    $id_mascota = $_POST['id_mascota'];
    $id_serv = $_POST['id_serv'];
    $modalidad = $_POST['modalidad'];

    $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

    $sqlCheck = "SELECT id FROM atenciones WHERE id_mascota = ? AND fecha = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("is", $id_mascota, $fecha_datetime);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $errorMascotaOcupada = true;
    } else {
        $conn->begin_transaction();
        try {
            $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
            $stmtInsert->execute();

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
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h2 style='color: #00897b;'>¡Turno Confirmado!</h2>
                        <p>Hola <strong>{$infoMail['nombre_cliente']}</strong>,</p>
                        <p>Se ha registrado un nuevo turno para tu mascota:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #00897b;'>
                            <p><strong>Mascota:</strong> {$infoMail['nombre_mascota']}</p>
                            <p><strong>Servicio:</strong> {$infoMail['nombre_serv']}</p>
                            <p><strong>Profesional:</strong> {$infoMail['nombre_pro']}</p>
                            <p><strong>Fecha y Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_datetime)) . "</p>
                            <p><strong>Modalidad:</strong> $modalidad</p>
                        </div>
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
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
        }
    }
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
    $turnoExitoso = true;
    unset($_SESSION['turno_exitoso']);
}

$service_id_selected = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
$servicios = [];
$profesionales = [];
$horariosPorProfesional = [];
$mascotas = [];
$servicio_seleccionado = null;

if ($service_id_selected) {
    $sqlServicio = "SELECT id, nombre, precio, id_esp FROM servicios WHERE id = ?";
    $stmtServ = $conn->prepare($sqlServicio);
    $stmtServ->bind_param("i", $service_id_selected);
    $stmtServ->execute();
    $servicio_seleccionado = $stmtServ->get_result()->fetch_assoc();
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
    $mascotas = $stmtMasc->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtMasc->close();

} else {
    $resultServ = $conn->query("SELECT id, nombre, precio FROM servicios");
    $servicios = $resultServ->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if ($errorMascotaOcupada): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>¡Atención!</strong> Esta mascota ya tiene un turno
                para ese horario.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($service_id_selected && $servicio_seleccionado): ?>
            <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
                <h1 class="mb-0 font-weight-bold">Elegí tu Profesional</h1>
                <p class="mb-0 mt-1">Servicio: <strong><?= htmlspecialchars($servicio_seleccionado['nombre']) ?></strong>
                </p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="solicitar-turno-servicio.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Cambiar Servicio
                </a>
                <span class="badge badge-success px-3 py-2" style="font-size: 1rem;">
                    Precio estimado: $<?= number_format($servicio_seleccionado['precio'], 2) ?>
                </span>
            </div>

            <div class="row">
                <?php foreach ($profesionales as $profesional): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-profesional shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                        style="width: 50px; height: 50px; color: #00897b;">
                                        <i class="fas fa-user-md fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 font-weight-bold">
                                            <?= htmlspecialchars($profesional['nombre']) ?></h5>
                                        <small
                                            class="text-teal font-weight-bold"><?= htmlspecialchars($profesional['especialidad']) ?></small>
                                    </div>
                                </div>
                                <hr>
                                <ul class="list-unstyled mb-3 small text-secondary">
                                    <?php
                                    $horarios = $horariosPorProfesional[$profesional['id']] ?? [];
                                    foreach ($horarios as $h): ?>
                                        <li class="mb-1"><i
                                                class="fas fa-calendar-day mr-2 text-teal"></i><strong><?= $h['diaSem'] ?>:</strong>
                                            <?= $h['horaIni'] ?> - <?= $h['horaFin'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button"
                                    class="btn btn-outline-info btn-block rounded-pill font-weight-bold mostrar-formulario-btn"
                                    style="color:#00897b; border-color:#00897b;">Reservar Cita</button>

                                <form class="booking-form mt-3" data-id-pro="<?= $profesional['id'] ?>"
                                    data-pro-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                                    data-id-serv="<?= $servicio_seleccionado['id'] ?>"
                                    data-service-nombre="<?= htmlspecialchars($servicio_seleccionado['nombre']) ?>"
                                    data-service-precio="<?= $servicio_seleccionado['precio'] ?>" style="display:none;">
                                    <div class="form-group"><label class="small font-weight-bold">Fecha:</label><input
                                            type="date" class="form-control form-control-sm" name="fecha_turno"
                                            min="<?= date('Y-m-d') ?>" required></div>
                                    <div class="form-group"><label class="small font-weight-bold">Hora:</label><select
                                            class="form-control form-control-sm" name="hora_turno" required disabled>
                                            <option value="" disabled selected>Seleccione fecha</option>
                                        </select></div>
                                    <button type="button"
                                        class="btn btn-success btn-sm btn-block sacar-turno-btn font-weight-bold"
                                        disabled>Continuar</button>
                                    <button type="button"
                                        class="btn btn-link btn-sm btn-block text-secondary cancelar-turno-btn">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
                <h1 class="mb-0 font-weight-bold">Selecciona un Servicio</h1>
                <p class="mb-0 mt-1">¿Qué necesita tu mascota hoy?</p>
            </div>

            <div class="d-flex justify-content-start mb-4">
                <a href="solicitar-turno.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver atrás
                </a>
            </div>

            <table id="tablaServicios" class="table" style="width:100%">
                <thead>
                    <tr>
                        <th>Servicio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td class="p-0 border-0">
                                <a href="solicitar-turno-servicio.php?service_id=<?= $s['id'] ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center service-item py-3 shadow-sm mb-2">
                                    <div>
                                        <h5 class="mb-1 text-dark font-weight-bold"><?= htmlspecialchars($s['nombre']) ?></h5>
                                        <small class="text-muted">Clic para ver profesionales disponibles</small>
                                    </div>
                                    <span class="badge badge-success badge-pill px-3 py-2" style="font-size: 0.9rem;">
                                        $<?= number_format($s['precio'], 2) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title font-weight-bold">Confirmar Turno</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border text-center mb-4">
                        <h5 class="text-teal mb-0" id="summary-servicio-nombre"></h5>
                        <small class="text-muted">Servicio Seleccionado</small>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="profesional_id" id="form-profesional-id">
                        <input type="hidden" name="fecha_turno" id="form-fecha">
                        <input type="hidden" name="hora_turno" id="form-hora">
                        <input type="hidden" name="id_serv" id="form-service-id">
                        <div class="form-group"><label class="font-weight-bold">Mascota:</label>
                            <select class="form-control" name="id_mascota" required>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= $m['nombre'] ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label class="font-weight-bold d-block">Modalidad:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active"><input type="radio" name="modalidad"
                                        value="Presencial" checked> Presencial</label>
                                <label class="btn btn-outline-secondary"><input type="radio" name="modalidad"
                                        value="A domicilio"> Domicilio</label>
                            </div>
                        </div>
                        <button type="submit"
                            class="btn btn-success btn-block btn-lg font-weight-bold mt-4 shadow-sm">CONFIRMAR
                            RESERVA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaServicios').DataTable({
                "pageLength": 10,
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
                "ordering": false,
                "info": false,
                "lengthChange": false
            });

            $('.mostrar-formulario-btn').on('click', function () {
                $(this).hide().closest('.card-body').find('.booking-form').slideDown();
            });

            $('.cancelar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                form.slideUp(() => form.closest('.card-body').find('.mostrar-formulario-btn').show());
            });

            $('.booking-form').on('change', 'input[type="date"]', function () {
                const form = $(this).closest('.booking-form');
                const proId = form.data('id-pro');
                const fecha = $(this).val();
                const horaSelect = form.find('select[name="hora_turno"]');
                if (fecha) {
                    $.post('verificar-turno-disponible-servicio.php', { id_pro: proId, fecha: fecha }, function (disponibles) {
                        horaSelect.empty().append('<option value="" disabled selected>Seleccione hora</option>');
                        disponibles.forEach(h => horaSelect.append(`<option value="${h}">${h.substring(0, 5)}</option>`));
                        horaSelect.prop('disabled', disponibles.length === 0);
                    }, 'json');
                }
            });

            $('.booking-form').on('change', 'select[name="hora_turno"]', function () {
                $(this).closest('.booking-form').find('.sacar-turno-btn').prop('disabled', false);
            });

            $('.sacar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                $('#summary-profesional').text(form.data('pro-nombre'));
                $('#summary-fecha').text(form.find('input[name="fecha_turno"]').val().split('-').reverse().join('/'));
                $('#summary-hora').text(form.find('select[name="hora_turno"]').val());
                $('#summary-servicio-nombre').text(form.data('service-nombre'));
                $('#summary-precio').text('$' + form.data('service-precio'));
                $('#form-profesional-id').val(form.data('id-pro'));
                $('#form-fecha').val(form.find('input[name="fecha_turno"]').val());
                $('#form-hora').val(form.find('select[name="hora_turno"]').val());
                $('#form-service-id').val(form.data('id-serv'));
                $('#confirmacionModal').modal('show');
            });

            if (<?php echo json_encode($turnoExitoso); ?>) {
                $('#turnoExitosoModal').modal('show');
            }
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>