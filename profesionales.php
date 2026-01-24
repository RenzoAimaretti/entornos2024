<?php
session_start()
  ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Buscar Profesionales</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navegación -->
  <?php require_once 'shared/navbar.php'; ?>

  <!-- Formulario de Búsqueda de Profesionales -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card bg-light mb-3">
          <div class="card-header text-center">Buscar Profesionales</div>
          <div class="card-body">
            <form method="GET" action="">
              <div class="form-group">
                <label for="search">Buscar por nombre o especialidad:</label>
                <input type="text" class="form-control" id="search" name="search" required>
              </div>
              <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
  if (isset($_GET['search'])) {
    $search = $_GET['search'];

    require 'vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
    if ($conn->connect_error) {
      die("Error de conexión: " . $conn->connect_error);
    }

    // Consulta para buscar profesionales por nombre o especialidad
    $sql = "SELECT usuarios.nombre, usuarios.email, profesionales.telefono, especialidad.nombre AS especialidad 
            FROM profesionales 
            INNER JOIN usuarios ON profesionales.id = usuarios.id 
            INNER JOIN especialidad ON profesionales.id_esp = especialidad.id 
            WHERE usuarios.nombre LIKE ? OR especialidad.nombre LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $search . '%';
    $stmt->bind_param('ss', $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      echo "<div class='container'><h2>Resultados de la búsqueda:</h2><ul class='list-group'>";
      while ($row = $result->fetch_assoc()) {
        echo "<li class='list-group-item'>" . $row['nombre'] . " - " . $row['especialidad'] . "<br>Teléfono: " . $row['telefono'] . "<br>Email: " . $row['email'] . "</li>";
      }
      echo "</ul></div>";
    } else {
      echo "<div class='container'><p>No se encontraron resultados.</p></div>";
    }

    $stmt->close();
    $conn->close();
  }
  ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php require_once 'shared/footer.php'; ?>
</body>

</html>