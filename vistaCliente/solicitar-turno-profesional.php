<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Obtener los profesionales y sus especialidades
$sql = "SELECT profesionales.id, usuarios.nombre, especialidad.nombre AS especialidad 
        FROM profesionales 
        INNER JOIN usuarios ON profesionales.id = usuarios.id 
        INNER JOIN especialidad ON profesionales.id_esp = especialidad.id";
$result = $conn->query($sql);

$profesionales = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $profesionales[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5">
    <h1>Solicitar Turno</h1>
    <div class="row">
      <div class="col-md-6">
        <h2>Seleccionar Profesional</h2>
        <form>
          <div class="form-group">
            <label for="profesional">Profesional:</label>
            <select class="form-control" id="profesional" name="profesional" required>
              <?php foreach ($profesionales as $profesional): ?>
                <option value="<?php echo $profesional['id']; ?>">
                  <?php echo $profesional['nombre'] . ' - ' . $profesional['especialidad']; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="fecha_turno">Fecha del turno</label>
            <input type="date" class="form-control" id="fecha_turno" name="fecha_turno" required>
          </div>
          <!-- Puedes agregar más campos aquí si lo necesitas -->
          <button type="submit" class="btn btn-primary">Solicitar Turno</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>