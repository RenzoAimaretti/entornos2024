<?php
if (isset($_GET['id']) && isset($_GET['fecha'])) {
  $servicioId = $_GET['id'];
  $fecha = $_GET['fecha'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  require 'vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  // Crear conexión
  $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Obtener los horarios disponibles del servicio para la fecha seleccionada
  $sql = "SELECT hora_inicio, hora_fin FROM horarios_servicio WHERE servicio_id = ? AND dia = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $servicioId, $fecha);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    echo '<div class="d-flex flex-wrap">';
    while ($row = $result->fetch_assoc()) {
      $horaInicio = $row['hora_inicio'];
      $horaFin = $row['hora_fin'];
      echo '<button class="btn btn-outline-primary m-2">' . $horaInicio . '</button>';
    }
    echo '</div>';
  } else {
    echo '<p>No hay horarios disponibles para esta fecha.</p>';
  }

  $stmt->close();
  $conn->close();
}
?>