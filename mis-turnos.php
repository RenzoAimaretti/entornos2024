<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

// Conexión a la base de datos (ajusta los parámetros según tu configuración)
$conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los turnos del usuario
$sql = "SELECT atenciones.fecha, servicios.nombre AS servicio, usuarios.nombre AS profesional
        FROM atenciones
        INNER JOIN servicios ON atenciones.id_serv = servicios.id
        INNER JOIN profesionales ON atenciones.id_pro = profesionales.id
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN mascotas ON atenciones.id_mascota = mascotas.id
        WHERE mascotas.id_cliente = ?
        ORDER BY atenciones.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$turnos = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $turnos[] = $row;
  }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Turnos</title>
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
            <a class="nav-link active" href="index.php">Inicio</a>
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
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Secciones
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item" href="contactanos.php">Contacto</a>
              <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                <a class="dropdown-item" href="./vistaAdmin/gestionar-especialistas.php">Especialistas</a>
                <a class="dropdown-item" href="./vistaAdmin/gestionar-clientes.php">Gestionar clientes</a>
              <?php endif; ?>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <h1>Mis Turnos</h1>
    <?php if (count($turnos) > 0): ?>
      <ul class="list-group">
        <?php foreach ($turnos as $turno): ?>
          <li class="list-group-item">
            <h5><?php echo $turno['servicio']; ?></h5>
            <p>Profesional: <?php echo $turno['profesional']; ?></p>
            <p>Fecha: <?php echo date('d-m-Y H:i', strtotime($turno['fecha'])); ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay turnos pendientes.</p>
    <?php endif; ?>
    <a href="solicitar-turno-profesional.php" class="btn btn-primary mt-3">Solicitar Nuevo Turno</a>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>