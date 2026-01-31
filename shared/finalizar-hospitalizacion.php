<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../vistaAdmin/gestionar-hospitalizacion.php?res=error_permiso');
  exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_hosp'])) {
  $id = intval($_POST['id_hosp']);
  $fecha_salida = date('Y-m-d H:i:s');

  $sql = "UPDATE hospitalizaciones SET fecha_egreso_real = ?, estado = 'Finalizada' WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("si", $fecha_salida, $id);

  if ($stmt->execute()) {
    header("Location: ../vistaAdmin/gestionar-hospitalizacion.php?res=alta_ok");
  }
}