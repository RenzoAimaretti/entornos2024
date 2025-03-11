<?php
if (isset($_GET['id'])) {
  $profesionalId = $_GET['id'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Consulta para obtener los horarios del profesional
  $sql = "SELECT dia, hora_inicio, hora_fin, especialidad FROM horarios JOIN profesionales ON horarios.profesional_id = profesionales.id WHERE profesional_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $profesionalId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    echo "<table class='table'>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>" . $row['dia'] . "</td>";
      echo "<td>";
      echo "<strong>De " . date('H:i', strtotime($row['hora_inicio'])) . " a " . date('H:i', strtotime($row['hora_fin'])) . "</strong><br>";
      echo $row['especialidad'];
      echo "</td>";
      echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
  } else {
    echo "<p>No se encontraron horarios.</p>";
  }

  $stmt->close();
  $conn->close();
}
?>