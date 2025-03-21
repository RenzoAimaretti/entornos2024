<?php
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
if (isset($_GET['idMascota'])) {
    $idMascota = $_GET['idMascota'];

    // Obtener los datos actuales de la mascota
    $sql = "SELECT * FROM mascotas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idMascota);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mascota = $result->fetch_assoc();
    } else {
        die("Mascota no encontrada.");
    }
    $stmt->close();
} else {
    die("ID de mascota no proporcionado.");
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_mascota = $_POST['nombre'];
    $raza = isset($_POST['raza']) && !empty($_POST['raza']) ? $_POST['raza'] : null;
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) && !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $fecha_muerte = isset($_POST['fecha_muerte']) && !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;

    // Validar los datos
    if (!empty($nombre_mascota)) {
        $sql = "UPDATE mascotas SET nombre = ?, raza = ?, fecha_nac = ?, fecha_mue = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nombre_mascota, $raza, $fecha_nacimiento, $fecha_muerte, $idMascota);

        if ($stmt->execute()) {
            echo "Mascota actualizada correctamente.";
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