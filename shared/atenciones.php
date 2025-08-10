<?php
session_start();

require '../vendor/autoload.php';

// Verifica permisos: solo admin o profesional pueden entrar
if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    die("Acceso denegado");
}

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Construir query base
$query = "SELECT a.id, 
                 m.nombre AS title, 
                 fecha AS start
          FROM atenciones a
          INNER JOIN mascotas m ON a.id_mascota = m.id
          INNER JOIN servicios s ON a.id_serv = s.id
          INNER JOIN profesionales p ON a.id_pro = p.id
          INNER JOIN usuarios u ON p.id = u.id";
// Si es profesional, filtrar por su ID
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
?>
