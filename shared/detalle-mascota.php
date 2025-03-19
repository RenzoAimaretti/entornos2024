<!-- ES VISIBLE PARA LOS 3 PERO CON DIFERENTES OPCIONES -->
<?php
session_start();
$idMascota = $_GET['idMascota'];
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

$query = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue
          FROM mascotas m 
          WHERE m.id = $idMascota";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mascota_nombre = $row['mascota_nombre'];
    $raza = $row['raza'];
    $fecha_nac = $row['fecha_nac'];
    $fecha_mue = $row['fecha_mue'];
    $idMascota = $row['id'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body>
<div class="d-flex justify-content-center">
    <div class="card text-center" style="width:50rem;">
        <div class="card-header">
            <h2>Detalles de <?php echo $mascota_nombre?> </h2>
        </div>
        <div class="card-body">
        <form action="editar-mascota.php?idMascota=<?php echo htmlspecialchars($idMascota); ?>" method="POST">
            <input type="hidden" name="idMascota" value="<?php echo htmlspecialchars($idMascota); ?>">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $mascota_nombre; ?>" required>
            </div>
            <div class="form-group">
                <label for="raza">Raza:</label>
                <input type="text" class="form-control" id="raza" name="raza" value="<?php echo $raza; ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_nac">Fecha de Nacimiento:</label>
                <input type="date" class="form-control" id="fecha_nac" name="fecha_nac" value="<?php echo $fecha_nac; ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_mue">Fecha de Muerte (si aplica):</label>
                <input type="date" class="form-control" id="fecha_mue" name="fecha_mue" value="<?php echo $fecha_mue; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='eliminar-mascota.php?idMascota=<?php echo $idMascota; ?>'">Eliminar Mascota</button>
        </form>
        </div>
    
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>