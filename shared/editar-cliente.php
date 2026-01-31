<?php
session_start();
require_once 'db.php';

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