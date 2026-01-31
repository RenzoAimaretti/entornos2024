<?php
session_start();

date_default_timezone_set('America/Argentina/Buenos_Aires');

$id = $_SESSION['usuario_id'] ?? 0;
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require_once 'db.php';

$hoy = date('Y-m-d');

$query = "SELECT u.id, u.nombre FROM usuarios u WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$nombre_cliente = ($result && $row = $result->fetch_assoc()) ? $row['nombre'] : "Cliente";

$sql_mascotas = "SELECT * FROM mascotas WHERE id_cliente = ?";
$stmt_mascotas = $conn->prepare($sql_mascotas);
$stmt_mascotas->bind_param('i', $id);
$stmt_mascotas->execute();
$result_mascotas = $stmt_mascotas->get_result();
?>