<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'No autorizado']);
  exit();
}

if (isset($_POST['id_pro'])) {
  $id_pro = intval($_POST['id_pro']);
  $stmt = $conn->prepare("SELECT DISTINCT m.id, m.nombre FROM mascotas m INNER JOIN atenciones a ON a.id_mascota = m.id WHERE a.id_pro = ? ORDER BY m.nombre");
  $stmt->bind_param("i", $id_pro);
  $stmt->execute();
  $result = $stmt->get_result();
  $mascotas = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode($mascotas);
}

$conn->close();
?>