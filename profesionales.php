<?php
session_start();
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
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <div class="card bg-green mb-3 border-0 shadow-sm">
          <div class="card-header text-center border-0">
            <h3>Buscar Profesionales</h3>
          </div>
          <div class="card-body">
            <form method="GET" action="">
              <div class="form-group">
                <label for="search" class="text-white">Buscar por nombre o especialidad:</label>
                <input type="text" class="form-control" id="search" name="search" required
                  placeholder="Ej: Juan Pérez o Cirugía">
              </div>
              <button type="submit" class="btn btn-light font-weight-bold btn-block" style="color: #00897b;">
                Buscar
              </button>
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
      echo "<div class='container mb-5'>";

      echo "<div class='bg-green p-3 mb-3 rounded text-center shadow-sm'>
              <h3 class='mb-0 text-white'>Resultados de la búsqueda</h3>
            </div>";

      echo "<ul class='list-group shadow-sm'>";
      while ($row = $result->fetch_assoc()) {
        echo "<li class='list-group-item'>
                <h5 class='mb-1' style='color: #00897b;'>" . htmlspecialchars($row['nombre']) . "</h5>
                <p class='mb-1'><strong>Especialidad:</strong> " . htmlspecialchars($row['especialidad']) . "</p>
                <small class='text-muted'>
                  Teléfono: " . htmlspecialchars($row['telefono']) . " | Email: " . htmlspecialchars($row['email']) . "
                </small>
              </li>";
      }
      echo "</ul></div>";

    } else {
      echo "<div class='container text-center mb-5'>
              <div class='bg-green p-3 rounded text-white shadow-sm'>
                <h4 class='mb-0'>No se encontraron profesionales con ese criterio.</h4>
              </div>
            </div>";
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