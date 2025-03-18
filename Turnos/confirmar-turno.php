<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $profesional = $_POST['profesional'];
  $fecha = $_POST['fecha'];
  $hora = $_POST['hora'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $celular = $_POST['celular'];
  $icalendar = $_POST['icalendar'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Insertar el turno confirmado en la base de datos
  $sql = "INSERT INTO turnos_confirmados (profesional, fecha, hora, correo, telefono, celular, icalendar) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssss", $profesional, $fecha, $hora, $correo, $telefono, $celular, $icalendar);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo 'Turno confirmado con éxito';
  } else {
    echo 'Error al confirmar el turno';
  }

  $stmt->close();
  $conn->close();
}
?>