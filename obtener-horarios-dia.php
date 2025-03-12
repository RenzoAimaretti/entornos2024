<?php
session_start();
if (isset($_GET['id']) && isset($_GET['fecha'])) {
  $profesionalId = $_GET['id'];
  $fecha = $_GET['fecha'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Obtener los horarios disponibles del profesional para la fecha seleccionada
  $sql = "SELECT hora_inicio, hora_fin FROM horarios WHERE profesional_id = ? AND dia = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $profesionalId, $fecha);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $horaInicio = $row['hora_inicio'];
      $horaFin = $row['hora_fin'];
      echo '<button class="btn btn-outline-primary m-2" onclick="seleccionarHorario(\'' . $horaInicio . '\')">' . $horaInicio . ' - ' . $horaFin . '</button>';
    }
  } else {
    echo '<p>No hay horarios disponibles para esta fecha.</p>';
  }

  $stmt->close();
  $conn->close();
}
?>