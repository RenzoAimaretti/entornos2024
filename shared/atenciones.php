<?php
session_start();
require '../vendor/autoload.php';

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión");
}

$query = "SELECT a.id, 
                 CONCAT(m.nombre, ' - ', s.nombre) AS title, 
                 a.fecha AS start
          FROM atenciones a
          INNER JOIN mascotas m ON a.id_mascota = m.id
          INNER JOIN servicios s ON a.id_serv = s.id";

if ($_SESSION['usuario_tipo'] === 'especialista') {
    $idProfesional = intval($_SESSION['usuario_id']);
    $query .= " WHERE a.id_pro = $idProfesional";
}

$result = $conn->query($query);

$eventos = [];
if ($result && $result->num_rows > 0) {
    while ($fila = $result->fetch_assoc()) {
        $eventos[] = $fila;
    }
}

header('Content-Type: application/json');
echo json_encode($eventos);
$conn->close();
?>