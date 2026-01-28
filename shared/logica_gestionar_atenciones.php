<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

$esAdmin = ($_SESSION['usuario_tipo'] === 'admin');
$idUsuario = $_SESSION['usuario_id'];
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Especialista';

$resMascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");

$serviciosDelProfesional = [];
if (!$esAdmin) {
  $stmtEsp = $conn->prepare("SELECT id_esp FROM profesionales WHERE id = ?");
  $stmtEsp->bind_param("i", $idUsuario);
  $stmtEsp->execute();
  $resEsp = $stmtEsp->get_result();

  if ($rowEsp = $resEsp->fetch_assoc()) {
    $idEspecialidad = $rowEsp['id_esp'];
    $stmtServ = $conn->prepare("SELECT id, nombre FROM servicios WHERE id_esp = ? ORDER BY nombre ASC");
    $stmtServ->bind_param("i", $idEspecialidad);
    $stmtServ->execute();
    $resServ = $stmtServ->get_result();
    while ($rowS = $resServ->fetch_assoc()) {
      $serviciosDelProfesional[] = $rowS;
    }
  }
}
?>