<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

// 1. Detectar Rol y ID
$esAdmin = ($_SESSION['usuario_tipo'] === 'admin');
$idUsuario = $_SESSION['usuario_id'];
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Especialista';

// 2. Cargar Selectores Iniciales (Mascotas)
$resMascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");

// 3. Cargar servicios del profesional logueado (Server-Side)
$serviciosDelProfesional = [];
if (!$esAdmin) {
    $stmtEsp = $conn->prepare("SELECT id_esp FROM profesionales WHERE id = ?");
    $stmtEsp->bind_param("i", $idUsuario);
    $stmtEsp->execute();
    $resEsp = $stmtEsp->get_result();

    if ($rowEsp = $resEsp->fetch_assoc()) {
        $idEspecialidad = $rowEsp['id_esp'];
        $stmtServ = $conn->prepare("SELECT id, nombre FROM servicios WHERE id_esp = ? ORDER BY nombre ASC");
        $stmtServ->bind_param("i", $idEspecialidad);
        $stmtServ->execute();
        $resServ = $stmtServ->get_result();
        while ($rowS = $resServ->fetch_assoc()) {
            $serviciosDelProfesional[] = $rowS;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Atenciones - San Antón</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container-fluid mt-4 px-lg-5">
        <h2 class="text-center mb-4">Gestión de Atenciones</h2>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div id="calendario" class="bg-white p-3 shadow-sm rounded border"></div>
            </div>

            <div class="col-lg-4">
                <div class="bg-white p-4 shadow-sm rounded border">
                    <h4 class="text-primary mb-3 border-bottom pb-2">Registrar Turno</h4>

                    <form action="../shared/alta-atencion.php" method="POST">

                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha_input" required
                                min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-group">
                            <label>Especialista</label>
                            <?php if ($esAdmin): ?>
                                <select class="form-control" name="especialista_id" id="especialista_select" required
                                    disabled>
                                    <option value="">Seleccione fecha primero</option>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($nombreUsuario) ?>"
                                    disabled style="background-color: #e9ecef;">
                                <input type="hidden" name="especialista_id" id="especialista_hidden"
                                    value="<?= $idUsuario ?>">
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Servicio</label>
                            <select class="form-control" name="servicio_id" id="servicio_select" required <?= $esAdmin ? 'disabled' : '' ?>>
                                <?php if ($esAdmin): ?>
                                    <option value="">Seleccione especialista primero</option>
                                <?php else: ?>
                                    <option value="">Seleccione servicio</option>
                                    <?php foreach ($serviciosDelProfesional as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                    <?php endforeach; ?>
                                    <?php if (empty($serviciosDelProfesional)): ?>
                                        <option value="" disabled>No hay servicios asignados</option>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Hora Disponible</label>
                            <select class="form-control" name="hora" id="hora_select" required disabled>
                                <option value="">Seleccione fecha primero</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mascota</label>
                            <select class="form-control" name="mascota_id" required>
                                <option value="">Seleccione...</option>
                                <?php if ($resMascotas):
                                    while ($m = $resMascotas->fetch_assoc()): ?>
                                        <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nombre']); ?></option>
                                    <?php endwhile; endif; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                            <i class="fas fa-save mr-2"></i> Confirmar Cita
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalle Turno</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <p id="textoDetalle" class="lead"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <a id="btnVerMas" href="#" class="btn btn-primary">Ver Ficha</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExitoTurno" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-calendar-check text-success fa-5x"></i>
                    </div>
                    <h2 class="font-weight-bold text-success mb-3">¡Turno Confirmado!</h2>
                    <p class="lead text-muted mb-4">La cita ha sido registrada correctamente.</p>
                    <button type="button" class="btn btn-success btn-lg px-5 rounded-pill shadow" data-dismiss="modal">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalErrorTurno" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold">No se pudo registrar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-5x"></i>
                    </div>
                    <h3 class="font-weight-bold text-danger mb-3">¡Conflicto de Horario!</h3>
                    <p class="lead text-muted mb-4" id="mensajeErrorTexto">
                        Ocurrió un error al intentar reservar el turno.
                    </p>
                    <button type="button" class="btn btn-outline-danger btn-lg px-5 rounded-pill" data-dismiss="modal">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // DETECTAR PARÁMETROS URL PARA MOSTRAR MODALES
            const urlParams = new URLSearchParams(window.location.search);

            // CASO ÉXITO
            if (urlParams.get('res') === 'ok') {
                $('#modalExitoTurno').modal('show');
            }

            // CASO ERROR
            if (urlParams.has('error')) {
                const errorType = urlParams.get('error');
                let mensaje = "Ocurrió un error desconocido.";

                if (errorType === 'especialista_ocupado') {
                    mensaje = "El especialista ya tiene un turno asignado en ese horario.";
                } else if (errorType === 'mascota_ocupada') {
                    mensaje = "Esta mascota ya tiene un turno pendiente en ese mismo horario.";
                }

                $('#mensajeErrorTexto').text(mensaje);
                $('#modalErrorTurno').modal('show');
            }

            // Limpiar URL para que no salga al recargar
            if (urlParams.has('res') || urlParams.has('error')) {
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }

            // Variables y lógica de FullCalendar / AJAX...
            const esAdmin = <?= json_encode($esAdmin); ?>;
            const idUsuarioLogueado = <?= json_encode($idUsuario); ?>;

            const fechaInput = $('#fecha_input');
            const espSelect = $('#especialista_select');
            const servicioSelect = $('#servicio_select');
            const horaSelect = $('#hora_select');

            // --- FUNCIONES AJAX ---
            function cargarServiciosAdmin(idPro) {
                servicioSelect.html('<option value="">Cargando servicios...</option>').prop('disabled', true);
                $.post('../shared/obtener-servicios-especialista.php', { id_especialista: idPro }, function (data) {
                    servicioSelect.html('<option value="">Seleccione servicio</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function (s) {
                            servicioSelect.append(`<option value="${s.id}">${s.nombre}</option>`);
                        });
                        servicioSelect.prop('disabled', false);
                    } else {
                        servicioSelect.html('<option value="">Sin servicios disponibles</option>');
                    }
                }, 'json');
            }

            function cargarHoras(idPro, fecha) {
                horaSelect.html('<option value="">Cargando horas...</option>').prop('disabled', true);
                $.post('../shared/obtener-horas-especialista.php', { id_pro: idPro, fecha: fecha }, function (data) {
                    horaSelect.html('<option value="">Seleccione hora</option>');
                    if (data.disponibles && data.disponibles.length > 0) {
                        let disponibles = data.disponibles;
                        const hoy = new Date().toISOString().split('T')[0];
                        const ahora = new Date();
                        if (fecha === hoy) {
                            disponibles = disponibles.filter(h => {
                                const [hora, min] = h.split(':').map(Number);
                                const horaDate = new Date();
                                horaDate.setHours(hora, min, 0, 0);
                                return horaDate > ahora;
                            });
                        }
                        if (disponibles.length > 0) {
                            disponibles.forEach(function (h) {
                                horaSelect.append(`<option value="${h}">${h}</option>`);
                            });
                            horaSelect.prop('disabled', false);
                        } else {
                            horaSelect.html('<option value="">No hay horas disponibles para hoy</option>');
                        }
                    } else {
                        horaSelect.html('<option value="">Sin turnos libres</option>');
                    }
                }, 'json');
            }

            // --- EVENTOS ---
            fechaInput.on('change', function () {
                const fecha = $(this).val();
                if (!fecha) return;

                if (esAdmin) {
                    espSelect.html('<option value="">Buscando...</option>').prop('disabled', true);
                    servicioSelect.html('<option value="">Seleccione especialista primero</option>').prop('disabled', true);
                    horaSelect.html('<option value="">Esperando...</option>').prop('disabled', true);

                    $.post('../shared/obtener-medicos-por-fecha.php', { fecha: fecha }, function (data) {
                        espSelect.html('<option value="">Seleccione médico</option>');
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function (m) {
                                espSelect.append(`<option value="${m.id}">${m.nombre}</option>`);
                            });
                            espSelect.prop('disabled', false);
                        } else {
                            espSelect.html('<option value="">Sin médicos este día</option>');
                        }
                    }, 'json');

                } else {
                    cargarHoras(idUsuarioLogueado, fecha);
                }
            });

            if (esAdmin) {
                espSelect.on('change', function () {
                    const idPro = $(this).val();
                    const fecha = fechaInput.val();
                    if (idPro) {
                        cargarServiciosAdmin(idPro);
                        if (fecha) {
                            cargarHoras(idPro, fecha);
                        }
                    }
                });
            }

            // --- CALENDARIO ---
            var calendar = new FullCalendar.Calendar(document.getElementById('calendario'), {
                locale: 'es',
                initialView: 'dayGridMonth',
                events: '../shared/atenciones.php',
                dateClick: function (info) {
                    // Validar fecha pasada
                    var clickedDate = new Date(info.dateStr);
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (clickedDate < today) return;

                    fechaInput.val(info.dateStr).trigger('change');
                },
                eventClick: function (info) {
                    $('#textoDetalle').text(info.event.title);
                    $('#btnVerMas').attr('href', '../shared/detalle-atencionAP.php?id=' + info.event.id);
                    $('#modalDetalles').modal('show');
                }
            });
            calendar.render();
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>