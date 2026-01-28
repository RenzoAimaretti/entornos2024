<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || ($_SESSION['usuario_tipo'] !== 'admin' && $_SESSION['usuario_tipo'] !== 'especialista')) {
  header('Location: ../index.php');
  exit();
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if ($id === 0) {
  die("ID de especialista no proporcionado.");
}

require_once '../shared/db.php';

$stmt = $conn->prepare("SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
                        FROM usuarios u 
                        INNER JOIN profesionales p ON u.id = p.id
                        INNER JOIN especialidad e on p.id_esp = e.id
                        WHERE u.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultEsp = $stmt->get_result();

$nombre = "No encontrado";
$email = $telefono = $especialidad = "";

if ($resultEsp && $resultEsp->num_rows > 0) {
  $row = $resultEsp->fetch_assoc();
  $nombre = $row['nombre'];
  $email = $row['email'];
  $telefono = $row['telefono'];
  $especialidad = $row['especialidad'];
}

// Horarios para mostrar en lista
$hRes = $conn->query("SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id ORDER BY CASE diaSem WHEN 'Lun' THEN 1 WHEN 'Mar' THEN 2 WHEN 'Mie' THEN 3 WHEN 'Jue' THEN 4 WHEN 'Vie' THEN 5 WHEN 'Sab' THEN 6 ELSE 7 END");

// Turnos Próximos
$atNext = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                        FROM atenciones a 
                        INNER JOIN servicios s ON a.id_serv = s.id 
                        INNER JOIN mascotas m ON a.id_mascota = m.id
                        WHERE a.id_pro = $id AND a.fecha >= CURDATE() ORDER BY a.fecha ASC LIMIT 5");

// Historial Pasado
$atPast = $conn->query("SELECT a.id, a.fecha, s.nombre as serv, m.nombre as masc 
                        FROM atenciones a 
                        INNER JOIN servicios s ON a.id_serv = s.id 
                        INNER JOIN mascotas m ON a.id_mascota = m.id
                        WHERE a.id_pro = $id AND a.fecha < CURDATE() ORDER BY a.fecha DESC LIMIT 5");

// Datos para JS (Horarios Editables)
$hResJS = $conn->query("SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id");
$horariosJS = [];
while ($r = $hResJS->fetch_assoc()) {
  $horariosJS[] = $r;
}
?>