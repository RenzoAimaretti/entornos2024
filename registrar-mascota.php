<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

// Conexión a la base de datos (ajusta los parámetros según tu configuración)
$conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

$nombre = $_POST['nombre'];
$foto = $_POST['foto'];
$raza = $_POST['raza'];
$fecha_nac = $_POST['fecha_nac'];
$id_cliente = $_SESSION['usuario_id'];

$sql = "INSERT INTO mascotas (id_cliente, nombre, foto, raza, fecha_nac) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issss', $id_cliente, $nombre, $foto, $raza, $fecha_nac);

if ($stmt->execute()) {
  header('Location: mis-mascotas.php');
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>