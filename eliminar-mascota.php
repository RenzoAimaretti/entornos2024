<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mascotaId = $_POST['id'];

  require_once 'shared/db.php';

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

  $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'mis-mascotas.php';
  header('Location: ' . $referer);
  exit();
}
?>