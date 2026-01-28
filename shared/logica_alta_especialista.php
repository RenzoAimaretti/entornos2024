<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if (isset($_POST['check_email'])) {
  $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
  $stmt->bind_param("s", $_POST['email']);
  $stmt->execute();
  $stmt->store_result();
  echo json_encode(['exists' => $stmt->num_rows > 0]);
  exit();
}

$especialidades = $conn->query("SELECT id, nombre FROM especialidad")->fetch_all(MYSQLI_ASSOC);
?>