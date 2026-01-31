<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../vistaAdmin/gestionar-hospitalizacion.php?res=error_permiso');
  exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_mascota = $_POST['id_mascota'];
  $motivo = $_POST['motivo'];
  $id_pro = $_SESSION['usuario_id'];
  $fecha_ingreso = date('Y-m-d H:i:s');

  $sql = "INSERT INTO hospitalizaciones (id_mascota, id_pro_deriva, fecha_ingreso, motivo, estado) VALUES (?, ?, ?, ?, 'Activa')";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iiss", $id_mascota, $id_pro, $fecha_ingreso, $motivo);

  if ($stmt->execute()) {
    header("Location: ../vistaAdmin/gestionar-hospitalizacion.php?res=ok");
  } else {
    echo "Error: " . $stmt->error;
  }
}