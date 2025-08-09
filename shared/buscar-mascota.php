<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];

if (!empty($q)) {
    $sql = "SELECT id, nombre FROM mascotas WHERE nombre LIKE CONCAT('%', ?, '%') LIMIT 10";
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