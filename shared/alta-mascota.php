<?php
session_start();
// Muy importante: misma zona horaria que en la vista
date_default_timezone_set('America/Argentina/Buenos_Aires');

require '../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    die("Error: No se pudo cargar el archivo .env");
}

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $nombre_mascota = $_POST['nombre'];
    $raza = !empty($_POST['raza']) ? $_POST['raza'] : null;
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $fecha_muerte = !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;

    // --- VALIDACIÓN REFORZADA ---
    if ($fecha_nacimiento) {
        $hoy = date('Y-m-d');

        // Comparamos: si la fecha enviada es mayor a hoy, rebota.
        if ($fecha_nacimiento > $hoy) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../vistaCliente/mis-mascotas.php';
            // Limpiamos la URL de referer por si ya tenía otros errores previos
            $urlBase = strtok($referer, '?');
            header('Location: ' . $urlBase . '?error=fecha');
            exit();
        }
    }

    $sql = "INSERT INTO mascotas (nombre, id_cliente, raza, fecha_nac, fecha_mue) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisss", $nombre_mascota, $id_cliente, $raza, $fecha_nacimiento, $fecha_muerte);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        // Redirección al éxito
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../vistaCliente/mis-mascotas.php';
        $urlBase = strtok($referer, '?');
        header('Location: ' . $urlBase);
        exit();
    } else {
        die("Error al registrar: " . $stmt->error);
    }
}
?>