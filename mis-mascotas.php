<?php
session_start();
$id = $_SESSION['usuario_id'] ?? 0;
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}
?>
<?php
require 'vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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
  <title>Mis Mascotas</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <?php require_once 'shared/navbar.php'; ?>


  <div class="container mt-5">
    <h1>Mis Mascotas</h1>
    <div class="row">
      <div class="col-md-6">
        
        <?php 
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
        }
        ?>
        <h2>Registrar Nueva Mascota para <?php echo $nombre?></h2>
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
      <div class="col-md-6">
        <h2>Mascotas Registradas</h2>
        <h2 style="color: red;">LAS MASCOTAS NO SE BORRAN, SOLO SE LES DA BAJA LOGICA</h2>
        <?php
        

        $usuario_id = $_SESSION['usuario_id'];
        $sql = "SELECT * FROM mascotas WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          // echo "<ul class='list-group'>";
          // while ($row = $result->fetch_assoc()) {
          //   echo "<li class='list-group-item'>";
          //   echo "<h5>" . $row['nombre'] . "</h5>";
          //   if ($row['foto']) {
          //     echo "<img src='" . $row['foto'] . "' alt='" . $row['nombre'] . "' class='img-fluid' style='max-width: 100px;'>";
          //   }
          //   echo "<p>Raza: " . $row['raza'] . "</p>";
          //   echo "<p>Fecha de Nacimiento: " . $row['fecha_nac'] . "</p>";
          //   echo "<form action='eliminar-mascota.php' method='POST' style='display:inline;' onsubmit='return confirmarEliminacion()'>";
          //   echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
          //   echo "<button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>";
          //   echo "</form>";
          //   echo "</li>";
          // }
          // echo "</ul>";
        } else {
          echo "<p>No tienes mascotas registradas.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    function confirmarEliminacion() {
      return confirm('¿Estás seguro de que deseas eliminar esta mascota?');
    }
  </script>
</body>

</html>