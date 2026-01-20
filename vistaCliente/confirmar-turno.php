<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $profesional = $_POST['profesional'];
  $fecha = $_POST['fecha'];
  $hora = $_POST['hora'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $celular = $_POST['celular'];
  $icalendar = $_POST['icalendar'];

  require __DIR__ . '/../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
  $dotenv->load();

  // Crear conexión
  $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
  if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
  }

  // Insertar el turno en la base de datos
  $sql = "INSERT INTO turnos (profesional, fecha, hora, correo, telefono, celular, icalendar) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssss', $profesional, $fecha, $hora, $correo, $telefono, $celular, $icalendar);
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