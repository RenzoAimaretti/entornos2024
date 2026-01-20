<?php
session_start();
// Establecemos la zona horaria para que la validación sea exacta
date_default_timezone_set('America/Argentina/Buenos_Aires');

$id = $_SESSION['usuario_id'] ?? 0;
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

// Obtener fecha de hoy para el límite del calendario
$hoy = date('Y-m-d');

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          WHERE u.id = $id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Mascotas</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5 mb-4">
    <h1>Mis Mascotas</h1>
    <div class="row">
      <div class="col-md-6">
        <?php
        if ($result && $result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $nombre = $row['nombre'];
        }
        ?>
        <h2>Registrar Nueva Mascota para <?php echo htmlspecialchars($nombre) ?></h2>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'fecha'): ?>
          <div class="alert alert-danger">La fecha de nacimiento no puede ser futura.</div>
        <?php endif; ?>

        <form action="../shared/alta-mascota.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id_cliente" value="<?php echo $id ?>">
          <div class="form-group">
            <label for="nombre">Nombre de la mascota</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>
          <div class="form-group">
            <label for="raza">Raza</label>
            <input type="text" class="form-control" id="raza" name="raza">
          </div>
          <div class="form-group">
            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
              max="<?php echo $hoy; ?>" required>
          </div>
          <div class="form-group">
            <label for="foto">Foto</label>
            <input type="file" class="form-control-file" id="foto" name="foto">
          </div>
          <button type="submit" class="btn btn-primary">Registrar Mascota</button>
        </form>
      </div>

      <div class="col-md-6">
        <h2>Mascotas Registradas</h2>
        <?php
        $usuario_id = $_SESSION['usuario_id'];
        $sql = "SELECT * FROM mascotas WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result_mascotas = $stmt->get_result();

        if ($result_mascotas->num_rows > 0) {
          echo "<ul class='list-group'>";
          while ($row = $result_mascotas->fetch_assoc()) {
            echo "<li class='list-group-item'>";
            echo "<h5>" . htmlspecialchars($row['nombre']) . "</h5>";

            if (!empty($row['foto'])) {
              echo "<img src='" . htmlspecialchars($row['foto']) . "' alt='" . htmlspecialchars($row['nombre']) . "' class='img-fluid mb-2' style='max-width: 100px; display: block;'>";
            }

            echo "<p class='mb-1'>Raza: " . htmlspecialchars($row['raza'] ?? 'N/A') . "</p>";
            echo "<a href='../shared/detalle-mascota.php?idMascota=" . $row['id'] . "' class='btn btn-info btn-sm'>Ver</a>";
            echo "</li>";
          }
          echo "</ul>";
        } else {
          echo "<p>No tienes mascotas registradas.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
      </div>
    </div>
  </div>

  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>