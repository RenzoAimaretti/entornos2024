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

$id_especialista = $_POST['id_especialista'] ?? '';
if (!$id_especialista) {
  ob_end_clean();
  echo json_encode([]);
  exit;
}

$sql = "SELECT s.id, s.nombre
        FROM servicios s
        INNER JOIN especialidad e ON s.id_esp = e.id
        INNER JOIN profesionales p ON p.id_esp = e.id
        WHERE p.id = ?
        ORDER BY s.nombre ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_especialista);
$stmt->execute();
$res = $stmt->get_result();

$servicios = [];
while ($row = $res->fetch_assoc()) {
  $servicios[] = $row;
}

ob_end_clean();
echo json_encode($servicios);
exit;
?>