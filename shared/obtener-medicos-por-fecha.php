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
  echo json_encode([]);
  exit;
}

$fecha_raw = $_POST['fecha'] ?? '';
if (!$fecha_raw) {
  ob_end_clean();
  echo json_encode([]);
  exit;
}

$dia_ingles = date('l', strtotime($fecha_raw));
$mapeo = [
  'Monday' => 'Lun',
  'Tuesday' => 'Mar',
  'Wednesday' => 'Mie',
  'Thursday' => 'Jue',
  'Friday' => 'Vie',
  'Saturday' => 'Sab',
  'Sunday' => 'Dom'
];
$dia_busqueda = $mapeo[$dia_ingles];

$sql = "SELECT u.id, u.nombre
        FROM usuarios u
        INNER JOIN profesionales_horarios ph ON u.id = ph.idPro
        WHERE ph.diaSem = ? AND u.tipo = 'especialista'
        GROUP BY u.id, u.nombre
        ORDER BY u.nombre ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dia_busqueda);
$stmt->execute();
$res = $stmt->get_result();

$medicos = [];
while ($row = $res->fetch_assoc()) {
  $medicos[] = $row;
}

ob_end_clean();
echo json_encode($medicos);
exit;