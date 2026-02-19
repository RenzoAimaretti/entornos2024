<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'No autorizado']);
  exit();
}

$stmt = $conn->prepare("SELECT id, nombre FROM mascotas ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();
$mascotas = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($mascotas);

$conn->close();
?>