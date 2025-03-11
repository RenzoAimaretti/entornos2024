<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinaria";

// Crear conexión
$conn = new mysqli("localhost", "root", "marcoruben9", "veterinaria");

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
$sql = "INSERT INTO usuarios (nombre, email, password,tipo) VALUES (?, ?, ?, 'cliente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $email, $password);

if ($stmt->execute()) {
  $_SESSION['usuario_id'] = $stmt->insert_id;
  $_SESSION['usuario_nombre'] = $nombre;
  $_SESSION['usuario_tipo'] = 'cliente';
  header("Location: index.php");
  exit();
} else {
  echo "Error al registrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>