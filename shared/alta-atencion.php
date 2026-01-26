<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    if ($_SESSION['usuario_tipo'] === 'especialista') {
        $id_pro = $_SESSION['usuario_id'];
    } else {
        $id_pro = isset($_POST['especialista_id']) ? intval($_POST['especialista_id']) : null;
    }

    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $id_mascota = isset($_POST['mascota_id']) ? intval($_POST['mascota_id']) : 0;
    $id_serv = isset($_POST['servicio_id']) ? intval($_POST['servicio_id']) : null;
    $detalle = $_POST['detalle'] ?? '';

   
    $fecha_hora = $fecha . ' ' . $hora . ':00';

    

    
    $checkMascota = $conn->prepare("SELECT id FROM atenciones WHERE id_mascota = ? AND fecha = ?");
    $checkMascota->bind_param("is", $id_mascota, $fecha_hora);
    $checkMascota->execute();
    if ($checkMascota->get_result()->num_rows > 0) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "error=mascota_ocupada");
        exit;
    }

    
    $checkPro = $conn->prepare("SELECT id FROM atenciones WHERE id_pro = ? AND fecha = ?");
    $checkPro->bind_param("is", $id_pro, $fecha_hora);
    $checkPro->execute();
    if ($checkPro->get_result()->num_rows > 0) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "error=especialista_ocupado");
        exit;
    }

   

    
    $stmt = $conn->prepare("
        INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_hora, $detalle);

    if ($stmt->execute()) {
        
        header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "res=ok");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}




