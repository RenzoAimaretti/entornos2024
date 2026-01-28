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

$usuario_id = $_SESSION['usuario_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$sqlFilter = '';
$sqlOrder = '';

$btnUpcomingClass = 'btn-outline-teal';
$btnCompletedClass = 'btn-outline-secondary';

$now = date('Y-m-d H:i:s');

if ($filter === 'completed') {
  $sqlFilter = "AND atenciones.fecha < '$now'";
  $sqlOrder = "ORDER BY atenciones.fecha DESC";
  $btnCompletedClass = 'btn-secondary text-white';
} else {
  $sqlFilter = "AND atenciones.fecha >= '$now'";
  $sqlOrder = "ORDER BY atenciones.fecha ASC";
  $btnUpcomingClass = 'btn-teal text-white';
}

$sql = "SELECT atenciones.id, atenciones.fecha, servicios.nombre AS servicio, 
               usuarios.nombre AS profesional, mascotas.nombre AS mascota
        FROM atenciones
        INNER JOIN servicios ON atenciones.id_serv = servicios.id
        INNER JOIN profesionales ON atenciones.id_pro = profesionales.id
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN mascotas ON atenciones.id_mascota = mascotas.id
        WHERE mascotas.id_cliente = ? $sqlFilter $sqlOrder";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$turnos = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $turnos[] = $row;
  }
}

$stmt->close();
$conn->close();
?>