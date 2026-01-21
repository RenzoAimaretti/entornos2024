<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pro = intval($_POST['especialista_id']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $id_mascota = intval($_POST['mascota_id']);
    $id_serv = intval($_POST['servicio_id']);
    $fecha_hora = $fecha . ' ' . $hora . ':00';

    // Validación de seguridad (Especialista ocupado)
    $check = $conn->prepare("SELECT id FROM atenciones WHERE id_pro = ? AND fecha = ?");
    $check->bind_param("is", $id_pro, $fecha_hora);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        header("Location: ../vistaAdmin/gestionar-atenciones.php?error=especialista_ocupado");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, 'Atención programada')");
    $stmt->bind_param("iiis", $id_mascota, $id_serv, $id_pro, $fecha_hora);

    if ($stmt->execute()) {
        header("Location: ../vistaAdmin/gestionar-atenciones.php?res=ok");
    } else {
        echo "Error: " . $conn->error;
    }
}