<?php
session_start();
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

    // --- LÓGICA DE SUBIDA DE FOTO ---
    $ruta_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $carpeta_destino = '../uploads/';

        // Crear carpeta si no existe
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = time() . "_" . uniqid() . "." . $extension;
        $ruta_temporal = $_FILES['foto']['tmp_name'];
        $ruta_final = $carpeta_destino . $nombre_archivo;

        if (move_uploaded_file($ruta_temporal, $ruta_final)) {
            $ruta_foto = '../uploads/' . $nombre_archivo;
        }
    }

    // --- VALIDACIÓN DE FECHA ---
    if ($fecha_nacimiento) {
        $hoy = date('Y-m-d');
        if ($fecha_nacimiento > $hoy) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../vistaCliente/mis-mascotas.php';
            $urlBase = strtok($referer, '?');
            header('Location: ' . $urlBase . '?error=fecha');
            exit();
        }
    }

    // --- INSERT EN BD ---
    // Asegúrate de que la columna 'foto' existe en tu tabla 'mascotas'
    $sql = "INSERT INTO mascotas (nombre, id_cliente, raza, fecha_nac, fecha_mue, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissss", $nombre_mascota, $id_cliente, $raza, $fecha_nacimiento, $fecha_muerte, $ruta_foto);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../vistaCliente/mis-mascotas.php';
        $urlBase = strtok($referer, '?');
        header('Location: ' . $urlBase);
        exit();
    } else {
        die("Error al registrar: " . $stmt->error);
    }
}
?>