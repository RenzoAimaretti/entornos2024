<?php
session_start();

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$usuarioId = $_SESSION['usuario_id'];
$usuarioTipo = $_SESSION['usuario_tipo']; // 'admin' o 'especialista'


if ($usuarioTipo === 'especialista') {
    // Especialista: solo sus turnos
    $sql = "SELECT a.id, a.fecha, a.detalle, m.nombre AS mascota
            FROM atenciones a
            INNER JOIN mascotas m ON a.id_mascota = m.id
            WHERE a.id_pro = ?
            ORDER BY a.fecha ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
} else {
    // Administrador: todos los turnos
    $sql = "SELECT a.id, a.fecha, a.detalle, m.nombre AS mascota
            FROM atenciones a
            INNER JOIN mascotas m ON a.id_mascota = m.id
            ORDER BY a.fecha ASC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$eventos = [];
while ($row = $result->fetch_assoc()) {
    $eventos[] = [
        'id' => $row['id'],
        'title' => $row['mascota'] . ' - ' . $row['detalle'],
        'start' => $row['fecha']
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($eventos);
?>