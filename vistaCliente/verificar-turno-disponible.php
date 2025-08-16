<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  http_response_code(403);
  die("Acceso denegado");
}

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  http_response_code(500);
  die("Error de conexión: " . $conn->connect_error);
}

header('Content-Type: application/json');

$response = ['disponible' => false];

$id_pro = $_POST['id_pro'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$hora = $_POST['hora'] ?? null;

if ($id_pro && $fecha && $hora) {
  $fecha_datetime = $fecha . ' ' . $hora;

  $sql = "SELECT COUNT(*) AS count FROM atenciones WHERE id_pro = ? AND fecha = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $id_pro, $fecha_datetime);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if ($row['count'] == 0) {
    $response['disponible'] = true;
  }

  $stmt->close();
}

$conn->close();

echo json_encode($response);