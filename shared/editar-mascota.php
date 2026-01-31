<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['idMascota'])) {
        $idMascota = intval($_POST['idMascota']);
    } else {
        die("ID de mascota no proporcionado.");
    }

    $nombre_mascota = $_POST['nombre'];
    $raza = !empty($_POST['raza']) ? $_POST['raza'] : null;
    $fecha_nacimiento = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $fecha_muerte = !empty($_POST['fecha_mue']) ? $_POST['fecha_mue'] : null;
    $hoy = date('Y-m-d');

    if (empty($nombre_mascota)) {
        die("El nombre es obligatorio.");
    }

    if ($fecha_nacimiento && $fecha_nacimiento > $hoy) {
        die("Error: La fecha de nacimiento no puede ser futura.");
    }

    if ($fecha_muerte) {
        if ($fecha_muerte > $hoy) {
            die("Error: La fecha de muerte no puede ser futura.");
        }
        if ($fecha_nacimiento && $fecha_muerte < $fecha_nacimiento) {
            die("Error: La fecha de muerte no puede ser anterior al nacimiento.");
        }
    }

    $sql = "UPDATE mascotas SET nombre = ?, raza = ?, fecha_nac = ?, fecha_mue = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre_mascota, $raza, $fecha_nacimiento, $fecha_muerte, $idMascota);

    if ($stmt->execute()) {
        header("Location: detalle-mascota.php?idMascota=" . $idMascota . "&res=ok");
        exit();
    } else {
        echo "Error al actualizar la mascota: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>