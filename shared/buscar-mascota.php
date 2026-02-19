<?php
require_once 'db.php';

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];

if (!empty($q)) {
    $sql = "SELECT id, nombre FROM mascotas WHERE nombre LIKE CONCAT('%', ?, '%') and fecha_mue IS NULL LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $resultados[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($resultados);
$conn->close();
?>