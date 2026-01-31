<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../index.php');
  exit();
}

require_once 'db.php';

$profesionalId = $_SESSION['usuario_id'];

$sql = "SELECT DISTINCT m.id, m.nombre, m.raza, m.fecha_nac
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        WHERE a.id_pro = ?
        ORDER BY m.nombre ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
?>