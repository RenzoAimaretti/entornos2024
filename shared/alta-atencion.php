<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pro = intval($_POST['especialista_id']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $id_mascota = intval($_POST['mascota_id']);
    $id_serv = intval($_POST['servicio_id']);
    $fecha_hora = $fecha . ' ' . $hora . ':00';

    $check = $conn->prepare("SELECT id FROM atenciones WHERE id_pro = ? AND fecha = ?");
    $check->bind_param("is", $id_pro, $fecha_hora);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        header("Location: ../vistaAdmin/gestionar-atenciones.php?error=especialista_ocupado");
        exit;
    }

    $check_mascota = $conn->prepare("SELECT id FROM atenciones WHERE id_mascota = ? AND fecha = ?");
    $check_mascota->bind_param("is", $id_mascota, $fecha_hora);
    $check_mascota->execute();

    if ($check_mascota->get_result()->num_rows > 0) {
        header("Location: ../vistaAdmin/gestionar-atenciones.php?error=mascota_ocupada");
        exit;
    }

    $detalle = "";

    $stmt = $conn->prepare("INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, ?)");

    $stmt->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_hora, $detalle);

    if ($stmt->execute()) {
        header("Location: ../vistaAdmin/gestionar-atenciones.php?res=ok");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>