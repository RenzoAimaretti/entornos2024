<?php
session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
          FROM usuarios u 
          INNER JOIN profesionales p ON u.id = p.id
          INNER JOIN especialidad e on p.id_esp = e.id
          ORDER BY u.nombre ASC"; // Orden alfabético

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Especialistas - Veterinaria San Antón</title>
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

        .table-hover tbody tr:hover {
            background-color: #f1f8e9;
        }

        .btn-circle {
            border-radius: 50px;
            padding-left: 20px;
            padding-right: 20px;
        }

        /* Ajuste de flechas de ordenamiento pegadas al texto */
        table.dataTable thead th {
            position: relative;
            padding-right: 25px !important;
            white-space: nowrap;
        }

        table.dataTable thead .sorting:before, 
        table.dataTable thead .sorting_asc:before, 
        table.dataTable thead .sorting_desc:before,
        table.dataTable thead .sorting:after, 
        table.dataTable thead .sorting_asc:after, 
        table.dataTable thead .sorting_desc:after {
            right: 5px !important; 
        }

        table.dataTable thead .sorting_disabled:before, 
        table.dataTable thead .sorting_disabled:after {
            display: none !important;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0">Gestión de Especialistas</h2>
                <p class="text-muted small mb-0">Administración del equipo médico</p>
            </div>
            <a href="alta-especialista.php" class="btn btn-success shadow-sm btn-circle font-weight-bold">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Especialista
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaEspecialistas">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 font-weight-bold">Profesional</th>
                                <th class="border-0 font-weight-bold">Especialidad</th>
                                <th class="border-0 font-weight-bold">Contacto</th>
                                <th class="border-0 font-weight-bold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center mr-3"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold text-dark">
                                                        <?php echo htmlspecialchars($row['nombre']); ?>
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($row['especialidad']); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="small">
                                                <i class="fas fa-envelope mr-1 text-muted"></i>
                                                <?php echo htmlspecialchars($row['email']); ?><br>
                                                <i class="fas fa-phone mr-1 text-muted"></i>
                                                <?php echo htmlspecialchars($row['telefono']); ?>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <form action="detalle-especialista.php" method="post" style="display:inline;">
                                                <input type="hidden" name="id"
                                                    value="<?php echo htmlspecialchars($row['id']); ?>">
                                                <button type="submit"
                                                    class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm"
                                                    title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaEspecialistas').DataTable({
                "pageLength": 10,
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2] },
                    { "orderable": false, "targets": 3 } // Deshabilitar orden en 'Acciones'
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>
<?php
$conn->close();
?>