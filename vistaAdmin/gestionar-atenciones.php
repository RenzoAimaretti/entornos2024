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

// Cargar Selectores Iniciales (Mascotas) - Ahora se hace dinámicamente

// Cargar Servicios para Especialista
$serviciosEspecialista = [];
if (!$esAdmin) {
    $stmtServ = $conn->prepare("SELECT s.id, s.nombre FROM servicios s INNER JOIN especialidad e ON s.id_esp = e.id INNER JOIN profesionales p ON p.id_esp = e.id WHERE p.id = ?");
    $stmtServ->bind_param("i", $idUsuario);
    $stmtServ->execute();
    $resServ = $stmtServ->get_result();
    $serviciosEspecialista = $resServ->fetch_all(MYSQLI_ASSOC);
    $stmtServ->close();
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
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php
                            if ($_GET['error'] == 'especialista_ocupado')
                                echo "El especialista ya tiene turno.";
                            elseif ($_GET['error'] == 'mascota_ocupada')
                                echo "La mascota ya tiene turno.";
                            else
                                echo "Error desconocido.";
                            ?>
                            <button class="close" data-dismiss="alert">&times;</button>
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
                            <select class="form-control" name="servicio_id" id="servicio_select" required <?php if (!$esAdmin)
                                echo 'enabled';
                            else
                                echo 'disabled'; ?>>
                                <?php if (!$esAdmin): ?>
                                    <option value="">Seleccione servicio</option>
                                    <?php foreach ($serviciosEspecialista as $serv): ?>
                                        <option value="<?= $serv['id'] ?>"><?= htmlspecialchars($serv['nombre']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Seleccione especialista primero</option>
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
                            <select class="form-control" name="mascota_id" id="mascota_select" required disabled>
                                <option value="">Cargando...</option>
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
            // Variables de entorno PHP a JS
            const esAdmin = <?= json_encode($esAdmin); ?>;
            const idUsuarioLogueado = <?= json_encode($idUsuario); ?>;

            const fechaInput = $('#fecha_input');
            const espSelect = $('#especialista_select'); // Solo existe si es Admin
            const servicioSelect = $('#servicio_select');
            const horaSelect = $('#hora_select');
            const mascotaSelect = $('#mascota_select');

            // --- FUNCIONES AJAX SEPARADAS ---

            // 1. Cargar Servicios (Solo requiere ID del profesional)
            function cargarServicios(idPro) {
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
                }, 'json').fail(function () {
                    servicioSelect.html('<option value="">Error al cargar</option>');
                });
            }

            // 2. Cargar Horas (Requiere ID Profesional + Fecha)
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
                }, 'json').fail(function () {
                    horaSelect.html('<option value="">Error al cargar</option>');
                });
            }

            // 3. Cargar Mascotas (Requiere ID Profesional)
            function cargarMascotas(idPro) {
                mascotaSelect.html('<option value="">Cargando mascotas...</option>').prop('disabled', true);

                $.post('../shared/obtener-mascotas-especialista.php', { id_pro: idPro }, function (data) {
                    mascotaSelect.html('<option value="">Seleccione mascota</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function (m) {
                            mascotaSelect.append(`<option value="${m.id}">${m.nombre}</option>`);
                        });
                        mascotaSelect.prop('disabled', false);
                    } else {
                        mascotaSelect.html('<option value="">No hay mascotas atendidas</option>');
                    }
                }, 'json').fail(function () {
                    mascotaSelect.html('<option value="">Error al cargar</option>');
                });
            }

            // 4. Cargar Todas las Mascotas (Para Admin)
            function cargarTodasMascotas() {
                mascotaSelect.html('<option value="">Cargando mascotas...</option>').prop('disabled', true);

                $.post('../shared/obtener-todas-mascotas.php', {}, function (data) {
                    mascotaSelect.html('<option value="">Seleccione mascota</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function (m) {
                            mascotaSelect.append(`<option value="${m.id}">${m.nombre}</option>`);
                        });
                        mascotaSelect.prop('disabled', false);
                    } else {
                        mascotaSelect.html('<option value="">No hay mascotas</option>');
                    }
                }, 'json').fail(function () {
                    mascotaSelect.html('<option value="">Error al cargar</option>');
                });
            }

            // --- INICIALIZACIÓN ---

            if (!esAdmin) {
                // CASO ESPECIALISTA:
                // Servicios ya cargados en PHP, no hacer nada más aquí
                cargarMascotas(idUsuarioLogueado);
            } else {
                // CASO ADMIN:
                // Mensaje inicial en servicios y mascotas
                servicioSelect.html('<option value="">Seleccione especialista primero</option>');
                mascotaSelect.html('<option value="">Seleccione especialista primero</option>');
            }

            // --- EVENTOS ---

            // Cambio de Fecha
            fechaInput.on('change', function () {
                const fecha = $(this).val();
                if (!fecha) return;

                if (esAdmin) {
                    // ADMIN: Buscar médicos disponibles ese día
                    espSelect.html('<option value="">Buscando...</option>').prop('disabled', true);
                    servicioSelect.html('<option value="">Seleccione especialista primero</option>').prop('disabled', true);
                    horaSelect.html('<option value="">Esperando...</option>').prop('disabled', true);
                    mascotaSelect.html('<option value="">Esperando...</option>').prop('disabled', true);

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
                    // ESPECIALISTA: Ya tenemos ID y Servicios cargados, solo cargamos HORAS
                    cargarHoras(idUsuarioLogueado, fecha);
                }
            });

            // Cambio de Especialista (SOLO ADMIN)
            if (esAdmin) {
                espSelect.on('change', function () {
                    const idPro = $(this).val();
                    const fecha = fechaInput.val();

                    if (idPro) {
                        cargarServicios(idPro); // Cargar servicios del médico elegido
                        cargarTodasMascotas(); // Cargar todas las mascotas para admin
                        if (fecha) {
                            cargarHoras(idPro, fecha); // Cargar horas si ya hay fecha
                        }
                    }
                });
            }

            // --- CALENDARIO FULLCALENDAR ---
            var calendar = new FullCalendar.Calendar(document.getElementById('calendario'), {
                locale: 'es',
                initialView: 'dayGridMonth',
                // IMPORTANTE: El archivo atenciones.php debe filtrar por $_SESSION['usuario_id'] si es especialista
                events: '../shared/atenciones.php',
                dateClick: function (info) {
                    // Al hacer click en el calendario, llena la fecha en el input
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