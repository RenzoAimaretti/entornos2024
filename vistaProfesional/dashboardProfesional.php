<?php
session_start();

// 1. Verificación de Seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// 2. Configuración
date_default_timezone_set('America/Argentina/Buenos_Aires');
$nombre = $_SESSION['usuario_nombre'];
$profesional_id = $_SESSION['usuario_id'];

// 3. Conexión BD
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 4. Lógica POST (Acciones)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'finalizar') {
            $id_hosp = $_POST['id_hosp'];
            $fecha_egreso = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE hospitalizaciones SET estado = 'Finalizada', fecha_egreso_real = ? WHERE id = ?");
            $stmt->bind_param("si", $fecha_egreso, $id_hosp);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=Hospitalización finalizada correctamente");
            } else {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=Error al finalizar");
            }
            exit();
        } elseif ($_POST['accion'] === 'crear') {
            $id_mascota = $_POST['id_mascota'];
            $motivo = $_POST['motivo'];
            $fecha_prevista = $_POST['fecha_egreso_prevista'];
            $fecha_ingreso = date('Y-m-d H:i:s');

            if (strtotime($fecha_prevista) <= strtotime($fecha_ingreso)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=La fecha de egreso debe ser futura");
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO hospitalizaciones (id_mascota, id_pro_deriva, fecha_ingreso, fecha_egreso_prevista, motivo, estado) VALUES (?, ?, ?, ?, ?, 'Activa')");
            $stmt->bind_param("iisss", $id_mascota, $profesional_id, $fecha_ingreso, $fecha_prevista, $motivo);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=Paciente ingresado a internación");
            } else {
                header("Location: " . $_SERVER['PHP_SELF'] . "?error=Error al registrar ingreso");
            }
            $stmt->close();
            exit();
        }
    }
}

// 5. Consultas de Datos
$hoy = date('Y-m-d');

$turnos_hoy = $conn->query("
    SELECT a.id, DATE_FORMAT(a.fecha, '%H:%i') AS hora, m.nombre AS nombre_mascota, s.nombre AS nombre_servicio, a.detalle
    FROM atenciones a
    INNER JOIN mascotas m ON a.id_mascota = m.id
    INNER JOIN servicios s ON a.id_serv = s.id
    WHERE a.id_pro = $profesional_id AND DATE(a.fecha) = '$hoy'
    ORDER BY a.fecha ASC
")->fetch_all(MYSQLI_ASSOC);

$hosp_activas = $conn->query("
    SELECT h.id, m.nombre AS nombre_mascota, h.fecha_ingreso, h.fecha_egreso_prevista, h.motivo 
    FROM hospitalizaciones h 
    INNER JOIN mascotas m ON h.id_mascota = m.id 
    WHERE h.estado = 'Activa' 
    ORDER BY h.fecha_ingreso ASC
")->fetch_all(MYSQLI_ASSOC);

$mascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Profesional - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
    <style>
        .bg-teal {
            background-color: #00897b;
            color: white;
        }

        .text-teal {
            color: #00897b;
        }

        .card-dashboard {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .card-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.5rem;
        }

        /* Estilos de paginación similares al historial */
        .page-item.active .page-link {
            background-color: #00897b;
            border-color: #00897b;
        }

        table.dataTable thead th {
            position: relative;
            padding-right: 30px !important;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0">Panel de Control</h2>
                <p class="text-muted">Hola, Dr/a. <?php echo htmlspecialchars($nombre); ?></p>
            </div>
            <div class="text-right">
                <span class="badge badge-pill badge-light border p-2"><?php echo date('d/m/Y'); ?></span>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card card-dashboard h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold text-teal mb-0"><i class="fas fa-calendar-day mr-2"></i> Agenda de
                            Hoy</h5>
                        <a href="../vistaAdmin/gestionar-atenciones.php"
                            class="btn btn-sm btn-outline-secondary rounded-pill">Ver Todo</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($turnos_hoy) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Hora</th>
                                            <th>Paciente</th>
                                            <th>Servicio</th>
                                            <th class="text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($turnos_hoy as $turno): ?>
                                            <tr class="<?php echo !empty($turno['detalle']) ? 'table-success' : ''; ?>">
                                                <td class="align-middle font-weight-bold">
                                                    <?php echo htmlspecialchars($turno['hora']); ?>
                                                </td>
                                                <td class="align-middle">
                                                    <?php echo htmlspecialchars($turno['nombre_mascota']); ?>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="badge <?php echo !empty($turno['detalle']) ? 'badge-light text-success' : 'badge-info'; ?> px-2 py-1">
                                                        <?php echo htmlspecialchars($turno['nombre_servicio']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="editarAtencionProfesional.php?id=<?php echo $turno['id']; ?>"
                                                        class="btn btn-teal btn-sm rounded-pill shadow-sm"
                                                        style="background-color: #00897b; color: white;">
                                                        <i class="fas fa-notes-medical mr-1"></i>
                                                        <?php echo !empty($turno['detalle']) ? 'Ver' : 'Atender'; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted"><i
                                    class="fas fa-calendar-check fa-3x mb-3 text-black-50"></i>
                                <p>No hay turnos.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-dashboard bg-white mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-light text-primary mr-3"><i class="fas fa-history"></i></div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Historial Clínico</h6><a
                                href="atencionesPreviasProfesional.php" class="stretched-link text-muted small">Ver
                                pasadas</a>
                        </div>
                    </div>
                </div>
                <div class="card card-dashboard bg-white mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-light text-warning mr-3"><i class="fas fa-paw"></i></div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Mis Pacientes</h6><a
                                href="pacientesMascotasProfesional.php" class="stretched-link text-muted small">Ver
                                todos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <div class="card card-dashboard border-top-danger" style="border-top: 4px solid #dc3545;">
                    <div
                        class="card-header bg-white pt-4 pb-0 d-flex justify-content-between align-items-center border-bottom-0">
                        <h5 class="font-weight-bold text-danger mb-0"><i class="fas fa-procedures mr-2"></i>
                            Hospitalizaciones Activas</h5>
                        <button class="btn btn-danger btn-sm shadow-sm font-weight-bold rounded-pill"
                            data-toggle="modal" data-target="#modalNuevaHosp">
                            <i class="fas fa-plus mr-1"></i> Nueva Internación
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (count($hosp_activas) > 0): ?>
                            <div class="table-responsive">
                                <table id="tablaHosp" class="table table-hover" style="width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Egreso Estimado</th>
                                            <th>Motivo</th>
                                            <th class="text-right">Gestión</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hosp_activas as $hosp): ?>
                                            <tr>
                                                <td class="align-middle font-weight-bold">
                                                    <?php echo htmlspecialchars($hosp['nombre_mascota']); ?>
                                                </td>
                                                <td class="align-middle"
                                                    data-order="<?php echo strtotime($hosp['fecha_ingreso']); ?>">
                                                    <?php echo date('d/m/Y H:i', strtotime($hosp['fecha_ingreso'])); ?> hs
                                                </td>
                                                <td class="align-middle text-danger font-weight-bold"
                                                    data-order="<?php echo strtotime($hosp['fecha_egreso_prevista']); ?>">
                                                    <?php echo !empty($hosp['fecha_egreso_prevista']) ? date('d/m/Y H:i', strtotime($hosp['fecha_egreso_prevista'])) : 'Indefinido'; ?>
                                                    hs
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    <?php echo htmlspecialchars($hosp['motivo']); ?>
                                                </td>
                                                <td class="text-right align-middle">
                                                    <button type="button"
                                                        class="btn btn-outline-success btn-sm rounded-pill px-3 btn-dar-alta"
                                                        data-id="<?php echo $hosp['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($hosp['nombre_mascota']); ?>"
                                                        data-toggle="modal" data-target="#modalConfirmarAlta">
                                                        <i class="fas fa-check mr-1"></i> Dar Alta
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center border mt-2"><small class="text-muted">No hay
                                    pacientes internados.</small></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarAlta" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">Confirmar Alta Médica</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <p class="mb-1">¿Estás seguro de que deseas dar el alta a:</p>
                    <h5 id="altaNombrePaciente" class="font-weight-bold text-dark"></h5>
                    <p class="text-muted small">Esta acción registrará la fecha de egreso real como el momento actual.
                    </p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <form method="POST">
                        <input type="hidden" name="id_hosp" id="altaIdHosp">
                        <input type="hidden" name="accion" value="finalizar">
                        <button type="button" class="btn btn-secondary px-4 mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold">Confirmar Alta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevaHosp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST" id="formNuevaHosp">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-hospital-symbol mr-2"></i> Ingreso a
                            Internación</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="accion" value="crear">
                        <div class="form-group">
                            <label class="font-weight-bold small text-muted">Paciente</label>
                            <select name="id_mascota" class="form-control" required>
                                <option value="">Seleccione mascota...</option>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-muted">Egreso Estimado</label>
                            <input type="datetime-local" id="fecha_egreso_prevista" name="fecha_egreso_prevista"
                                class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-muted">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 font-weight-bold">Confirmar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicializar DataTables para Hospitalizaciones
            $('#tablaHosp').DataTable({
                "pageLength": 5,
                "autoWidth": true,
                "order": [[1, "asc"]], // Ordenar por fecha de ingreso
                "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "Todos"]],
                "columnDefs": [
                    { "orderable": false, "targets": [3, 4] }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });

            // Lógica para el modal de Alta
            $('.btn-dar-alta').on('click', function () {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                $('#altaIdHosp').val(id);
                $('#altaNombrePaciente').text(nombre);
            });

            // Fecha mínima
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            $('#fecha_egreso_prevista').attr('min', now.toISOString().slice(0, 16));
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>