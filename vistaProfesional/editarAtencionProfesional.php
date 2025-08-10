<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}
$profesionalId = $_SESSION['usuario_id'];

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $detalle = $_POST['detalle'];
    $sql = "UPDATE atenciones SET detalle = ? WHERE id = ? AND id_pro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $detalle, $id, $profesionalId);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Atención actualizada correctamente.";
}

function get_param($key) {
    if (isset($_GET[$key])) {
        return $_GET[$key];
    } elseif (isset($_POST[$key])) {
        return $_POST[$key];
    }
    return null;
}

$id = get_param('id');

if (!$id) {
    die("ID de atención no proporcionado.");
}


$sql = "SELECT a.*, m.nombre AS mascota, s.nombre AS servicio
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        INNER JOIN servicios s ON a.id_serv = s.id
        WHERE a.id = ? AND a.id_pro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
$atencion = $result->fetch_assoc();
$stmt->close();
$conn->close();
if (!$atencion) {
    die("Atención no encontrada o no autorizada.");
}
$fecha = date('Y-m-d', strtotime($atencion['fecha']));
$hora = date('H:i', strtotime($atencion['fecha']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Atención</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php //require_once '../shared/navbar.php'; ?>
<div class="container my-4">
    <h2>Editar Atención</h2>
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($atencion['id']); ?>">
        <div class="form-group">
            <label>Mascota</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($atencion['mascota']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Servicio</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($atencion['servicio']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Fecha</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($fecha); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Hora</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($hora); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Detalle de la atención</label>
            <textarea name="detalle" class="form-control" rows="5"><?php echo htmlspecialchars($atencion['detalle']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="dashboardProfesional.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>