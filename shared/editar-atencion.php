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
    $fecha = $_POST['fecha'];
    $detalle = $_POST['detalle'];

    if (!$id || !$fecha || !$detalle) {
        die("Todos los campos son obligatorios.");
    }


    $query = "UPDATE atenciones SET fecha = ?, detalle = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $fecha, $detalle, $id);

    if ($stmt->execute()) {
        header("Location: detalle-atencionAP.php?id=$id");
        exit();
    } else {
        echo "Error al actualizar la atención: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>