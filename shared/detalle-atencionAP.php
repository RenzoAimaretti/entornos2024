<?php
session_start();

// Validación de sesión
if (!isset($_SESSION['usuario_tipo']) || ($_SESSION['usuario_tipo'] !== 'admin' && $_SESSION['usuario_tipo'] !== 'especialista')) {
    header('Location: ../index.php');
    exit();
}

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validamos que exista el ID en la URL
if (!isset($_GET['id'])) {
    die("ID de atención no proporcionado.");
}

$idAtencion = intval($_GET['id']);

// Variable para verificar si es admin (usada para ocultar botones)
$esAdmin = ($_SESSION['usuario_tipo'] === 'admin');

$query = "SELECT a.id, 
                 m.nombre as nombreMascota,
                 m.id as idMascota,
                 m.raza,
                 s.nombre as nombreServicio,
                 a.fecha,
                 a.detalle,
                 u.nombre as nombrePro,
                 u.id as idPro
        FROM atenciones a
        INNER JOIN mascotas m on a.id_mascota = m.id
        INNER JOIN servicios s on a.id_serv = s.id
        INNER JOIN usuarios u on a.id_pro = u.id
        WHERE a.id = $idAtencion";

$result = $conn->query($query);
$atencion = $result->fetch_assoc();

if (!$atencion) {
    die("Atención no encontrada.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Médica #<?php echo $atencion['id']; ?> - San Antón</title>
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

        .ficha-medica {
            background-color: white;
            border-top: 5px solid #00897b;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.25rem;
        }

        .label-dato {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .valor-dato {
            font-size: 1.1rem;
            color: #343a40;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-4">
                <li class="breadcrumb-item"><a href="../vistaAdmin/gestionar-atenciones.php">Atenciones</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ficha #<?php echo $atencion['id']; ?></li>
            </ol>
        </nav>

        <div class="row">

            <div class="col-lg-8 mb-4">
                <div class="ficha-medica p-5">

                    <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                        <div>
                            <h2 class="mb-0 text-teal font-weight-bold">Reporte de Atención</h2>
                            <span class="badge badge-info px-3 py-2 mt-2" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($atencion['nombreServicio']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <h5 class="text-muted mb-0"><?php echo date('d/m/Y', strtotime($atencion['fecha'])); ?></h5>
                            <small class="text-muted"><?php echo date('H:i', strtotime($atencion['fecha'])); ?>
                                hs</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="label-dato">Paciente</div>
                            <div class="valor-dato font-weight-bold">
                                <?php echo htmlspecialchars($atencion['nombreMascota']) ?>
                                <small
                                    class="text-muted font-weight-normal">(<?php echo htmlspecialchars($atencion['raza'] ?? '') ?>)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="label-dato">Profesional a Cargo</div>
                            <div class="valor-dato">
                                Dr/a. <?php echo htmlspecialchars($atencion['nombrePro']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="label-dato">Observaciones / Diagnóstico</div>
                        <div class="p-3 bg-light rounded border" style="min-height: 150px;">
                            <?php echo nl2br(htmlspecialchars($atencion['detalle'])); ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white font-weight-bold text-secondary">
                        <i class="fas fa-cogs mr-2"></i> Acciones
                    </div>
                    <div class="card-body">

                        <?php if ($esAdmin): ?>
                            <button type="button" class="btn btn-warning btn-block font-weight-bold mb-3 shadow-sm"
                                data-toggle="modal" data-target="#editarAtencionModal">
                                <i class="fas fa-edit mr-2"></i> Editar Informe
                            </button>
                            <div class="border-top pt-3 mb-3"></div>
                        <?php endif; ?>

                        <a href="../shared/detalle-mascota.php?idMascota=<?php echo $atencion['idMascota']; ?>"
                            class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-paw mr-2"></i> Ver Historia Clínica
                        </a>

                        <a href="../vistaAdmin/detalle-especialista.php?id=<?php echo $atencion['idPro']; ?>"
                            class="btn btn-outline-secondary btn-block mb-2">
                            <i class="fas fa-user-md mr-2"></i> Perfil Profesional
                        </a>

                        <?php if ($esAdmin): ?>
                            <div class="border-top pt-3 mb-3"></div>

                            <?php if (strtotime($atencion['fecha']) < time()): ?>
                                <button class="btn btn-light btn-block text-muted" disabled>
                                    <i class="fas fa-lock mr-2"></i> Eliminar (Bloqueado)
                                </button>
                                <small class="text-center d-block text-muted mt-1">No se puede borrar historial pasado.</small>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-danger btn-block" data-toggle="modal"
                                    data-target="#modalEliminar">
                                    <i class="fas fa-trash-alt mr-2"></i> Cancelar Turno
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php if ($esAdmin): ?>
        <div class="modal fade" id="editarAtencionModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-teal text-white">
                        <h5 class="modal-title font-weight-bold">Editar Informe Médico</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form action="../shared/editar-atencion.php" method="POST">
                        <div class="modal-body p-4">
                            <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">

                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Fecha y Hora</label>
                                <input type="datetime-local" class="form-control" name="fecha"
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($atencion['fecha'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Detalles del Procedimiento /
                                    Observaciones</label>
                                <textarea class="form-control" name="detalle" rows="6"
                                    required><?php echo htmlspecialchars($atencion['detalle']); ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success font-weight-bold">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($esAdmin): ?>
        <div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold">Confirmar Eliminación</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body text-center p-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-4x mb-3"></i>
                        <h4>¿Estás seguro?</h4>
                        <p class="text-muted">Se eliminará permanentemente este registro de atención.</p>

                        <form action="../shared/eliminar-atencion.php" method="POST" class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">
                            <button type="button" class="btn btn-secondary px-4 mr-2" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold">Sí, Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>