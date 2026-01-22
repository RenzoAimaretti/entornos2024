<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

// Cargar Selectores Iniciales
$resMascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");
// Los servicios se cargan dinámicamente según el especialista seleccionado
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

                    <?php if (isset($_GET['res']) && $_GET['res'] == 'ok'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            ¡Turno registrado! <button class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error']) && $_GET['error'] == 'especialista_ocupado'): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            El especialista ya tiene un turno en ese horario. <button class="close"
                                data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error']) && $_GET['error'] == 'mascota_ocupada'): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            Esa mascota ya tiene un turno pendiente en el horario seleccionado. <button class="close"
                                data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <form action="../shared/alta-atencion.php" method="POST">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha_input" required
                                min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="form-group">
                            <label>Especialista</label>
                            <select class="form-control" name="especialista_id" id="especialista_id" required disabled>
                                <option value="">Seleccione fecha primero</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Servicio</label>
                            <select class="form-control" name="servicio_id" id="servicio_select" required disabled>
                                <option value="">Seleccione especialista primero</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Hora Disponible</label>
                            <select class="form-control" name="hora" id="hora_select" required disabled>
                                <option value="">Elija médico</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mascota</label>
                            <select class="form-control" name="mascota_id" required>
                                <option value="">Seleccione...</option>
                                <?php
                                if ($resMascotas) {
                                    while ($m = $resMascotas->fetch_assoc()):
                                        ?>
                                        <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nombre']); ?></option>
                                        <?php
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Confirmar Cita</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            const fechaInput = $('#fecha_input');
            const espSelect = $('#especialista_id');
            const horaSelect = $('#hora_select');

            // 1. CARGAR MÉDICOS DEL DÍA (SIN DUPLICADOS)
            fechaInput.on('change', function () {
                const fecha = $(this).val();
                if (!fecha) return;

                espSelect.html('<option value="">Buscando...</option>').prop('disabled', true);
                horaSelect.html('<option value="">Esperando...</option>').prop('disabled', true);
                $('#servicio_select').html('<option value="">Seleccione especialista primero</option>').prop('disabled', true);

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
                }, 'json').fail(function () {
                    espSelect.html('<option value="">Error de conexión</option>');
                });
            });

            // 2. CARGAR SERVICIOS Y HORAS DISPONIBLES
            espSelect.on('change', function () {
                const idPro = $(this).val();
                if (!idPro) {
                    $('#servicio_select').html('<option value="">Seleccione especialista primero</option>').prop('disabled', true);
                    horaSelect.prop('disabled', true);
                    return;
                }

                // Cargar servicios del especialista
                $('#servicio_select').html('<option value="">Cargando servicios...</option>').prop('disabled', true);
                $.post('../shared/obtener-servicios-especialista.php', { id_especialista: idPro }, function (data) {
                    $('#servicio_select').html('<option value="">Seleccione servicio</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function (s) {
                            $('#servicio_select').append(`<option value="${s.id}">${s.nombre}</option>`);
                        });
                        $('#servicio_select').prop('disabled', false);
                    } else {
                        $('#servicio_select').html('<option value="">Sin servicios disponibles</option>');
                    }
                }, 'json').fail(function () {
                    $('#servicio_select').html('<option value="">Error de conexión</option>');
                });

                horaSelect.html('<option value="">Cargando horas...</option>').prop('disabled', true);

                $.post('../shared/obtener-horas-especialista.php', { id_pro: idPro, fecha: fechaInput.val() }, function (data) {
                    horaSelect.html('<option value="">Seleccione hora</option>');
                    if (data.disponibles && data.disponibles.length > 0) {
                        data.disponibles.forEach(function (h) {
                            horaSelect.append(`<option value="${h}">${h}</option>`);
                        });
                        horaSelect.prop('disabled', false);
                    } else {
                        horaSelect.html('<option value="">Sin turnos libres</option>');
                    }
                }, 'json');
            });

            var calendar = new FullCalendar.Calendar(document.getElementById('calendario'), {
                locale: 'es',
                initialView: 'dayGridMonth',
                events: '../shared/atenciones.php',
                dateClick: function (info) {
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