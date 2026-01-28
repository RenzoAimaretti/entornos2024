<?php
session_start();
if (!isset($_SESSION['usuario_tipo'])) {
  header('Location: ../iniciar-sesion.php');
  exit();
}
if (!isset($_GET['idMascota'])) {
  die("ID de mascota no proporcionado.");
}

require_once '../shared/db.php';

$idMascota = intval($_GET['idMascota']);
$hoy = date('Y-m-d');
$esProfesional = ($_SESSION['usuario_tipo'] === 'admin' || $_SESSION['usuario_tipo'] === 'especialista');
$esAdmin = ($_SESSION['usuario_tipo'] === 'admin');

$stmt = $conn->prepare("SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue, m.id_cliente, m.foto FROM mascotas m WHERE m.id = ?");
$stmt->bind_param("i", $idMascota);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $mascota_nombre = $row['mascota_nombre'];
  $raza = $row['raza'];
  $fecha_nac = $row['fecha_nac'];
  $fecha_mue = $row['fecha_mue'];
  $idPropietario = $row['id_cliente'];
  $fotoMascota = $row['foto'];
} else {
  die("Mascota no encontrada.");
}

if ($_SESSION['usuario_tipo'] === 'cliente' && $_SESSION['usuario_id'] != $idPropietario) {
  die("Acceso denegado: esta mascota no le pertenece.");
}

function getHistorial($conn, $id, $filtro)
{
  $sql = "SELECT a.id, a.fecha, s.nombre as servicio, u.nombre as profesional 
            FROM atenciones a 
            INNER JOIN servicios s ON a.id_serv = s.id 
            INNER JOIN usuarios u ON a.id_pro = u.id 
            WHERE a.id_mascota = ? $filtro ORDER BY a.fecha DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  return $stmt->get_result();
}

$resH = getHistorial($conn, $idMascota, "AND s.nombre NOT LIKE '%vacuna%' AND s.nombre NOT LIKE '%corte de pelo%'");
$resVac = getHistorial($conn, $idMascota, "AND s.nombre LIKE '%vacuna%'");
$resEst = getHistorial($conn, $idMascota, "AND s.nombre LIKE '%corte de pelo%'");

$stmtHosp = $conn->prepare("SELECT h.*, u.nombre as profesional FROM hospitalizaciones h INNER JOIN usuarios u ON h.id_pro_deriva = u.id WHERE h.id_mascota = ? ORDER BY h.fecha_ingreso DESC");
$stmtHosp->bind_param("i", $idMascota);
$stmtHosp->execute();
$resHosp = $stmtHosp->get_result();
?>