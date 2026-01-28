<?php
session_start();
$id = $_GET['id'] ?? 0;

if ($_SESSION['usuario_tipo'] !== 'admin') {
  die("Acceso denegado");
}

require_once '../shared/db.php';

$registroExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $conn->prepare("INSERT INTO mascotas (nombre, raza, fecha_nac, fecha_mue, id_cliente) VALUES (?, ?, ?, ?, ?)");
  $nac = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
  $mue = !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;
  $stmt->bind_param("ssssi", $_POST['nombre'], $_POST['raza'], $nac, $mue, $_POST['id_cliente']);

  if ($stmt->execute()) {
    $registroExitoso = true;
  }
  $stmt->close();
}

$stmtCli = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmtCli->bind_param("i", $id);
$stmtCli->execute();
$cliente = $stmtCli->get_result()->fetch_assoc();
$nombreCliente = $cliente ? $cliente['nombre'] : "Cliente no encontrado";
$hoy = date('Y-m-d');
?>