<?php
session_start();

if (!isset($_SESSION['usuario_tipo'])) {
    header('Location: ../iniciar-sesion.php');
    exit();
}

if (!isset($_GET['idMascota'])) {
    die("ID de mascota no proporcionado.");
}

$idMascota = intval($_GET['idMascota']);

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 1. Obtener datos de la mascota
$query = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue, m.id_cliente
          FROM mascotas m
          WHERE m.id = $idMascota";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mascota_nombre = $row['mascota_nombre'];
    $raza = $row['raza'];
    $fecha_nac = $row['fecha_nac'];
    $fecha_mue = $row['fecha_mue'];
    $idPropietario = $row['id_cliente'];
} else {
    die("Mascota no encontrada.");
}

if ($_SESSION['usuario_tipo'] === 'cliente' && $_SESSION['usuario_id'] != $idPropietario) {
    die("Acceso denegado: esta mascota no le pertenece.");
}

$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de mascota - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if (isset($_GET['res']) && $_GET['res'] == 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ¡Mascota actualizada correctamente!
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0">Detalles de <?php echo htmlspecialchars($mascota_nombre); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3"><strong>Nombre</strong>
                                <p><?php echo htmlspecialchars($mascota_nombre); ?></p>
                            </div>
                            <div class="col-md-3"><strong>Raza</strong>
                                <p><?php echo htmlspecialchars($raza); ?></p>
                            </div>
                            <div class="col-md-3"><strong>Nacimiento</strong>
                                <p><?php echo $fecha_nac ? date('d/m/Y', strtotime($fecha_nac)) : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-3"><strong>Estado</strong>
                                <p><?php echo $fecha_mue ? "<span class='badge badge-danger'>Fallecido (" . date('d/m/Y', strtotime($fecha_mue)) . ")</span>" : "<span class='badge badge-success'>Vivo</span>"; ?>
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-warning" data-toggle="modal"
                                data-target="#editarModal">Editar Mascota</button>
                            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                <a href="../vistaAdmin/detalle-cliente.php?id=<?php echo $idPropietario; ?>"
                                    class="btn btn-secondary">Volver al Cliente</a>
                            <?php else: ?>
                                <a href="../vistaCliente/mis-mascotas.php" class="btn btn-secondary">Volver</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 border-primary">
                    <div class="card-body">
                        <h4 class="text-primary"><i class="fas fa-syringe"></i> Carnet de Vacunación</h4>
                        <table class="table table-hover mt-3">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Vacuna / Refuerzo</th>
                                    <th>Profesional</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qVac = "SELECT a.id, a.fecha, s.nombre as servicio, u.nombre as profesional
                                         FROM atenciones a
                                         INNER JOIN servicios s ON a.id_serv = s.id
                                         INNER JOIN usuarios u ON a.id_pro = u.id
                                         WHERE a.id_mascota = $idMascota AND s.nombre LIKE '%vacuna%' ORDER BY a.fecha DESC";
                                $resVac = $conn->query($qVac);
                                if ($resVac && $resVac->num_rows > 0) {
                                    while ($v = $resVac->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . date('d/m/Y', strtotime($v['fecha'])) . "</td>
                                                <td>{$v['servicio']}</td>
                                                <td>{$v['profesional']}</td>
                                                <td><a href='detalle-atencionAP.php?id={$v['id']}' class='btn btn-sm btn-outline-primary'>Ver</a></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No registra vacunas</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow mb-4 border-info">
                    <div class="card-body">
                        <h4 class="text-info"><i class="fas fa-cut"></i> Historial de Estética (Corte de pelo)</h4>
                        <table class="table table-hover mt-3">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Esteticista</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qEst = "SELECT a.id, a.fecha, s.nombre as servicio, u.nombre as profesional
                                         FROM atenciones a
                                         INNER JOIN servicios s ON a.id_serv = s.id
                                         INNER JOIN usuarios u ON a.id_pro = u.id
                                         WHERE a.id_mascota = $idMascota AND s.nombre LIKE '%corte de pelo%' ORDER BY a.fecha DESC";
                                $resEst = $conn->query($qEst);
                                if ($resEst && $resEst->num_rows > 0) {
                                    while ($e = $resEst->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . date('d/m/Y', strtotime($e['fecha'])) . "</td>
                                                <td>{$e['servicio']}</td>
                                                <td>{$e['profesional']}</td>
                                                <td><a href='detalle-atencionAP.php?id={$e['id']}' class='btn btn-sm btn-outline-info'>Ver</a></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted'>No registra servicios de estética</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h3><i class="fas fa-file-medical"></i> Historia Clínica (Consultas y Cirugías)</h3>
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Profesional</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // NOT LIKE excluye los servicios de las otras tablas
                                $queryResto = "SELECT a.id, a.fecha, s.nombre as servicio, u.nombre as profesional
                                               FROM atenciones a
                                               INNER JOIN servicios s ON a.id_serv = s.id
                                               INNER JOIN usuarios u ON a.id_pro = u.id
                                               WHERE a.id_mascota = $idMascota 
                                               AND s.nombre NOT LIKE '%vacuna%' 
                                               AND s.nombre NOT LIKE '%corte de pelo%'
                                               ORDER BY a.fecha DESC";

                                $resAt = $conn->query($queryResto);
                                if ($resAt && $resAt->num_rows > 0) {
                                    while ($at = $resAt->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . date('d/m/Y H:i', strtotime($at['fecha'])) . "</td>
                                                <td>{$at['servicio']}</td>
                                                <td>{$at['profesional']}</td>
                                                <td><a href='detalle-atencionAP.php?id={$at['id']}' class='btn btn-sm btn-info'>Ver</a></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No hay otras atenciones médicas registradas</td></tr>";
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="editar-mascota.php" method="POST" id="formEditarMascota">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Datos de Mascota</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idMascota" value="<?php echo $idMascota; ?>">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre"
                                value="<?php echo htmlspecialchars($mascota_nombre); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Raza</label>
                            <input type="text" class="form-control" name="raza"
                                value="<?php echo htmlspecialchars($raza); ?>">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nac" id="edit_fecha_nac"
                                value="<?php echo $fecha_nac; ?>" max="<?php echo $hoy; ?>" required>
                        </div>
                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <div class="form-group">
                                <label>Fecha de Muerte (Opcional)</label>
                                <input type="date" class="form-control" name="fecha_mue" id="edit_fecha_mue"
                                    value="<?php echo $fecha_mue; ?>" max="<?php echo $hoy; ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const inputNac = document.getElementById('edit_fecha_nac');
        const inputMue = document.getElementById('edit_fecha_mue');

        function actualizarMinMuerte() {
            if (inputNac.value && inputMue) {
                inputMue.min = inputNac.value;
            }
        }

        inputNac.addEventListener('change', actualizarMinMuerte);
        actualizarMinMuerte();

        document.getElementById('formEditarMascota').addEventListener('submit', function (e) {
            const nac = new Date(inputNac.value);
            const hoy = new Date();
            if (nac > hoy) {
                e.preventDefault();
                alert("La fecha de nacimiento no puede ser futura.");
                return;
            }
            if (inputMue && inputMue.value) {
                const mue = new Date(inputMue.value);
                if (mue < nac) {
                    e.preventDefault();
                    alert("La fecha de muerte no puede ser anterior al nacimiento.");
                }
            }
        });
    </script>
    <?php $conn->close(); ?>
</body>

</html>