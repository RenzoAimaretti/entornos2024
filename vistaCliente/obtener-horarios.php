<?php
if (isset($_GET['id'])) {
  $profesionalId = $_GET['id'];

  session_start();
  require 'vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  // Crear conexión
  $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);


  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Obtener los horarios del profesional
  $sql = "SELECT horarios_turnos.hora 
          FROM profesionales_horarios 
          INNER JOIN horarios_turnos ON profesionales_horarios.id_horario = horarios_turnos.id 
          WHERE profesionales_horarios.id_pro = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $profesionalId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
      echo "<li class='list-group-item'>" . $row['hora'] . "</li>";
    }
    echo "</ul>";
  } else {
    echo "<p>No hay horarios disponibles.</p>";
  }

  $stmt->close();
  $conn->close();
}
?>