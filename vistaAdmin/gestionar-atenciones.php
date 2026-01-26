<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$usuarioTipo = $_SESSION['usuario_tipo']; // 'admin' o 'especialista'
$usuarioNombre = $_SESSION['usuario_nombre'];

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


if ($usuarioTipo === 'admin') {
   
    $resMascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");
    $resEspecialistas = $conn->query("SELECT id, nombre FROM usuarios WHERE tipo = 'especialista' ORDER BY nombre ASC");
} elseif ($usuarioTipo === 'especialista') {
   
    $resMascotas = $conn->query("
        SELECT DISTINCT m.id, m.nombre 
        FROM mascotas m
        INNER JOIN atenciones a ON m.id = a.id_mascota
        WHERE a.id_pro = $usuarioId
        ORDER BY m.nombre ASC
    ");
    if (!$resMascotas) {
        die("Error en la consulta de mascotas: " . $conn->error);
    }
}

$resServicios = $conn->query("SELECT id, nombre FROM servicios ORDER BY nombre ASC");
if (!$resServicios) {
    die("Error en la consulta de servicios: " . $conn->error);
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
        <h2 class="text-center mb-4">Panel de Gestión de Atenciones</h2>

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
                        <?php if ($usuarioTipo === 'especialista'): ?>
                            <div class="form-group">
                                <label>Especialista</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuarioNombre); ?>" disabled>
                                <input type="hidden" name="especialista_id" value="<?php echo $usuarioId; ?>">
                            </div>
                        <?php elseif ($usuarioTipo === 'admin'): ?>
                            <div class="form-group">
                                <label>Especialista</label>
                                <select class="form-control" name="especialista_id" required>
                                    <option value="">Seleccione médico...</option>
                                    <?php $resEspecialistas->data_seek(0);
                                    while ($e = $resEspecialistas->fetch_assoc()): ?>
                                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <?php endif; ?>
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

    
    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="alertModalLabel">Advertencia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="alertModalBody">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'especialista_ocupado'): ?>
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>El especialista seleccionado ya tiene una atención programada en el horario indicado.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                
                $('#errorModal').modal('show');

               
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'mascota_ocupada'): ?>
        <div class="modal fade" id="errorMascotaModal" tabindex="-1" role="dialog" aria-labelledby="errorMascotaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorMascotaModalLabel">Error</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>La mascota seleccionada ya tiene una atención programada en el horario indicado.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                
                $('#errorMascotaModal').modal('show');

                
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                events: '../shared/atenciones.php',
                dateClick: function (info) {
                    document.getElementById('fecha_input').value = info.dateStr;
                },
                eventClick: function (info) {
                    var idAtencion = info.event.id;
                    var titulo = info.event.title;

                    document.getElementById('textoDetalle').innerText = titulo;
                    document.getElementById('btnVerMas').href = '../shared/detalle-atencionAP.php?id=' + idAtencion;

                    $('#modalDetalles').modal('show');
                }
            });
            calendar.render();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            $('#fecha_input').attr('min', today);

            
            $('form').on('submit', function (e) {
                const fechaSeleccionada = $('#fecha_input').val();
                const horaSeleccionada = $('select[name="hora"]').val();

                if (!fechaSeleccionada || !horaSeleccionada) {
                    $('#alertModalBody').text('Por favor, selecciona una fecha y hora válidas.');
                    $('#alertModal').modal('show');
                    e.preventDefault();
                    return;
                }

                const fechaHoraSeleccionada = new Date(`${fechaSeleccionada}T${horaSeleccionada}:00`);
                const fechaHoraActual = new Date();

                if (fechaHoraSeleccionada <= fechaHoraActual) {
                    $('#alertModalBody').text('No puedes registrar un turno en una fecha u horario anterior al actual.');
                    $('#alertModal').modal('show');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>