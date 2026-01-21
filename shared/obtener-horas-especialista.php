<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  ob_end_clean();
  echo json_encode(['disponibles' => []]);
  exit;
}

$id_pro = $_POST['id_pro'] ?? 0;
$fecha_raw = $_POST['fecha'] ?? '';

if (!$id_pro || !$fecha_raw) {
  ob_end_clean();
  echo json_encode(['disponibles' => []]);
  exit;
}

$dia_ingles = date('l', strtotime($fecha_raw));
$mapeo = ['Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mie', 'Thursday' => 'Jue', 'Friday' => 'Vie', 'Saturday' => 'Sab', 'Sunday' => 'Dom'];
$dia_busqueda = $mapeo[$dia_ingles];

$sql = "SELECT horaIni, horaFin FROM profesionales_horarios WHERE idPro = ? AND diaSem = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_pro, $dia_busqueda);
$stmt->execute();
$res = $stmt->get_result();

$response = ['disponibles' => []];

while ($h = $res->fetch_assoc()) {
  $inicio = strtotime($h['horaIni']);
  $fin = strtotime($h['horaFin']);
  while ($inicio < $fin) {
    $hora = date("H:i", $inicio);
    if (!in_array($hora, $response['disponibles'])) {
      $response['disponibles'][] = $hora;
    }
    $inicio = strtotime('+30 minutes', $inicio);
  }
}
sort($response['disponibles']);

ob_end_clean();
echo json_encode($response);
exit;