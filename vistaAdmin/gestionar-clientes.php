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
            transition: background-color 0.2s ease-in-out;
        }

        .search-icon {
            color: #ccc;
        }

        .btn-circle-action {
            width: 35px;
            height: 35px;
            padding: 6px 0px;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            line-height: 1.42857;
        }
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
            <a href="../registrarse.php" class="btn btn-outline-success rounded-pill font-weight-bold px-4 shadow-sm">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Cliente
            </a>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-0"><i
                                class="fas fa-search search-icon"></i></span>
                    </div>
                    <input type="text" id="clienteSearch" class="form-control border-0"
                        placeholder="Buscar por nombre, email o teléfono...">
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaClientes">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 font-weight-bold pl-4">Cliente</th>
                                <th class="border-0 font-weight-bold">Contacto</th>
                                <th class="border-0 font-weight-bold">Ubicación</th>
                                <th class="border-0 font-weight-bold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="pl-4 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3 text-secondary font-weight-bold"
                                                    style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($row['nombre'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold text-dark">
                                                        <?php echo htmlspecialchars($row['nombre']); ?>
                                                    </h6>
                                                </div>
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
                                                class="btn btn-outline-info btn-circle-action shadow-sm" data-toggle="tooltip"
                                                title="Ver Perfil y Mascotas">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i><br>
                                        No se encontraron clientes registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 text-right">
                <small class="text-muted">Total de clientes: <strong><?php echo $result->num_rows; ?></strong></small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Buscador en vivo
            $("#clienteSearch").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $("#tablaClientes tbody tr").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>