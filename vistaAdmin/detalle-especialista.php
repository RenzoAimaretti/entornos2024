<?php
session_start();

// 1. Validamos acceso
if (!isset($_SESSION['usuario_tipo']) || ($_SESSION['usuario_tipo'] !== 'admin' && $_SESSION['usuario_tipo'] !== 'especialista')) {
    header('Location: ../index.php');
    exit();
}

// 2. Capturamos el ID (por POST o GET, preferiblemente GET para enlaces directos)
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../styles.css" rel="stylesheet">
    <style>
        .bg-teal {
            background-color: #00897b;
            color: white;
        }

        .text-teal {
            color: #00897b;
        }

        .profile-header {
            background: linear-gradient(135deg, #00897b 0%, #4db6ac 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background-color: white;
            color: #00897b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-4">
                <li class="breadcrumb-item"><a href="../vistaAdmin/gestionar-especialistas.php">Especialistas</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($nombre); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="profile-header text-center">
                        <div class="avatar-circle">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h4 class="font-weight-bold"><?php echo htmlspecialchars($nombre); ?></h4>
                        <span class="badge badge-light px-3 py-2 mt-1" style="font-size: 0.9rem; color: #00897b;">
                            <?php echo htmlspecialchars($especialidad); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted text-uppercase font-weight-bold">Contacto</small>
                            <div class="d-flex align-items-center mt-2">
                                <i class="fas fa-envelope text-teal mr-3" style="width: 20px;"></i>
                                <span><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <i class="fas fa-phone text-teal mr-3" style="width: 20px;"></i>
                                <span><?php echo htmlspecialchars($telefono); ?></span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted text-uppercase font-weight-bold">Horarios de Atención</small>
                            <ul class="list-unstyled mt-2 small">
                                <?php
                                $hRes = $conn->query("SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id ORDER BY CASE diaSem WHEN 'Lun' THEN 1 WHEN 'Mar' THEN 2 WHEN 'Mie' THEN 3 WHEN 'Jue' THEN 4 WHEN 'Vie' THEN 5 WHEN 'Sab' THEN 6 ELSE 7 END");
                                if ($hRes->num_rows > 0) {
                                    while ($hRow = $hRes->fetch_assoc()) {
                                        echo "<li class='mb-1 d-flex justify-content-between'>
                                                <strong>{$hRow['diaSem']}</strong> 
                                                <span>{$hRow['horaIni']} - {$hRow['horaFin']}</span>
                                              </li>";
                                    }
                                } else {
                                    echo "<li class='text-muted font-italic'>Sin horarios configurados.</li>";
                                }
                                ?>
                            </ul>
                        </div>

                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <button type="button"
                                class="btn btn-outline-warning btn-block rounded-pill font-weight-bold mt-4"
                                data-toggle="modal" data-target="#editarModal">
                                <i class="fas fa-edit mr-2"></i> Editar Perfil
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="text-teal font-weight-bold"><i class="fas fa-calendar-check mr-2"></i> Próximos
                            Turnos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Paciente</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $atNext = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                                                            FROM atenciones a 
                                                            INNER JOIN servicios s ON a.id_serv = s.id 
                                                            INNER JOIN mascotas m ON a.id_mascota = m.id
                                                            WHERE a.id_pro = $id AND a.fecha >= CURDATE() ORDER BY a.fecha ASC LIMIT 5");
                                    if ($atNext && $atNext->num_rows > 0) {
                                        while ($r = $atNext->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . date("d/m/Y H:i", strtotime($r['fecha'])) . "</td>
                                                    <td>{$r['serv']}</td>
                                                    <td><strong>{$r['masc']}</strong></td>
                                                    <td><a class='btn btn-info btn-sm rounded-pill px-3' href='../shared/detalle-atencionAP.php?id={$r['id']}'>Ver</a></td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-3'>No hay turnos próximos.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="text-secondary font-weight-bold"><i class="fas fa-history mr-2"></i> Historial
                            Reciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Paciente</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $atPast = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                                                            FROM atenciones a 
                                                            INNER JOIN servicios s ON a.id_serv = s.id 
                                                            INNER JOIN mascotas m ON a.id_mascota = m.id
                                                            WHERE a.id_pro = $id AND a.fecha < CURDATE() ORDER BY a.fecha DESC LIMIT 5");
                                    if ($atPast && $atPast->num_rows > 0) {
                                        while ($r = $atPast->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . date("d/m/Y H:i", strtotime($r['fecha'])) . "</td>
                                                    <td>{$r['serv']}</td>
                                                    <td>{$r['masc']}</td>
                                                    <td><a class='btn btn-outline-secondary btn-sm rounded-pill px-3' href='../shared/detalle-atencionAP.php?id={$r['id']}'>Ver</a></td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-3'>Sin historial reciente.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
        <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <form action="../shared/editar-especialista.php" method="POST">
                        <div class="modal-header bg-teal text-white">
                            <h5 class="modal-title font-weight-bold">Editar Datos del Especialista</h5>
                            <button type="button" class="close text-white"
                                data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div class="form-row mb-3">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold text-muted small">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono"
                                        value="<?php echo htmlspecialchars($telefono); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold text-muted small">Especialidad</label>
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

                            <h6 class="text-teal font-weight-bold mb-3">Configuración de Horarios</h6>
                            <div id="dias-container"></div>

                            <button type="button" class="btn btn-outline-info btn-sm mt-2 rounded-pill font-weight-bold"
                                id="add-dia-btn">
                                <i class="fas fa-plus mr-1"></i> Agregar Jornada
                            </button>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success font-weight-bold px-4">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
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
                div.className = 'form-row mb-2 align-items-center row-horario bg-light p-2 rounded border';
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
                <div class="col-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm rounded-circle shadow-sm" onclick="this.closest('.row-horario').remove()">
                        <i class="fas fa-trash-alt"></i>
                    </button>
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
                } else {
                    // Si no hay horarios, mostramos mensaje o una fila vacía
                    container.innerHTML = '<div class="text-muted small text-center mb-3">No hay horarios definidos.</div>';
                }
            });

            // 3. Botón para agregar nuevos
            document.getElementById('add-dia-btn').addEventListener('click', () => {
                // Limpiar mensaje de "no hay horarios" si existe
                if (container.querySelector('.text-center')) {
                    container.innerHTML = '';
                }
                agregarFilaHorario();
            });
        </script>
    <?php endif; ?>

    <?php
    $conn->close();
    require_once '../shared/footer.php';
    ?>
</body>

</html>