<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}
$profesionalId = $_SESSION['usuario_id'];
$nombreProfesional = $_SESSION['usuario_nombre'] ?? '';


require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
// Mascotas atendidas por el profesional
$mascotas = [];
if ($profesionalId) {
    $stmt = $conn->prepare("SELECT DISTINCT m.id, m.nombre
                              FROM mascotas m
                              INNER JOIN atenciones a ON a.id_mascota = m.id
                              WHERE a.id_pro = ?");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $mascotas[] = $row;
    }
    $stmt->close();
}

// Servicios del profesional
$servicios = [];
if ($profesionalId) {
    $stmt = $conn->prepare("SELECT s.id, s.nombre
                              FROM servicios s
                              INNER JOIN especialidad esp ON esp.id = s.id_esp
                              INNER JOIN profesionales p on p.id_esp = esp.id
                              WHERE p.id = ?");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $servicios[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Atenciones</title>

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>

    <!-- jQuery y Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />

    <link href="../styles.css" rel="stylesheet" />
</head>
<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container">
        <h2 class="text-center mt-3">Calendario de Atenciones</h2>
        <div class="row">
            <!-- Calendario -->
            <div class="col-md-7 mb-4">
                <div id="calendario"></div>
            </div>
            <!-- Formulario -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Registrar nueva atención</h2>
                        <form action="../shared/alta-atencion.php" method="POST">
                            <div class="form-group">
                                <label for="fecha">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required />
                            </div>

                            <div class="form-group">
                                <label for="hora">Hora</label>
                                <input type="time" class="form-control" id="hora" name="hora" required />
                            </div>

                            <!-- Selección de Mascota -->
                            <div class="form-group">
                                <label for="mascota_id">Mascota</label>
                                <select class="form-control" id="mascota_id" name="mascota_id" required>
                                    <option value="">-- Seleccione una mascota --</option>
                                    <?php foreach ($mascotas as $m): ?>
                                        <option value="<?= htmlspecialchars($m['id']) ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Selección de Especialista -->
                            <div class="form-group">
                                <label>Especialista</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($nombreProfesional) ?>" disabled />
                                <input type="hidden" id="especialista_id" name="especialista_id" value="<?= htmlspecialchars($profesionalId) ?>" />
                            </div>

                            

                            <!-- Selección de Servicio -->
                            <div class="form-group">
                                <label for="servicio_id">Servicio</label>
                                <select class="form-control" id="servicio_id" name="servicio_id" required>
                                    <option value="">-- Seleccione un servicio --</option>
                                    <?php foreach ($servicios as $s): ?>
                                        <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Registrar Atención</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var calendarEl = document.getElementById("calendario");
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                events: "../shared/atenciones.php",
                eventClick: function (info) {
                    let confirmacion = confirm(`Ver detalles de atención para: "${info.event.title}"?`);
                    if (confirmacion) {
                        window.location.href = "editarAtencionProfesional.php?id=" + info.event.id;
                    }
                },
            });
            calendar.render();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
