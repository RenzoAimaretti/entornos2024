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

$idPro = isset($_GET['idPro']) ? intval($_GET['idPro']) : 0;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

$horarios_disponibles = [];

if ($idPro > 0 && !empty($fecha)) {
  // Calcular el día de la semana a partir de la fecha
  $diaSemanaNumero = date('N', strtotime($fecha));
  $diasSemana = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
  $diaSemana = $diasSemana[$diaSemanaNumero];

  // Consultar horarios de atención del profesional y excluir los que ya tienen turno
  $sql = "SELECT ht.id AS id_horario, ht.hora
            FROM horarios_turnos ht
            INNER JOIN profesionales_horarios ph ON ph.horaIni <= ht.hora AND ph.horaFin > ht.hora
            WHERE ph.idPro = ? AND ph.diaSem = ?
            AND NOT EXISTS (
                SELECT 1 FROM atenciones a
                WHERE a.id_pro = ? AND a.fecha = ?
            )
            ORDER BY ht.hora ASC";

  $stmt = $conn->prepare($sql);
  $fecha_dt = $fecha . ' 00:00:00'; // La tabla `atenciones` usa DATETIME
  $stmt->bind_param('siss', $idPro, $diaSemana, $idPro, $fecha_dt);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $horarios_disponibles[] = $row;
  }
  $stmt->close();
}

echo json_encode($horarios_disponibles);

$conn->close();