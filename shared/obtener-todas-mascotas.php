<?php
session_start();
require_once 'db.php';

$stmt = $conn->prepare("SELECT id, nombre FROM mascotas ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();
$mascotas = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($mascotas);

$conn->close();
?>