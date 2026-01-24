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
$query = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue, m.id_cliente, m.foto
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
    $fotoMascota = $row['foto'];
} else {
    die("Mascota no encontrada.");
}

if ($_SESSION['usuario_tipo'] === 'cliente' && $_SESSION['usuario_id'] != $idPropietario) {
    die("Acceso denegado: esta mascota no le pertenece.");
}

$hoy = date('Y-m-d');
$esProfesional = ($_SESSION['usuario_tipo'] === 'admin' || $_SESSION['usuario_tipo'] === 'especialista');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de <?php echo htmlspecialchars($mascota_nombre); ?> - San Antón</title>
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

        /* Imagen de perfil grande */
        .profile-img-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            margin: 0 auto;
            background-color: #e0f2f1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header {
            background: linear-gradient(135deg, #00897b 0%, #004d40 100%);
            padding-top: 30px;
            padding-bottom: 30px;
            border-radius: 10px 10px 0 0;
            color: white;
            position: relative;
        }

        /* Tabs personalizados */
        .nav-tabs .nav-link {
            color: #555;
            font-weight: bold;
            border: none;
            border-bottom: 3px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: #00897b;
            border-bottom: 3px solid #00897b;
            background-color: transparent;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #00897b;
        }

        /* Botón desactivado visualmente */
        .btn-disabled-look {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
            /* Evita clics */
            background-color: #e9ecef;
            border-color: #e9ecef;
            color: #6c757d;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <?php if (isset($_GET['res']) && $_GET['res'] == 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> Datos actualizados correctamente.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg border-0 mb-4">

            <div class="profile-header text-center">
                <div class="profile-img-container mb-3">
                    <?php if (!empty($fotoMascota)): ?>
                        <img src="<?php echo htmlspecialchars($fotoMascota); ?>"
                            alt="<?php echo htmlspecialchars($mascota_nombre); ?>">
                    <?php else: ?>
                        <i class="fas fa-paw fa-4x text-teal" style="opacity: 0.5;"></i>
                    <?php endif; ?>
                </div>
                <h2 class="font-weight-bold"><?php echo htmlspecialchars($mascota_nombre); ?></h2>
                <p class="mb-0 text-white-50"><?php echo htmlspecialchars($raza); ?></p>
            </div>

            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Fecha de Nacimiento</small>
                        <h5 class="mt-1 text-dark">
                            <?php echo $fecha_nac ? date('d/m/Y', strtotime($fecha_nac)) : 'N/A'; ?>
                        </h5>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Edad Aproximada</small>
                        <h5 class="mt-1 text-dark">
                            <?php
                            if ($fecha_nac) {
                                $nac = new DateTime($fecha_nac);
                                $ahora = new DateTime($fecha_mue ? $fecha_mue : 'now');
                                $diff = $nac->diff($ahora);
                                echo $diff->y . " años";
                            } else {
                                echo "-";
                            }
                            ?>
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase font-weight-bold">Estado</small>
                        <h5 class="mt-1">
                            <?php echo $fecha_mue ? "<span class='badge badge-danger px-3'>Fallecido</span>" : "<span class='badge badge-success px-3'>Activo</span>"; ?>
                        </h5>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-warning rounded-pill px-4 mr-2 font-weight-bold shadow-sm"
                        data-toggle="modal" data-target="#editarModal">
                        <i class="fas fa-edit mr-2"></i> Editar Datos
                    </button>

                    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                        <a href="../vistaAdmin/detalle-cliente.php?id=<?php echo $idPropietario; ?>"
                            class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-user mr-2"></i> Ver Dueño
                        </a>
                    <?php else: ?>
                        <a href="../vistaCliente/mis-mascotas.php" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <ul class="nav nav-tabs card-header-tabs" id="mascotaTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="historia-tab" data-toggle="tab" href="#historia" role="tab"
                            aria-controls="historia" aria-selected="true">
                            <i class="fas fa-file-medical-alt mr-2"></i> Historia Clínica
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="vacunas-tab" data-toggle="tab" href="#vacunas" role="tab"
                            aria-controls="vacunas" aria-selected="false">
                            <i class="fas fa-syringe mr-2"></i> Vacunas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="estetica-tab" data-toggle="tab" href="#estetica" role="tab"
                            aria-controls="estetica" aria-selected="false">
                            <i class="fas fa-cut mr-2"></i> Estética
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="internacion-tab" data-toggle="tab" href="#internacion" role="tab"
                            aria-controls="internacion" aria-selected="false">
                            <i class="fas fa-procedures mr-2"></i> Hospitalizaciones
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="mascotaTabContent">

                    <div class="tab-pane fade show active" id="historia" role="tabpanel">
                        <h5 class="text-teal mb-3">Consultas y Cirugías Recientes</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Profesional</th>
                                        <th class="text-center">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
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
                                            // Lógica del botón: Si es profesional link real, sino deshabilitado
                                            $btnClass = $esProfesional ? "btn btn-sm btn-info rounded-pill px-3" : "btn btn-sm btn-disabled-look rounded-pill px-3";
                                            $btnHref = $esProfesional ? "detalle-atencionAP.php?id={$at['id']}" : "#";
                                            $btnIcon = $esProfesional ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-lock"></i>';

                                            echo "<tr>
                                                    <td>" . date('d/m/Y', strtotime($at['fecha'])) . "</td>
                                                    <td>{$at['servicio']}</td>
                                                    <td>{$at['profesional']}</td>
                                                    <td class='text-center'>
                                                        <a href='$btnHref' class='$btnClass'>$btnIcon</a>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No hay consultas registradas</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="vacunas" role="tabpanel">
                        <h5 class="text-primary mb-3">Carnet de Vacunación</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light text-primary">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Vacuna</th>
                                        <th>Profesional</th>
                                        <th class="text-center">Detalle</th>
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
                                            $btnClass = $esProfesional ? "btn btn-sm btn-outline-primary rounded-pill px-3" : "btn btn-sm btn-disabled-look rounded-pill px-3";
                                            $btnHref = $esProfesional ? "detalle-atencionAP.php?id={$v['id']}" : "#";
                                            $btnIcon = $esProfesional ? 'Ver' : '<i class="fas fa-lock"></i>';

                                            echo "<tr>
                                                    <td>" . date('d/m/Y', strtotime($v['fecha'])) . "</td>
                                                    <td>{$v['servicio']}</td>
                                                    <td>{$v['profesional']}</td>
                                                    <td class='text-center'><a href='$btnHref' class='$btnClass'>$btnIcon</a></td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No registra vacunas</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="estetica" role="tabpanel">
                        <h5 class="text-info mb-3">Historial de Peluquería</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light text-info">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Esteticista</th>
                                        <th class="text-center">Detalle</th>
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
                                            $btnClass = $esProfesional ? "btn btn-sm btn-outline-info rounded-pill px-3" : "btn btn-sm btn-disabled-look rounded-pill px-3";
                                            $btnHref = $esProfesional ? "detalle-atencionAP.php?id={$e['id']}" : "#";
                                            $btnIcon = $esProfesional ? 'Ver' : '<i class="fas fa-lock"></i>';

                                            echo "<tr>
                                                    <td>" . date('d/m/Y', strtotime($e['fecha'])) . "</td>
                                                    <td>{$e['servicio']}</td>
                                                    <td>{$e['profesional']}</td>
                                                    <td class='text-center'><a href='$btnHref' class='$btnClass'>$btnIcon</a></td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No registra servicios de estética</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="internacion" role="tabpanel">
                        <h5 class="text-danger mb-3">Historial de Hospitalizaciones</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light text-danger">
                                    <tr>
                                        <th>Ingreso</th>
                                        <th>Egreso</th>
                                        <th>Derivado por</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $qHosp = "SELECT h.*, u.nombre as profesional 
                                              FROM hospitalizaciones h
                                              INNER JOIN usuarios u ON h.id_pro_deriva = u.id
                                              WHERE h.id_mascota = $idMascota ORDER BY h.fecha_ingreso DESC";
                                    $resH = $conn->query($qHosp);
                                    if ($resH && $resH->num_rows > 0) {
                                        while ($h = $resH->fetch_assoc()) {
                                            $egreso = ($h['estado'] == 'Activa') ? '<span class="badge badge-warning">En curso</span>' : date('d/m/Y', strtotime($h['fecha_egreso_real']));
                                            echo "<tr>
                                                    <td>" . date('d/m/Y', strtotime($h['fecha_ingreso'])) . "</td>
                                                    <td>$egreso</td>
                                                    <td>{$h['profesional']}</td>
                                                    <td>" . htmlspecialchars($h['motivo']) . "</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No registra internaciones</td></tr>";
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

    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title font-weight-bold">Editar Mascota</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="editar-mascota.php" method="POST" id="formEditarMascota">
                    <div class="modal-body p-4">
                        <input type="hidden" name="idMascota" value="<?php echo $idMascota; ?>">

                        <div class="form-group">
                            <label class="font-weight-bold text-muted small">Nombre</label>
                            <input type="text" class="form-control bg-light" name="nombre"
                                value="<?php echo htmlspecialchars($mascota_nombre); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-muted small">Raza / Especie</label>
                            <input type="text" class="form-control bg-light" name="raza"
                                value="<?php echo htmlspecialchars($raza); ?>">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-muted small">Fecha de Nacimiento</label>
                            <input type="date" class="form-control bg-light" name="fecha_nac" id="edit_fecha_nac"
                                value="<?php echo $fecha_nac; ?>" max="<?php echo $hoy; ?>" required>
                        </div>

                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <div class="form-group">
                                <label class="font-weight-bold text-danger small">Fecha de Fallecimiento (Solo
                                    Admin)</label>
                                <input type="date" class="form-control border-danger" name="fecha_mue" id="edit_fecha_mue"
                                    value="<?php echo $fecha_mue; ?>" max="<?php echo $hoy; ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success font-weight-bold">Guardar Cambios</button>
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

        if (inputNac) inputNac.addEventListener('change', actualizarMinMuerte);
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