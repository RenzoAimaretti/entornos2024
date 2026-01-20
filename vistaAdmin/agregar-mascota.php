<?php
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          where u.id = $id";

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
    <!-- Título -->
    <div class="container-fluid my-4">
        <?php
        $nombre = "Cliente no encontrado";
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
        }
        ?>
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d; width: 100%;">Detalles de
            <?php echo htmlspecialchars($nombre); ?></h2>
    </div>
    <div class="d-flex justify-content-center">
        <div class="card text-center" style="width:50rem;">
            <h3 class="card-title">Alta de mascota para <?php echo $row["nombre"] ?></h3>
            <div class="card-body">
                <form action="../shared/alta-mascota.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre de la mascota</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="raza">Raza (opcional)</label>
                        <input type="text" class="form-control" id="raza" name="raza">
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de nacimiento (opcional)</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="fecha_muerte">Fecha de muerte (opcional)</label>
                        <input type="date" class="form-control" id="fecha_muerte" name="fecha_muerte">
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Mascota</button>
                </form>
            </div>

        </div>
        <!-- Bootstrap Scripts -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
        <?php require_once '../shared/footer.php'; ?>
</body>


</html>