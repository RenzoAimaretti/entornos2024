<?php
session_start();

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$profesionalId = $_SESSION['usuario_id'];

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 2. Consulta (DISTINCT para no repetir mascotas si las atendiste muchas veces)
$sql = "SELECT DISTINCT m.id, m.nombre, m.raza, m.fecha_nac
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        WHERE a.id_pro = ?
        ORDER BY m.nombre ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pacientes - San Antón</title>

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

        .page-item.active .page-link {
            background-color: #00897b;
            border-color: #00897b;
        }

        .avatar-paw {
            width: 40px;
            height: 40px;
            background-color: #e0f2f1;
            color: #00897b;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>
</head>

<body class="bg-light">

    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0"><i class="fas fa-paw text-teal mr-2"></i> Mis Pacientes</h2>
                <p class="text-muted">Listado de mascotas atendidas históricamente</p>
            </div>
            <a href="dashboardProfesional.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table id="tablaPacientes" class="table table-hover" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Raza</th>
                                    <th>Edad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    // Cálculo de edad mejorado
                                    $edadTexto = "Desconocida";
                                    $edadSort = 0; // Para ordenar correctamente en DataTables
                            
                                    if (!empty($row['fecha_nac'])) {
                                        $nac = new DateTime($row['fecha_nac']);
                                        $hoy = new DateTime();
                                        $diff = $hoy->diff($nac);

                                        if ($diff->y > 0) {
                                            $edadTexto = $diff->y . " años";
                                            $edadSort = $diff->y * 12; // Meses totales
                                        } elseif ($diff->m > 0) {
                                            $edadTexto = $diff->m . " meses";
                                            $edadSort = $diff->m;
                                        } else {
                                            $edadTexto = $diff->d . " días";
                                            $edadSort = 0;
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-paw mr-3">
                                                    <i class="fas fa-dog"></i>
                                                </div>
                                                <span
                                                    class="font-weight-bold text-dark"><?php echo htmlspecialchars($row['nombre']); ?></span>
                                            </div>
                                        </td>

                                        <td class="align-middle"><?php echo htmlspecialchars($row['raza']); ?></td>

                                        <td class="align-middle" data-order="<?php echo $edadSort; ?>">
                                            <span class="badge badge-light border text-muted">
                                                <?php echo $edadTexto; ?>
                                            </span>
                                        </td>

                                        <td class="text-center align-middle">
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo urlencode($row['id']); ?>"
                                                class="btn btn-sm btn-info rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-notes-medical mr-1"></i> Ver Ficha
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-dog fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aún no has atendido pacientes.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaPacientes').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                "pageLength": 10
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>