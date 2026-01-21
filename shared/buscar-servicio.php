<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$especialistaId = isset($_GET['especialista_id']) ? (int)$_GET['especialista_id'] : 0;
$resultados = [];

if (!empty($q) && $especialistaId > 0) {
    // Consulta para buscar servicios según la especialidad del especialista
    $sql = "SELECT s.id, s.nombre 
            FROM servicios s
            INNER JOIN profesionales p ON s.id_esp = p.id_esp
            WHERE s.nombre LIKE CONCAT('%', ?, '%')
            AND p.id = ?
            LIMIT 10";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $q, $especialistaId);
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