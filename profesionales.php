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
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="https://doctoravanevet.com/wp-content/uploads/2020/04/Servicios-vectores-consulta-integral.png"
          alt="Logo" class="logo">
        <span>Veterinaria San Antón</span>
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Inicio</a>
          </li>
          <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <li class="nav-item dropdown d-flex align-items-center">
              <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Usuario" width="40" height="40"
                class="mr-2">
              <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <?php echo $_SESSION['usuario_nombre']; ?>
              </a>
              <div class="dropdown-menu" aria-labelledby="usuarioDropdown">
                <a class="dropdown-item" href="mis-mascotas.php">Mis Mascotas</a>
                <a class="dropdown-item" href="mis-turnos.php">Mis Turnos</a>
                <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
              </div>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="iniciar-sesion.php">Iniciar sesión</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="registrarse.php">Registrarse</a>
            </li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Secciones
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item active" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item" href="contactanos.php">Contacto</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Barra de Navegación Secundaria -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
      <li class="breadcrumb-item active" aria-current="page">Buscar Profesionales</li>
    </ol>
  </nav>

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
    // Conexión a la base de datos (ajusta los parámetros según tu configuración)
    $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

    if ($conn->connect_error) {
      die("Conexión fallida: " . $conn->connect_error);
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

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <!-- Pie de página -->
  <footer class="bg-light py-4">
    <div class="container text-center">
      <p>Teléfono de contacto: 115673346</p>
      <p>Mail: sananton24@gmail.com</p>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>