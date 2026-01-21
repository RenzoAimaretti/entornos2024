<?php
session_start();

// 1. Validamos acceso
if (!isset($_SESSION['usuario_tipo']) || ($_SESSION['usuario_tipo'] !== 'admin' && $_SESSION['usuario_tipo'] !== 'especialista')) {
    die("Acceso denegado");
}

// 2. Capturamos el ID
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if ($id === 0) {
    die("ID de especialista no proporcionado.");
}

// 3. Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 4. Obtener datos del especialista
$query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
          FROM usuarios u 
          INNER JOIN profesionales p ON u.id = p.id
          INNER JOIN especialidad e on p.id_esp = e.id
          WHERE u.id = $id";
$resultEsp = $conn->query($query);

$nombre = "No encontrado";
$email = $telefono = $especialidad = "";

if ($resultEsp && $resultEsp->num_rows > 0) {
    $row = $resultEsp->fetch_assoc();
    $nombre = $row['nombre'];
    $email = $row['email'];
    $telefono = $row['telefono'];
    $especialidad = $row['especialidad'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Especialista - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white text-center">
                        <h2 class="mb-0">Detalles de <?php echo htmlspecialchars($nombre); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-sm-4"><strong>Email</strong>
                                <p><?php echo htmlspecialchars($email); ?></p>
                            </div>
                            <div class="col-sm-4"><strong>Teléfono</strong>
                                <p><?php echo htmlspecialchars($telefono); ?></p>
                            </div>
                            <div class="col-sm-4"><strong>Especialidad</strong>
                                <p><?php echo htmlspecialchars($especialidad); ?></p>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-warning" data-toggle="modal"
                                data-target="#editarModal">Editar Especialista</button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="border-bottom pb-2">Días de Atención</h4>
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hRes = $conn->query("SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id");
                                if ($hRes->num_rows > 0) {
                                    while ($hRow = $hRes->fetch_assoc()) {
                                        echo "<tr><td>{$hRow['diaSem']}</td><td>{$hRow['horaIni']}</td><td>{$hRow['horaFin']}</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center'>Sin horarios</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="border-bottom pb-2 text-primary">Próximas Atenciones</h4>
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Mascota</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $atNext = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                                                       FROM atenciones a INNER JOIN servicios s ON a.id_serv = s.id INNER JOIN mascotas m ON a.id_mascota = m.id
                                                       WHERE a.id_pro = $id AND a.fecha >= CURDATE() ORDER BY a.fecha ASC");
                                if ($atNext->num_rows > 0) {
                                    while ($r = $atNext->fetch_assoc()) {
                                        echo "<tr><td>" . date("d/m/Y H:i", strtotime($r['fecha'])) . "</td><td>{$r['serv']}</td><td>{$r['masc']}</td><td><a class='btn btn-info btn-sm' href='../shared/detalle-atencionAP.php?id={$r['id']}'>Ver</a></td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No hay turnos pendientes.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm mb-5">
                    <div class="card-body">
                        <h4 class="border-bottom pb-2 text-secondary">Historial de Atenciones (Pasadas)</h4>
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Mascota</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $atPast = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                                                       FROM atenciones a INNER JOIN servicios s ON a.id_serv = s.id INNER JOIN mascotas m ON a.id_mascota = m.id
                                                       WHERE a.id_pro = $id AND a.fecha < CURDATE() ORDER BY a.fecha DESC");
                                if ($atPast->num_rows > 0) {
                                    while ($r = $atPast->fetch_assoc()) {
                                        echo "<tr><td>" . date("d/m/Y H:i", strtotime($r['fecha'])) . "</td><td>{$r['serv']}</td><td>{$r['masc']}</td><td><a class='btn btn-info btn-sm' href='../shared/detalle-atencionAP.php?id={$r['id']}'>Ver</a></td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No hay historial previo.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="../shared/editar-especialista.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Especialista</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Teléfono</label><input type="text"
                                    class="form-control" name="telefono" value="<?php echo $telefono; ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Especialidad</label>
                                <select class="form-control" name="especialidad">
                                    <?php
                                    $espR = $conn->query("SELECT id, nombre FROM especialidad");
                                    while ($e = $espR->fetch_assoc()) {
                                        $sel = ($e['nombre'] === $especialidad) ? 'selected' : '';
                                        echo "<option value='{$e['id']}' $sel>{$e['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <h6>Días de atención</h6>
                        <div id="dias-container"></div>
                        <button type="button" class="btn btn-outline-info btn-sm mt-2" id="add-dia-btn">+ Agregar
                            día</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const diasSemana = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
        const container = document.getElementById('dias-container');

        // 1. Cargamos los horarios actuales desde la base de datos a un array de JS
        const horariosActuales = <?php
        $hResJS = $conn->query("SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id");
        $datos = [];
        while ($r = $hResJS->fetch_assoc()) {
            $datos[] = $r;
        }
        echo json_encode($datos);
        ?>;

        // Función para crear una fila de horario
        function agregarFilaHorario(dia = 'Lun', inicio = '08:00', fin = '12:00') {
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = 'form-row mb-2 align-items-center row-horario';
            div.innerHTML = `
                <div class="col-4">
                    <select name="dias[${index}][dia]" class="form-control form-control-sm">
                        ${diasSemana.map(d => `<option value="${d}" ${d === dia ? 'selected' : ''}>${d}</option>`).join('')}
                    </select>
                </div>
                <div class="col-3">
                    <input type="time" name="dias[${index}][horaInicio]" class="form-control form-control-sm" value="${inicio}">
                </div>
                <div class="col-3">
                    <input type="time" name="dias[${index}][horaFin]" class="form-control form-control-sm" value="${fin}">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">&times;</button>
                </div>
            `;
            container.appendChild(div);
        }

        // 2. Precargar filas al iniciar la página
        document.addEventListener('DOMContentLoaded', () => {
            if (horariosActuales.length > 0) {
                horariosActuales.forEach(h => {
                    // Quitamos los segundos (:00) si vienen de la base de datos para el input type="time"
                    agregarFilaHorario(h.diaSem, h.horaIni.substring(0, 5), h.horaFin.substring(0, 5));
                });
            }
        });

        // 3. Botón para agregar nuevos vacíos
        document.getElementById('add-dia-btn').addEventListener('click', () => {
            agregarFilaHorario();
        });
    </script>
    <?php $conn->close();
    require_once '../shared/footer.php'; ?>
</body>

</html>