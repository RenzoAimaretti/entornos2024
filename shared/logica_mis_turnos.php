<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require_once 'db.php';

$usuario_id = $_SESSION['usuario_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';

$btnUpcomingClass = 'btn-outline-teal';
$btnCompletedClass = 'btn-outline-secondary';

date_default_timezone_set('America/Argentina/Buenos_Aires');
$now = date('Y-m-d H:i:s');

if ($filter === 'completed') {
  $sqlFilter = "AND ((atenciones.detalle IS NOT NULL AND atenciones.detalle <> '') OR atenciones.fecha < '$now')";
  $sqlOrder = "ORDER BY atenciones.fecha DESC";
  $btnCompletedClass = 'btn-secondary text-white';
} else {
  $sqlFilter = "AND (atenciones.detalle IS NULL OR atenciones.detalle = '') AND atenciones.fecha >= '$now'";
  $sqlOrder = "ORDER BY atenciones.fecha ASC";
  $btnUpcomingClass = 'btn-teal text-white';
}

$sql = "SELECT atenciones.id, atenciones.fecha, servicios.nombre AS servicio, 
               usuarios.nombre AS profesional, mascotas.nombre AS mascota, atenciones.detalle
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
while ($row = $result->fetch_assoc()) {
  $turnos[] = $row;
}

$stmt->close();
$conn->close();
?>