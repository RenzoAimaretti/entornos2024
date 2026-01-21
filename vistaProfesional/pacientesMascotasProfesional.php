<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

// Cargar variables de entorno
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$profesionalId = $_SESSION['usuario_id'];

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT m.id, m.nombre, m.raza, m.fecha_nac
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        WHERE a.id_pro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mascotas Atendidas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php 
 //require_once '../shared/navbar.php'; ?>
<div class="container my-4">
    <h2 class="mb-4">Mascotas Atendidas</h2>
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Raza</th>
                    <th>Edad</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="../shared/detalle-mascota.php?idMascota=<?php echo urlencode($row['id']); ?>">
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </a>
                    </td>

                    <td><?php echo htmlspecialchars($row['raza']); ?></td>
                    <td>
                        <?php
                        if ($row['fecha_nac']) {
                            $nacimiento = new DateTime($row['fecha_nac']);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($nacimiento);
                            echo $edad->y . ' años';
                        } else {
                            echo 'No registrada';
                        }
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No has atendido mascotas aún.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>