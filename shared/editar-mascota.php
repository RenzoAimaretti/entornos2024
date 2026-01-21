<?php
session_start();
// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se recibió el ID de la mascota
if (isset($_POST['idMascota'])) {
    $idMascota = $_POST['idMascota'];
} else {
    die("ID de mascota no proporcionado.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_mascota = $_POST['nombre'];
    $raza = isset($_POST['raza']) && !empty($_POST['raza']) ? $_POST['raza'] : null;


    $fecha_nacimiento = isset($_POST['fecha_nac']) && !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;


    $fecha_nacimiento = isset($_POST['fecha_nac']) && !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $fecha_muerte = isset($_POST['fecha_mue']) && !empty($_POST['fecha_mue']) ? $_POST['fecha_mue'] : null;

    // Validar los datos
    if (!empty($nombre_mascota)) {

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
    } else {
        echo "Por favor, complete todos los campos correctamente.";
    }
}

$conn->close();
?>