<?php
session_start();
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../index.php');
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

$query = "Select m.id,m.nombre,raza,fecha_nac,fecha_mue,u.nombre as nombreDueño from mascotas m 
inner join clientes c on m.id_cliente=c.id
inner join usuarios u on c.id=u.id 
ORDER BY m.nombre ASC";

$result = $conn->query($query);
$mascotas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mascotas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Mascotas - Veterinaria San Antón</title>
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
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0">Gestión de Mascotas</h2>
                <p class="text-muted small mb-0">Listado general de pacientes</p>
            </div>
            <a href="gestionar-clientes.php" class="btn btn-success shadow-sm rounded-pill font-weight-bold px-4">
                <i class="fas fa-paw mr-2"></i> Nueva Mascota
            </a>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-0"><i
                                class="fas fa-search search-icon"></i></span>
                    </div>
                    <input type="text" id="mascotaSearch" class="form-control border-0"
                        placeholder="Buscar por nombre, raza o dueño...">
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaMascotas">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 font-weight-bold pl-4">Nombre</th>
                                <th class="border-0 font-weight-bold">Raza</th>
                                <th class="border-0 font-weight-bold">Dueño</th>
                                <th class="border-0 font-weight-bold">Estado</th>
                                <th class="border-0 font-weight-bold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mascotas)): ?>
                                <?php foreach ($mascotas as $mascota): ?>
                                    <tr>
                                        <td class="pl-4 align-middle font-weight-bold text-dark">
                                            <?php echo htmlspecialchars($mascota['nombre']); ?>
                                        </td>
                                        <td class="align-middle text-muted">
                                            <?php echo htmlspecialchars($mascota['raza'] ?? 'Desconocida'); ?>
                                        </td>
                                        <td class="align-middle">
                                            <i class="fas fa-user-circle text-muted mr-1"></i>
                                            <?php echo htmlspecialchars($mascota['nombreDueño']); ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($mascota['fecha_mue']): ?>
                                                <span class="badge badge-danger px-2">Fallecido</span>
                                            <?php else: ?>
                                                <span class="badge badge-success px-2">Activo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo $mascota['id']; ?>"
                                                class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm">
                                                Ver Ficha
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-paw fa-3x mb-3 opacity-25"></i><br>
                                        No hay mascotas registradas en el sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 text-right">
                <small class="text-muted">Total de mascotas: <strong><?php echo count($mascotas); ?></strong></small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Buscador en vivo
            $("#mascotaSearch").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $("#tablaMascotas tbody tr").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>