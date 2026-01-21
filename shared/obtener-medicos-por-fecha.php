<?php
// Limpiar cualquier salida previa accidental
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

// Obtener día en inglés para mapeo
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

// Consulta con DISTINCT para evitar duplicados
$sql = "SELECT DISTINCT u.id, u.nombre, s.id AS id_serv, s.nombre AS nombre_serv
        FROM usuarios u
        INNER JOIN profesionales_horarios ph ON u.id = ph.idPro
        INNER JOIN profesionales p ON u.id = p.id
        INNER JOIN especialidad e ON p.id_esp = e.id
        INNER JOIN servicios s ON s.id_esp = e.id
        WHERE ph.diaSem = ? AND u.tipo = 'especialista'
        ORDER BY u.nombre ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dia_busqueda);
$stmt->execute();
$res = $stmt->get_result();

$medicos = [];
while ($row = $res->fetch_assoc()) {
  $medicos[] = $row;
}

// Limpiar buffer y enviar JSON puro
ob_end_clean();
echo json_encode($medicos);
exit;