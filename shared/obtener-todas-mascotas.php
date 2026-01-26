<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

$stmt = $conn->prepare("SELECT id, nombre FROM mascotas ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();
$mascotas = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($mascotas);

$conn->close();
?>