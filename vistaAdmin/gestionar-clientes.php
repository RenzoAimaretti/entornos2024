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

// Consulta optimizada para traer solo clientes
$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          ORDER BY u.nombre ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
    <style>
        .bg-teal { background-color: #00897b; color: white; }
        .text-teal { color: #00897b; }
        .table-hover tbody tr:hover { background-color: #f1f8e9; transition: background-color 0.2s ease-in-out; }
        .btn-circle-action { width: 35px; height: 35px; padding: 6px 0px; border-radius: 50%; text-align: center; font-size: 12px; line-height: 1.42857; }

        /* --- ESTILO DE FLECHAS ORIGINALES CON DISTANCIA NORMAL --- */
        
        /* Restauramos el espaciado normal de la cabecera */
        table.dataTable thead th {
            padding-right: 30px !important;
            position: relative;
            white-space: nowrap;
        }

        /* Posicionamiento de las flechas originales (antes estaban en 5px, ahora vuelven a 10px) */
        table.dataTable thead .sorting:before, 
        table.dataTable thead .sorting_asc:before, 
        table.dataTable thead .sorting_desc:before,
        table.dataTable thead .sorting:after, 
        table.dataTable thead .sorting_asc:after, 
        table.dataTable thead .sorting_desc:after {
            right: 10px !important; 
        }

        /* Ocultar flechas en la columna de Acciones (índice 3) */
        table.dataTable thead .sorting_disabled:before, 
        table.dataTable thead .sorting_disabled:after {
            display: none !important;
        }

        .dataTables_wrapper .dataTables_filter { margin-bottom: 20px; }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0">Gestión de Clientes</h2>
                <p class="text-muted small mb-0">Administración de usuarios y mascotas asociadas</p>
            </div>
            
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaClientes">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Ubicación</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3 text-secondary font-weight-bold"
                                                    style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($row['nombre'], 0, 1)); ?>
                                                </div>
                                                <h6 class="mb-0 font-weight-bold text-dark">
                                                    <?php echo htmlspecialchars($row['nombre']); ?>
                                                </h6>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="small">
                                                <div class="mb-1"><i class="fas fa-envelope text-muted mr-2"></i>
                                                    <?php echo htmlspecialchars($row['email']); ?></div>
                                                <div><i class="fas fa-phone text-muted mr-2"></i>
                                                    <?php echo htmlspecialchars($row['telefono'] ?? 'N/A'); ?></div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-muted small">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            <?php echo htmlspecialchars($row['direccion'] ?? 'Sin dirección'); ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="detalle-cliente.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-outline-info btn-circle-action shadow-sm"
                                                title="Ver Perfil y Mascotas">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
            $('#tablaClientes').DataTable({
                "pageLength": 10,
                "autoWidth": true, // Restauramos el cálculo automático
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2] },
                    { "orderable": false, "targets": 3 }
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
<?php $conn->close(); ?>