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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Atenciones - San Antón</title>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js'></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">

    <style>
        #calendario {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .contenedor-registro {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container-fluid mt-4 px-lg-5">
        <h2 class="text-center mb-4">Panel de Gestión de Atenciones</h2>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div id="calendario"></div>
            </div>

            <div class="col-lg-4">
                <div class="contenedor-registro">
                    <h4 class="mb-4 text-primary border-bottom pb-2">Registrar Nueva Atención</h4>
                    <form action="../shared/alta-atencion.php" method="POST">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha_input" required>
                        </div>
                        <div class="form-group">
                            <label>Hora</label>
                            <select class="form-control" name="hora" required>
                                <option value="">Seleccione hora</option>
                                <?php
                                for ($h = 9; $h <= 18; $h++) {
                                    foreach (['00', '30'] as $m) {
                                        $hora = sprintf("%02d:%s", $h, $m);
                                        echo "<option value='$hora'>$hora</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mascota</label>
                            <select class="form-control" name="mascota_id" required>
                                <option value="">Seleccione mascota...</option>
                                <?php $resMascotas->data_seek(0);
                                while ($m = $resMascotas->fetch_assoc()): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
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
                            <label>Servicio</label>
                            <select class="form-control" name="servicio_id" required>
                                <option value="">Seleccione servicio...</option>
                                <?php $resServicios->data_seek(0);
                                while ($s = $resServicios->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Registrar Atención</button>
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
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día' },
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