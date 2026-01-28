<?php
session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
  die("Acceso denegado");
}

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
          FROM usuarios u 
          INNER JOIN profesionales p ON u.id = p.id
          INNER JOIN especialidad e on p.id_esp = e.id
          ORDER BY u.nombre ASC";

$result = $conn->query($query);
?>