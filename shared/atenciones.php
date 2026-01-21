<?php
session_start();
require '../vendor/autoload.php';

// 1. Verifica permisos: solo admin o especialista pueden ver los eventos
if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    header('Content-Type: application/json');
    echo json_encode([]); // Devolver array vacío si no hay permiso
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión");
}

// 2. Construir query base
// Usamos CONCAT para que en el calendario se vea "Nombre Mascota - Nombre Servicio"
$query = "SELECT a.id, 
                 CONCAT(m.nombre, ' - ', s.nombre) AS title, 
                 a.fecha AS start
          FROM atenciones a
          INNER JOIN mascotas m ON a.id_mascota = m.id
          INNER JOIN servicios s ON a.id_serv = s.id";

// 3. Si el usuario es especialista, filtramos para que solo vea SUS turnos
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

// 4. IMPORTANTE: Definir el header como JSON para que FullCalendar lo entienda
header('Content-Type: application/json');
echo json_encode($eventos);
$conn->close();
?>