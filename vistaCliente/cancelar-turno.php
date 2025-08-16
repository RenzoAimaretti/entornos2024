<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $turno_id = $_POST['id'];
  $sql = "DELETE FROM atenciones WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $turno_id);

  if ($stmt->execute()) {
    // Redirigir de vuelta a mis-turnos.php
    header('Location: ../vistaCliente/mis-turnos.php');
    exit();
  } else {
    echo "Error al cancelar el turno: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
?>