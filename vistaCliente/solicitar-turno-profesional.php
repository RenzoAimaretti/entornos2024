<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciar-sesion.php');
    exit();
}

// Importar clases de PHPMailer
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

// --- PROCESAMIENTO DEL FORMULARIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
    $id_pro = $_POST['profesional_id'];
    $fecha_turno = $_POST['fecha_turno'];
    $hora_turno = $_POST['hora_turno'];
    $id_mascota = $_POST['id_mascota'];
    $id_serv = $_POST['id_serv'];
    $modalidad = $_POST['modalidad'];

    $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

    // Verificar si la mascota ya tiene turno
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
            // INSERTAR TURNO
            $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
            $stmtInsert->execute();

            // OBTENER DATOS PARA EL MAIL
            $sqlInfo = "SELECT u_cli.email as mail_cliente, u_cli.nombre as nombre_cliente, 
                               m.nombre as nombre_mascota, u_pro.nombre as nombre_pro, s.nombre as nombre_serv
                        FROM usuarios u_cli
                        INNER JOIN mascotas m ON m.id_cliente = u_cli.id
                        INNER JOIN usuarios u_pro ON u_pro.id = ?
                        INNER JOIN servicios s ON s.id = ?
                        WHERE m.id = ? AND u_cli.id = ?";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->bind_param("iiii", $id_pro, $id_serv, $id_mascota, $_SESSION['usuario_id']);
            $stmtInfo->execute();
            $infoMail = $stmtInfo->get_result()->fetch_assoc();

            // --- ENVÍO DE EMAIL CON PHPMAILER ---
            $mail = new PHPMailer(true);
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Destinatarios
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Veterinaria San Antón');
            $mail->addAddress($infoMail['mail_cliente'], $infoMail['nombre_cliente']);

            // Contenido del mail
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de Turno - San Antón';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: #00897b;'>¡Hola {$infoMail['nombre_cliente']}!</h2>
                    <p>Tu turno ha sido confirmado con éxito.</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #00897b;'>
                        <p><strong>Mascota:</strong> {$infoMail['nombre_mascota']}</p>
                        <p><strong>Servicio:</strong> {$infoMail['nombre_serv']}</p>
                        <p><strong>Profesional:</strong> {$infoMail['nombre_pro']}</p>
                        <p><strong>Fecha y Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_datetime)) . "</p>
                        <p><strong>Modalidad:</strong> $modalidad</p>
                    </div>
                    <p>Gracias por confiar en San Antón.</p>
                </div>
            ";

            $mail->send();
            // -------------------------------------

            $conn->commit();
            $_SESSION['turno_exitoso'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='alert alert-danger'>El turno se agendó pero no se pudo enviar el mail: {$mail->ErrorInfo}</div>";
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            echo "<div class='alert alert-danger'>Error en la base de datos: {$e->getMessage()}</div>";
        }
    }
    $stmtCheck->close();
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
    $turnoExitoso = true;
    unset($_SESSION['turno_exitoso']);
}

// --- OBTENCIÓN DE DATOS ---
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno - Por Profesional</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../styles.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        .card-profesional {
            border: none;
            border-left: 5px solid #00897b;
            /* Borde lateral Teal */
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-profesional:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .text-teal {
            color: #00897b;
        }

        .bg-teal {
            background-color: #00897b;
            color: white;
        }

        .booking-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        /* Input de búsqueda estilizado */
        .search-input {
            border-radius: 50px;
            padding-left: 45px;
            height: 50px;
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 15px;
            color: #00897b;
            font-size: 1.2rem;
            z-index: 10;
        }
    </style>
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <?php if ($errorMascotaOcupada): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>¡Atención!</strong> Esta mascota ya tiene un turno
                en ese horario.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
            <h1 class="mb-0 font-weight-bold">Elegí a tu Profesional</h1>
            <p class="mb-0 mt-1" style="opacity: 0.9;">Busca por nombre y selecciona el horario que mejor te convenga
            </p>
        </div>

        <div class="d-flex justify-content-start mb-4">
            <a href="solicitar-turno.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver a selección
            </a>
        </div>

        <div class="form-group mb-5 position-relative">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="filtroProfesionales" class="form-control search-input shadow-sm"
                placeholder="Escribe el nombre del veterinario...">
        </div>

        <div class="row">
            <?php foreach ($profesionales as $profesional): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-profesional shadow-sm h-100">
                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px; color: #00897b;">
                                        <i class="fas fa-user-md fa-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                                        <?= htmlspecialchars($profesional['nombre']) ?></h5>
                                    <small class="text-uppercase text-teal font-weight-bold" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($profesional['especialidad']) ?>
                                    </small>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3" style="font-size: 0.9rem;"><i class="far fa-clock mr-1"></i>
                                Horarios de atención:</h6>
                            <ul class="list-unstyled mb-3 small text-secondary">
                                <?php
                                $diasAtencion = $horariosPorProfesional[$profesional['id']] ?? [];
                                if (!empty($diasAtencion)): ?>
                                    <?php foreach ($diasAtencion as $horario): ?>
                                        <li class="mb-1">
                                            <i class="fas fa-calendar-day mr-2 text-teal"></i>
                                            <strong><?= $horario['diaSem'] ?>:</strong> <?= $horario['horaIni'] ?> -
                                            <?= $horario['horaFin'] ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-muted font-italic">Sin horarios asignados.</li>
                                <?php endif; ?>
                            </ul>

                            <div class="text-center mt-auto">
                                <button type="button"
                                    class="btn btn-outline-info btn-block rounded-pill font-weight-bold mostrar-formulario-btn"
                                    style="color: #00897b; border-color: #00897b;">
                                    Sacar Turno
                                </button>
                            </div>

                            <form class="booking-form mt-3" data-id-pro="<?= $profesional['id'] ?>"
                                data-pro-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                                data-id-esp="<?= $profesional['id_esp'] ?>" style="display:none;">

                                <h6 class="text-teal font-weight-bold mb-3">Reservar Cita</h6>

                                <div class="form-group">
                                    <label class="small font-weight-bold">Fecha:</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_turno"
                                        min="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="small font-weight-bold">Hora:</label>
                                    <select class="form-control form-control-sm" name="hora_turno" required disabled>
                                        <option value="" disabled selected>Seleccione fecha primero</option>
                                    </select>
                                    <small class="form-text text-danger" style="display:none;"></small>
                                </div>

                                <button type="button"
                                    class="btn btn-success btn-sm btn-block sacar-turno-btn font-weight-bold shadow-sm"
                                    disabled>
                                    Continuar
                                </button>
                                <button type="button"
                                    class="btn btn-link btn-sm btn-block text-secondary cancelar-turno-btn">
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-clipboard-check mr-2"></i> Confirmar Turno
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border text-center mb-4">
                        <h5 class="text-teal mb-0" id="summary-profesional"></h5>
                        <small class="text-muted">Profesional Seleccionado</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha:</small>
                            <strong id="summary-fecha" style="font-size: 1.1rem;"></strong>
                        </div>
                        <div class="col-6 text-right">
                            <small class="text-muted d-block">Hora:</small>
                            <strong id="summary-hora" style="font-size: 1.1rem;"></strong>
                        </div>
                    </div>

                    <form method="POST" id="confirmacionForm">
                        <input type="hidden" name="profesional_id" id="form-profesional-id">
                        <input type="hidden" name="fecha_turno" id="form-fecha">
                        <input type="hidden" name="hora_turno" id="form-hora">
                        <input type="hidden" name="id_serv" id="form-service-id">

                        <div class="form-group">
                            <label class="font-weight-bold">¿Qué mascota vendrá?</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-paw text-teal"></i></span>
                                </div>
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
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Servicio:</label>
                            <select class="form-control" name="id_serv" id="id_serv_modal" required>
                                <option value="">Cargando servicios...</option>
                            </select>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">Costo estimado:</small>
                                <strong class="text-success" id="summary-precio">--</strong>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="font-weight-bold d-block mb-2">Modalidad de atención:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active">
                                    <input type="radio" name="modalidad" value="Presencial" checked>
                                    <i class="fas fa-hospital mr-1"></i> Presencial
                                </label>
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="modalidad" value="A domicilio">
                                    <i class="fas fa-home mr-1"></i> A domicilio
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-block btn-lg mt-4 font-weight-bold shadow-sm">
                            CONFIRMAR RESERVA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="turnoExitosoModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg text-center">
                <div class="modal-header bg-success text-white justify-content-center">
                    <h5 class="modal-title font-weight-bold">¡Reserva Exitosa!</h5>
                </div>
                <div class="modal-body p-5">
                    <div class="mb-4 text-success">
                        <i class="fas fa-check-circle fa-5x"></i>
                    </div>
                    <h4 class="mb-3">¡Tu turno está confirmado!</h4>
                    <p class="text-muted">Hemos enviado los detalles a tu correo electrónico.</p>
                </div>
                <div class="modal-footer justify-content-center bg-light">
                    <a href="mis-turnos.php" class="btn btn-primary px-4">Ir a Mis Turnos</a>
                    <a href="solicitar-turno-profesional.php" class="btn btn-outline-secondary px-4">Nueva Reserva</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            const diasSemana = { 'Lun': 1, 'Mar': 2, 'Mie': 3, 'Jue': 4, 'Vie': 5, 'Sab': 6, 'Dom': 0 };
            const horariosProfesionales = <?php echo json_encode($horariosPorProfesional); ?>;
            const servicios = <?php echo json_encode($servicios); ?>;

            const serviciosPorEspecialidad = {};
            servicios.forEach(s => {
                if (!serviciosPorEspecialidad[s.id_esp]) serviciosPorEspecialidad[s.id_esp] = [];
                serviciosPorEspecialidad[s.id_esp].push(s);
            });

            // Filtro de búsqueda
            $('#filtroProfesionales').on('input', function () {
                const searchTerm = $(this).val().toLowerCase();
                $('.card-profesional').each(function () {
                    const nombrePro = $(this).find('.card-title').text().toLowerCase();
                    $(this).closest('.col-md-6').toggle(nombrePro.includes(searchTerm));
                });
            });

            // Mostrar/Ocultar formulario en tarjeta
            $('.mostrar-formulario-btn').on('click', function () {
                $(this).hide().closest('.card-body').find('.booking-form').slideDown();
            });

            $('.cancelar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                form.slideUp(function () {
                    form.closest('.card-body').find('.mostrar-formulario-btn').show();
                    form.find('input[type="date"]').val('');
                    form.find('select[name="hora_turno"]').empty().prop('disabled', true).append('<option value="" disabled selected>Seleccione fecha primero</option>');
                    form.find('.sacar-turno-btn').prop('disabled', true);
                    form.find('.form-text').hide();
                });
            });

            // Lógica de Fechas y Horarios
            $('.booking-form').on('change', 'input[type="date"]', function () {
                const form = $(this).closest('.booking-form');
                const proId = form.data('id-pro');
                const fecha = $(this).val();
                const horaSelect = form.find('select[name="hora_turno"]');
                const errorSpan = form.find('.form-text');

                horaSelect.prop('disabled', true).empty().append('<option value="" disabled selected>Cargando...</option>');
                form.find('.sacar-turno-btn').prop('disabled', true);
                errorSpan.hide();

                if (fecha) {
                    const fechaObj = new Date(fecha.replace(/-/g, '/'));
                    const diaStr = Object.keys(diasSemana).find(key => diasSemana[key] === fechaObj.getDay());
                    const horariosPro = horariosProfesionales[proId];

                    if (horariosPro && horariosPro.find(h => h.diaSem === diaStr)) {
                        $.ajax({
                            url: 'verificar-turno-disponible.php',
                            method: 'POST',
                            dataType: 'json',
                            data: { id_pro: proId, fecha: fecha },
                            success: function (disponibles) {
                                horaSelect.empty().append('<option value="" disabled selected>Seleccione hora</option>');
                                if (disponibles.length > 0) {
                                    disponibles.forEach(h => horaSelect.append(`<option value="${h}">${h.substring(0, 5)}</option>`));
                                    horaSelect.prop('disabled', false);
                                } else {
                                    errorSpan.text('No hay horarios disponibles.').show();
                                }
                            }
                        });
                    } else {
                        horaSelect.empty().append('<option value="" disabled selected>Día no laboral</option>');
                        errorSpan.text('El profesional no atiende este día.').show();
                    }
                }
            });

            $('.booking-form').on('change', 'select[name="hora_turno"]', function () {
                $(this).closest('.booking-form').find('.sacar-turno-btn').prop('disabled', false);
            });

            // Abrir Modal de Confirmación
            $('.sacar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                const idEsp = form.data('id-esp');

                $('#summary-profesional').text(form.data('pro-nombre'));
                $('#summary-fecha').text(form.find('input[name="fecha_turno"]').val().split('-').reverse().join('/'));
                $('#summary-hora').text(form.find('select[name="hora_turno"]').val());

                $('#form-profesional-id').val(form.data('id-pro'));
                $('#form-fecha').val(form.find('input[name="fecha_turno"]').val());
                $('#form-hora').val(form.find('select[name="hora_turno"]').val());

                const selectServ = $('#id_serv_modal').empty().append('<option value="">Selecciona un servicio</option>');
                (serviciosPorEspecialidad[idEsp] || []).forEach(s => {
                    selectServ.append(`<option value="${s.id}" data-precio="${s.precio}">${s.nombre}</option>`);
                });
                $('#summary-precio').text('--');

                $('#confirmacionModal').modal('show');
            });

            // Actualizar precio en modal
            $('#id_serv_modal').on('change', function () {
                const precio = $(this).find(':selected').data('precio');
                $('#summary-precio').text(precio ? `$${precio}` : '--');
                $('#form-service-id').val($(this).val());
            });

            // Mostrar modal de éxito si PHP lo indica
            if (<?php echo json_encode($turnoExitoso); ?>) {
                $('#turnoExitosoModal').modal('show');
            }
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>