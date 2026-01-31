<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../index.php');
  exit();
}

require_once 'db.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
$nombre = $_SESSION['usuario_nombre'];
$profesional_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if ($_POST['accion'] === 'finalizar') {
    $id_hosp = $_POST['id_hosp'];
    $fecha_egreso = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE hospitalizaciones SET estado = 'Finalizada', fecha_egreso_real = ? WHERE id = ?");
    $stmt->bind_param("si", $fecha_egreso, $id_hosp);

    if ($stmt->execute()) {
      header("Location: " . $_SERVER['PHP_SELF'] . "?success=Hospitalización finalizada correctamente");
    } else {
      header("Location: " . $_SERVER['PHP_SELF'] . "?error=Error al finalizar");
    }
    exit();
  } elseif ($_POST['accion'] === 'crear') {
    $id_mascota = $_POST['id_mascota'];
    $motivo = $_POST['motivo'];
    $fecha_prevista = $_POST['fecha_egreso_prevista'];
    $fecha_ingreso = date('Y-m-d H:i:s');

    if (strtotime($fecha_prevista) <= strtotime($fecha_ingreso)) {
      header("Location: " . $_SERVER['PHP_SELF'] . "?error=La fecha de egreso debe ser futura");
      exit();
    }

    $stmt = $conn->prepare("INSERT INTO hospitalizaciones (id_mascota, id_pro_deriva, fecha_ingreso, fecha_egreso_prevista, motivo, estado) VALUES (?, ?, ?, ?, ?, 'Activa')");
    $stmt->bind_param("iisss", $id_mascota, $profesional_id, $fecha_ingreso, $fecha_prevista, $motivo);

    if ($stmt->execute()) {
      header("Location: " . $_SERVER['PHP_SELF'] . "?success=Paciente ingresado a internación");
    } else {
      header("Location: " . $_SERVER['PHP_SELF'] . "?error=Error al registrar ingreso");
    }
    $stmt->close();
    exit();
  }
}

$hoy = date('Y-m-d');

$turnos_hoy = $conn->query("
    SELECT a.id, DATE_FORMAT(a.fecha, '%H:%i') AS hora, m.nombre AS nombre_mascota, s.nombre AS nombre_servicio, a.detalle
    FROM atenciones a
    INNER JOIN mascotas m ON a.id_mascota = m.id
    INNER JOIN servicios s ON a.id_serv = s.id
    WHERE a.id_pro = $profesional_id AND DATE(a.fecha) = '$hoy'
    ORDER BY a.fecha ASC
")->fetch_all(MYSQLI_ASSOC);

$hosp_activas = $conn->query("
    SELECT h.id, m.nombre AS nombre_mascota, h.fecha_ingreso, h.fecha_egreso_prevista, h.motivo 
    FROM hospitalizaciones h 
    INNER JOIN mascotas m ON h.id_mascota = m.id 
    WHERE h.estado = 'Activa' 
    ORDER BY h.fecha_ingreso ASC
")->fetch_all(MYSQLI_ASSOC);

$mascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
?>