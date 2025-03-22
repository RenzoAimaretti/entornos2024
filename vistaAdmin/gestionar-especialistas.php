<?php
session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
          FROM usuarios u 
          inner JOIN profesionales p ON u.id = p.id
          inner JOIN especialidad e on p.id_esp = e.id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?> 

    <!-- Título -->
    <div class="container my-4">
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d;">Gestión de Clientes</h2>
    </div>

    <!-- Tabla de Clientes -->
    <div class="container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['especialidad']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                <td>
                                    <form action="detalle-especialista.php" method="post" style="display:inline;">
                                      <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                      <button type="submit" class="btn btn-info btn-sm">Ver</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Clientes no encontrados, intente más tarde.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


