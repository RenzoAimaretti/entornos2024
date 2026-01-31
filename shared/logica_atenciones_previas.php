<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../index.php');
  exit();
}
$profesionalId = $_SESSION['usuario_id'];

require_once 'db.php';

$sql = "SELECT a.id, 
               a.fecha, 
               m.id AS id_mascota, 
               m.nombre AS paciente, 
               m.raza,
               s.nombre AS servicio, 
               a.detalle
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        INNER JOIN servicios s ON a.id_serv = s.id
        WHERE a.id_pro = ? AND a.fecha < NOW()
        ORDER BY a.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
?>