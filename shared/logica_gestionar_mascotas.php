<?php
session_start();
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
  header('Location: ../index.php');
  exit();
}

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT m.id, m.nombre, raza, fecha_nac, fecha_mue, u.nombre as nombreDueño 
          FROM mascotas m 
          INNER JOIN clientes c on m.id_cliente=c.id
          INNER JOIN usuarios u on c.id=u.id 
          ORDER BY m.nombre ASC";

$result = $conn->query($query);
$mascotas = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $mascotas[] = $row;
  }
}
?>