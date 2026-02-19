<?php
require_once 'db.php';

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$especialistaId = isset($_GET['especialista_id']) ? (int) $_GET['especialista_id'] : 0;
$resultados = [];

if ($especialistaId > 0) {

    $sql = "SELECT s.id, s.nombre 
            FROM servicios s
            INNER JOIN profesionales p ON s.id_esp = p.id_esp
            WHERE p.id = ?";

    if (!empty($q)) {
        $sql .= " AND s.nombre LIKE CONCAT('%', ?, '%')";
    }
    $sql .= " ORDER BY s.nombre";

    $stmt = $conn->prepare($sql);
    if (!empty($q)) {
        $stmt->bind_param("is", $especialistaId, $q);
    } else {
        $stmt->bind_param("i", $especialistaId);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $resultados[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($resultados);
?>