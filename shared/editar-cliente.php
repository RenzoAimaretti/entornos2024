<?php
session_start();
require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se recibieron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';



    $query = "UPDATE clientes SET direccion = ?, telefono = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $direccion, $telefono, $id);

    if ($stmt->execute()) {
        header("Location: ../vistaAdmin/detalle-cliente.php?id=$id");
        exit();
    } else {
        echo "Error al actualizar el cliente: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>