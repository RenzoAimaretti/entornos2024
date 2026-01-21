<?php
session_start();
require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se recibió el ID por POST (enviado desde el modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Consulta preparada para eliminar la atención
    $query = "DELETE FROM atenciones WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir a la vista de gestión con aviso de éxito
        header("Location: ../vistaAdmin/gestionar-atenciones.php?res=eliminado");
        exit();
    } else {
        echo "Error al eliminar la atención: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Si se accede directamente o sin ID, volver al calendario
    header("Location: ../vistaAdmin/gestionar-atenciones.php");
    exit();
}

$conn->close();
?>