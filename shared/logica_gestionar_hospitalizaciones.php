<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
  header('Location: ../index.php');
  exit();
}

require_once 'db.php';

$queryActivas = "SELECT h.*, m.nombre as mascota, u.nombre as profesional 
                 FROM hospitalizaciones h
                 INNER JOIN mascotas m ON h.id_mascota = m.id
                 INNER JOIN usuarios u ON h.id_pro_deriva = u.id
                 WHERE h.estado = 'Activa' ORDER BY h.fecha_ingreso ASC";
$resActivas = $conn->query($queryActivas);

$queryHistorial = "SELECT h.*, m.nombre as mascota, u.nombre as profesional 
                   FROM hospitalizaciones h
                   INNER JOIN mascotas m ON h.id_mascota = m.id
                   INNER JOIN usuarios u ON h.id_pro_deriva = u.id
                   WHERE h.estado = 'Finalizada' 
                   ORDER BY h.fecha_egreso_real DESC 
                   LIMIT 5";
$resHistorial = $conn->query($queryHistorial);
?>