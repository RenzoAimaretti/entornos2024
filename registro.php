<?php
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Recibir datos del formulario
$nombre = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validaciones
if ($password !== $confirm_password) {
  die("Error: Las contraseñas no coinciden. <a href='registrarse.html'>Volver</a>");
}

if (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
  die("Error: La contraseña debe contener al menos una letra mayúscula y un número. <a href='registrarse.html'>Volver</a>");
}

// Verificar si el correo ya está registrado
$sql_check = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Error: El correo ya está registrado. <a href='registrarse.html'>Volver</a>");
}

// Encriptar la contraseña
// $password_hashed = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario en la base de datos
$sql = "INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, 'cliente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $email, $password);

if (!$stmt->execute()) {
    die("Error al registrar usuario: " . $stmt->error);
}

$usuario_id = $stmt->insert_id; // Obtener el ID del usuario recién insertado

// Insertar en la tabla clientes
$sql2 = "INSERT INTO clientes (id) VALUES (?)";
$stmt = $conn->prepare($sql2);
$stmt->bind_param("i", $usuario_id);

if (!$stmt->execute()) {
    die("Error al registrar cliente: " . $stmt->error);
}

// Guardar datos en la sesión
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_tipo'] = 'cliente';

// Redirigir al usuario
header("Location: index.php");
exit();

$stmt->close();
$conn->close();
?>