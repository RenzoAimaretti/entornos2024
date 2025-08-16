<?php
require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  http_response_code(500);
  die("Error de conexión: " . $conn->connect_error);
}

header('Content-Type: application/json');

$idPro = isset($_GET['idPro']) ? intval($_GET['idPro']) : 0;
$horarios = [];

if ($idPro > 0) {
  $sql = "SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = ? ORDER BY FIELD(diaSem, 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom')";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $idPro);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $horarios[] = $row;
  }
  $stmt->close();
}

$conn->close();

echo json_encode($horarios);
?>