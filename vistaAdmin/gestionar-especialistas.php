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

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="searchInput" class="form-control border-0"
                        placeholder="Buscar por nombre, especialidad o email...">
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaEspecialistas">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 font-weight-bold pl-4">Profesional</th>
                                <th class="border-0 font-weight-bold">Especialidad</th>
                                <th class="border-0 font-weight-bold">Contacto</th>
                                <th class="border-0 font-weight-bold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="pl-4 align-middle">
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
                                            <span
                                                class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($row['especialidad']); ?></span>
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
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i><br>
                                        No hay especialistas registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 text-right">
            <small class="text-muted">Total mostrados: <?php echo $result->num_rows; ?></small>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Script de búsqueda en vivo
        $(document).ready(function () {
            $("#searchInput").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $("#tablaEspecialistas tbody tr").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>