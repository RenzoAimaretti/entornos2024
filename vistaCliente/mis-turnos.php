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
  <?php require_once '../shared/navbar.php'; ?>


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