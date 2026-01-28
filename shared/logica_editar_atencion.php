<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
  header('Location: ../index.php');
  exit();
}
$profesionalId = $_SESSION['usuario_id'];

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $detalle = $_POST['detalle'];

  $sql = "UPDATE atenciones SET detalle = ? WHERE id = ? AND id_pro = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $detalle, $id, $profesionalId);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: dashboardProfesional.php?success=Informe actualizado correctamente");
    exit();
  } else {
    echo "Error al guardar.";
  }
}

function get_param($key)
{
  if (isset($_GET[$key]))
    return $_GET[$key];
  elseif (isset($_POST[$key]))
    return $_POST[$key];
  return null;
}

$id = get_param('id');

if (!$id) {
  die("ID de atención no proporcionado.");
}

$sql = "SELECT a.*, m.nombre AS mascota, m.raza, s.nombre AS servicio
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        INNER JOIN servicios s ON a.id_serv = s.id
        WHERE a.id = ? AND a.id_pro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
$atencion = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$atencion) {
  die("Atención no encontrada o no tienes permisos para verla.");
}

$fecha = date('d/m/Y', strtotime($atencion['fecha']));
$hora = date('H:i', strtotime($atencion['fecha']));
?>