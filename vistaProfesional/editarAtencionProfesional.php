<?php
session_start();

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}
$profesionalId = $_SESSION['usuario_id'];

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 2. Procesar Guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $detalle = $_POST['detalle'];

    // Actualizamos el detalle
    $sql = "UPDATE atenciones SET detalle = ? WHERE id = ? AND id_pro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $detalle, $id, $profesionalId);

    if ($stmt->execute()) {
        $stmt->close();
        // Redirigir con éxito
        header("Location: dashboardProfesional.php?success=Informe actualizado correctamente");
        exit();
    } else {
        echo "Error al guardar.";
    }
}

// 3. Obtener Datos
function get_param($key)
{
    if (isset($_GET[$key]))
        return $_GET[$key];
    elseif (isset($_POST[$key]))
        return $_POST[$key];
    return null;
}

$id = get_param('id');

if (!$id) {
    die("ID de atención no proporcionado.");
}

$sql = "SELECT a.*, m.nombre AS mascota, m.raza, s.nombre AS servicio
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        INNER JOIN servicios s ON a.id_serv = s.id
        WHERE a.id = ? AND a.id_pro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
$atencion = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$atencion) {
    die("Atención no encontrada o no tienes permisos para verla.");
}

$fecha = date('d/m/Y', strtotime($atencion['fecha']));
$hora = date('H:i', strtotime($atencion['fecha']));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolución Médica - San Antón</title>
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

        .info-card {
            border: none;
            background-color: #f8f9fa;
            border-left: 4px solid #00897b;
            border-radius: 5px;
        }

        .label-dato {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .valor-dato {
            font-size: 1.1rem;
            color: #212529;
            font-weight: 600;
        }

        .editor-area {
            border-color: #ced4da;
            border-radius: 8px;
            padding: 15px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .editor-area:focus {
            border-color: #00897b;
            box-shadow: 0 0 0 0.2rem rgba(0, 137, 123, 0.25);
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-4">
                <li class="breadcrumb-item"><a href="panel-profesional.php" class="text-teal">Panel</a></li>
                <li class="breadcrumb-item active" aria-current="page">Realizar Atención</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Datos del Turno</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-4">
                            <div class="label-dato">Paciente</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-paw text-teal fa-lg"></i>
                                </div>
                                <div>
                                    <div class="valor-dato"><?php echo htmlspecialchars($atencion['mascota']); ?></div>
                                    <small
                                        class="text-muted"><?php echo htmlspecialchars($atencion['raza'] ?? 'Raza no esp.'); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="label-dato">Servicio Solicitado</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-stethoscope text-teal fa-lg"></i>
                                </div>
                                <div class="valor-dato"><?php echo htmlspecialchars($atencion['servicio']); ?></div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="label-dato">Fecha y Hora</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-calendar-alt text-teal fa-lg"></i>
                                </div>
                                <div>
                                    <div class="valor-dato"><?php echo htmlspecialchars($fecha); ?></div>
                                    <span class="badge badge-pill badge-info"><?php echo htmlspecialchars($hora); ?>
                                        hs</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <form method="post" class="card shadow border-0 h-100">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($atencion['id']); ?>">

                    <div class="card-header bg-teal text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-file-medical-alt mr-2"></i> Informe Médico
                        </h5>
                        <span class="badge badge-light text-teal">N° <?php echo $atencion['id']; ?></span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <div class="form-group flex-grow-1">
                            <label class="font-weight-bold text-secondary mb-2">Evolución / Diagnóstico /
                                Tratamiento:</label>
                            <textarea name="detalle" class="form-control editor-area h-100"
                                placeholder="Escriba aquí los detalles de la consulta, diagnóstico y tratamiento indicado..."
                                rows="12" required><?php echo htmlspecialchars($atencion['detalle']); ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 pb-4 pt-0 text-right">
                        <a href="dashboardProfesional.php" class="btn btn-outline-secondary px-4 mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i> Guardar Informe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>