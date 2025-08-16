<?php
session_start();
require '../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); // Cambiar la ruta al directorio raíz
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    die("Error: No se pudo cargar el archivo .env. Verifica su existencia.");
}

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verifica si los datos fueron enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $nombre_mascota = $_POST['nombre'];
    $raza = isset($_POST['raza']) && !empty($_POST['raza']) ? $_POST['raza'] : null;
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) && !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $fecha_muerte = isset($_POST['fecha_muerte']) && !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;

    // Insertar en la base de datos
    $sql = "INSERT INTO mascotas (nombre, id_cliente, raza, fecha_nac, fecha_mue) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisss", $nombre_mascota, $id_cliente, $raza, $fecha_nacimiento, $fecha_muerte);

    if (!$stmt->execute()) {
        die("Error al registrar mascota: " . $stmt->error);
    }

    echo "Mascota registrada con éxito: $nombre_mascota";
    $stmt->close();
    $conn->close();

    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'mis-mascotas.php';
    header('Location: ' . $referer);
    exit();
}

?>