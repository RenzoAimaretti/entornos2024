<?php

session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}
$nombre = $_SESSION['usuario_nombre'];
$profesional_id = $_SESSION['usuario_id']; 
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
date_default_timezone_set('America/Argentina/Buenos_Aires');
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {

    die("Error de conexión: " . $conn->connect_error);

}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['accion'])) {

        if ($_POST['accion'] === 'finalizar') {

            $id_hosp = $_POST['id_hosp'];

            $fecha_egreso = date('Y-m-d H:i:s');

            $stmt = $conn->prepare("UPDATE hospitalizaciones SET estado = 'Finalizada', fecha_egreso_real = ? WHERE id = ?");

            $stmt->bind_param("si", $fecha_egreso, $id_hosp);

            $stmt->execute();

        } elseif ($_POST['accion'] === 'crear') {

            $id_mascota = $_POST['id_mascota'];

            $motivo = $_POST['motivo'];

            $fecha_prevista = $_POST['fecha_egreso_prevista']; 

            $fecha_ingreso = date('Y-m-d H:i:s'); 
            error_log("Fecha de ingreso generada: " . $fecha_ingreso);
            if (strtotime($fecha_prevista) <= strtotime($fecha_ingreso)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=Fecha de egreso no válida");
                exit();
            }
            $stmt = $conn->prepare("INSERT INTO hospitalizaciones (id_mascota, id_pro_deriva, fecha_ingreso, fecha_egreso_prevista, motivo, estado) VALUES (?, ?, ?, ?, ?, 'Activa')");
            $stmt->bind_param("iisss", $id_mascota, $profesional_id, $fecha_ingreso, $fecha_prevista, $motivo);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=Hospitalización creada con éxito");
            } else {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=Error al crear la hospitalización");
            }
            $stmt->close();
            exit();
        }
    }
}
$hoy = date('Y-m-d');
$turnos_hoy = $conn->query("
    SELECT 
        a.id, 
        DATE_FORMAT(a.fecha, '%H:%i') AS hora, 
        m.nombre AS nombre_mascota, 
        s.nombre AS nombre_servicio,
        a.detalle
    FROM atenciones a
    INNER JOIN mascotas m ON a.id_mascota = m.id
    INNER JOIN servicios s ON a.id_serv = s.id
    WHERE a.id_pro = $profesional_id 
      AND DATE(a.fecha) = '$hoy'
    ORDER BY a.fecha ASC
")->fetch_all(MYSQLI_ASSOC);
$hosp_activas = $conn->query("SELECT h.id, m.nombre AS nombre_mascota, h.fecha_ingreso, h.fecha_egreso_prevista, h.motivo FROM hospitalizaciones h INNER JOIN mascotas m ON h.id_mascota = m.id WHERE h.estado = 'Activa' ORDER BY h.fecha_ingreso ASC")->fetch_all(MYSQLI_ASSOC);
$mascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Profesional</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body>
<?php require_once '../shared/navbar.php'; ?>
<div class="container my-4">
    <h2 class="text-center mb-4">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h2>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-success text-white">Turnos de Hoy (<?php echo date('d/m/Y'); ?>)</div>
                <div class="card-body">
                    <?php if (count($turnos_hoy) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Mascota</th>
                                        <th>Servicio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($turnos_hoy as $turno): ?>
                                        <tr class="<?php echo !empty($turno['detalle']) ? 'table-success' : ''; ?>">
                                            <td><?php echo htmlspecialchars($turno['hora']); ?></td>
                                            <td><?php echo htmlspecialchars($turno['nombre_mascota']); ?></td>
                                            <td><?php echo htmlspecialchars($turno['nombre_servicio']); ?></td>
                                            <td>
                                                <a href="editarAtencionProfesional.php?id=<?php echo $turno['id']; ?>" class="btn btn-primary btn-sm">
                                                    Ingresar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No tienes turnos programados para hoy.</p>
                    <?php endif; ?>
                    <a href="../vistaAdmin/gestionar-atenciones.php" class="btn btn-primary btn-block mt-3">Ver todos</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-header bg-info text-white">Historial</div><div class="card-body"><a href="atencionesPreviasProfesional.php" class="btn btn-info btn-block">Ver historial</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-header bg-warning text-white">Pacientes</div><div class="card-body"><a href="pacientesMascotasProfesional.php" class="btn btn-warning btn-block">Ver pacientes</a></div></div></div>

    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestión de Hospitalizaciones</h5>
                    <button class="btn btn-light btn-sm font-weight-bold" data-toggle="modal" data-target="#modalNuevaHosp"> + NUEVO INGRESO</button>
                </div>
                <div class="card-body">
                    <?php if (count($hosp_activas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mascota</th>
                                        <th>Ingreso</th>
                                        <th>Egreso Previsto</th>
                                        <th>Motivo</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hosp_activas as $hosp): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($hosp['nombre_mascota']); ?></strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($hosp['fecha_ingreso'])); ?></td>
                                            <td class="text-primary font-weight-bold">
                                                <?php echo !empty($hosp['fecha_egreso_prevista']) ? date('d/m/Y H:i', strtotime($hosp['fecha_egreso_prevista'])) : 'No definida'; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($hosp['motivo']); ?></td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="id_hosp" value="<?php echo $hosp['id']; ?>">
                                                    <input type="hidden" name="accion" value="finalizar">
                                                    <button type="submit" class="btn btn-success btn-sm">Dar de Alta</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay hospitalizaciones activas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalNuevaHosp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="formNuevaHosp">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Iniciar Hospitalización</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="form-group">
                        <label>Mascota</label>
                        <select name="id_mascota" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($mascotas as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Egreso Prevista</label>
                        <input type="datetime-local" id="fecha_egreso_prevista" name="fecha_egreso_prevista" class="form-control" required>
                        <small class="text-muted">Debe ser una fecha futura.</small>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {

    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDate = now.toISOString().slice(0, 16);
    $('#fecha_egreso_prevista').attr('min', minDate);

    $('#formNuevaHosp').on('submit', function(e) {
        const fechaSeleccionada = new Date($('#fecha_egreso_prevista').val());
        const fechaActual = new Date();
        if (fechaSeleccionada <= fechaActual) {
            alert('La fecha de egreso prevista debe ser posterior a la hora actual.');
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>