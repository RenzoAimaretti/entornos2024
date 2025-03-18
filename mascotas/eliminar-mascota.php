<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mascotaId = $_POST['id'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Eliminar la mascota
  $sql = "DELETE FROM mascotas WHERE id = ? AND id_cliente = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ii', $mascotaId, $_SESSION['usuario_id']);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    $_SESSION['mensaje'] = "Mascota eliminada con éxito.";
  } else {
    $_SESSION['mensaje'] = "Error al eliminar la mascota.";
  }

  $stmt->close();
  $conn->close();

  header('Location: mis-mascotas.php');
  exit();
}
?>