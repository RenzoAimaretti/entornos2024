<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $id_mascota = $_POST['mascota_id'] ?? '';
    $detalle = $_POST['detalle'] ?? 'Atención pendiente de actualizacion por el Especialista';
    $id_serv = $_POST['servicio_id'] ?? null; 
    $id_pro = $_POST['especialista_id'] ?? null; 

    echo "<script>console.log(" . json_encode([
        'fecha' => $fecha,
        'hora' => $hora,
        'id_mascota' => $id_mascota,
        'detalle' => $detalle,
        'id_serv' => $id_serv,
        'id_pro' => $id_pro
    ]) . ");</script>";

    $fecha_hora = $fecha . ' ' . $hora . ':00';

    // Insertar en atenciones
    $stmt = $conn->prepare("
        INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_hora, $detalle);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        echo "Error al registrar: " . $conn->error;
    }
}
?>
