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

$id_pro = $_POST['id_pro'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$horariosDisponibles = [];

if ($id_pro && $fecha) {
  // Definir la conversión de día de la semana de número a string
  $diasSemana = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
  $diaSemanaNum = date('w', strtotime($fecha)); // 0 para Domingo, 6 para Sábado
  $diaSemanaStr = $diasSemana[$diaSemanaNum];

  // Consulta para obtener los horarios de atención y excluir los ya ocupados
  $sql = "SELECT DISTINCT ph.horaIni, ph.horaFin
            FROM profesionales_horarios ph
            WHERE ph.idPro = ? AND ph.diaSem = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $id_pro, $diaSemanaStr);
  $stmt->execute();
  $result = $stmt->get_result();
  $horarioAtencion = $result->fetch_assoc();
  $stmt->close();

  if ($horarioAtencion) {
    $hora_inicio = $horarioAtencion['horaIni'];
    $hora_fin = $horarioAtencion['horaFin'];

    // Obtener todos los turnos ocupados para ese día
    $sqlOcupados = "SELECT TIME(fecha) AS hora_ocupada FROM atenciones WHERE id_pro = ? AND DATE(fecha) = ?";
    $stmtOcupados = $conn->prepare($sqlOcupados);
    $stmtOcupados->bind_param("is", $id_pro, $fecha);
    $stmtOcupados->execute();
    $resultOcupados = $stmtOcupados->get_result();
    $horariosOcupados = array_column($resultOcupados->fetch_all(MYSQLI_ASSOC), 'hora_ocupada');
    $stmtOcupados->close();

    // Generar y filtrar los horarios de 15 minutos
    $horaActual = strtotime($hora_inicio);
    $horaFinTimestamp = strtotime($hora_fin);

    while ($horaActual < $horaFinTimestamp) {
      $slot = date('H:i:s', $horaActual);
      if (!in_array($slot, $horariosOcupados)) {
        $horariosDisponibles[] = $slot;
      }
      $horaActual = strtotime('+15 minutes', $horaActual);
    }
  }
}

$conn->close();
echo json_encode($horariosDisponibles);