<?php
session_start();

// 1. Conexión a la base de datos para cargar los selectores
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 2. Obtener Mascotas
$resMascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");

// 3. Obtener Especialistas (Asegúrate de usar el nombre correcto de la columna: 'tipo')
$resEspecialistas = $conn->query("SELECT id, nombre FROM usuarios WHERE tipo = 'especialista' ORDER BY nombre ASC");

// 4. Obtener Servicios
$resServicios = $conn->query("SELECT id, nombre FROM servicios ORDER BY nombre ASC");
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
        <div class="container-fluid px-lg-5">
            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 'mascota_ocupada'): ?>
                    <div class="alert alert-danger">La mascota ya tiene una atención programada para ese día y hora.</div>
                <?php elseif ($_GET['error'] == 'especialista_ocupado'): ?>
                    <div class="alert alert-warning">El especialista ya tiene otro turno asignado en ese horario.</div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['res']) && $_GET['res'] == 'ok'): ?>
                <div class="alert alert-success">Atención registrada con éxito.</div>
            <?php endif; ?>
        </div>
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
                                <?php while ($m = $resMascotas->fetch_assoc()): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Especialista</label>
                            <select class="form-control" name="especialista_id" required>
                                <option value="">Seleccione médico...</option>
                                <?php while ($e = $resEspecialistas->fetch_assoc()): ?>
                                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Servicio</label>
                            <select class="form-control" name="servicio_id" required>
                                <option value="">Seleccione servicio...</option>
                                <?php while ($s = $resServicios->fetch_assoc()): ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. DESAPARECER ALERTAS AUTOMÁTICAMENTE
            // Busca todas las alertas y las oculta después de 5 segundos
            setTimeout(function () {
                $('.alert').fadeOut('slow', function () {
                    // Una vez que se desvanecen, limpiamos la URL para que no queden los parámetros ?res=ok o ?error=...
                    const url = new URL(window.location);
                    url.searchParams.delete('res');
                    url.searchParams.delete('error');
                    window.history.replaceState({}, document.title, url);
                });
            }, 5000);

            // 2. CONFIGURACIÓN DEL CALENDARIO
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
                    // Opcional: Al hacer clic en el calendario, ocultar alertas manualmente si aún existen
                    $('.alert').fadeOut();
                },

                eventClick: function (info) {
                    if (confirm(`¿Ver detalles de: "${info.event.title}"?`)) {
                        window.location.href = '../shared/detalle-atencionAP.php?id=' + info.event.id;
                    }
                }
            });
            calendar.render();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    $conn->close();
    require_once '../shared/footer.php';
    ?>
</body>

</html>