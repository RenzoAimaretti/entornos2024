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

if (isset($_POST['id'])) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        die("Usuario no encontrado.");
    }
    $stmt->close();
} else {
    die("ID de usuario no proporcionado.");

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especialidad = $_POST['especialidad'];
    $telefono = $_POST['telefono'];

    // Validar los datos
    if (!empty($especialidad) && !empty($telefono)) {
        $sql = "UPDATE profesionales SET id_esp = ?, telefono = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $especialidad, $telefono, $_POST['id']);

        if ($stmt->execute()) {
            // Redirigir a la página anterior
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error al actualizar el usuario: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Por favor, complete todos los campos correctamente.";
    }
}
$conn->close();

?>