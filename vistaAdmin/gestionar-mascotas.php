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
inner join usuarios u on c.id=u.id";

$result = $conn->query($query);
$mascotas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mascotas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Mascotas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>
    <!-- Título -->
    <div class="container my-4">
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d;">Gestión de Mascotas</h2>
    </div>
    <!-- Tabla de Mascotas -->
    <div class="container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Raza</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Fecha de Muerte</th>
                        <th>Dueño</th>
                        <th>Acción</th>
                    </tr>

                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php foreach ($mascotas as $mascota): ?>
                            <tr>
                                <td><?php echo $mascota['nombre']; ?></td>
                                <td><?php echo $mascota['raza']; ?></td>
                                <td><?php echo htmlspecialchars($mascota['fecha_nac'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($mascota['fecha_mue'] ?? '-'); ?></td>
                                <td><?php echo $mascota['nombreDueño']; ?></td>
                                <td>
                                    <a href="../shared/detalle-mascota.php?idMascota=<?php echo $mascota['id']; ?>"
                                        class="btn btn-info">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Mascotas no encontradas, intente más tarde.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>
        </div>
    </div>

    <div class="text-center">
        <a class="btn btn-primary" href="gestionar-clientes.php">Registrar mascota</a>
    </div>


    <!-- Bootstrap Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>