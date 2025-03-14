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
$email = $_POST['email'];
$password = $_POST['password'];

/*
// Buscar usuario en la base de datos
$sql = "SELECT * FROM usuarios WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  if (password_verify($password, $row['password'])) {
    $_SESSION['usuario'] = $row['nombre'];
    echo "Bienvenido, " . $_SESSION['usuario'] . "! <a href='index.html'>Ir al inicio</a>";
  } else {
    echo "Contraseña incorrecta. Intentelo nuevamente. <a href='iniciar-sesion.html'>Iniciar sesión</a>";
  }
} else {
  echo "Usuario no encontrado.";
}

$conn->close();
?> */

// Buscar usuario en la base de datos con consulta preparada
$sql = "SELECT id, nombre, tipo, password FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();

  if ($password === $row['password']) {
    // Guardar en la sesión
    $_SESSION['usuario_id'] = $row['id'];
    $_SESSION['usuario_nombre'] = $row['nombre'];
    $_SESSION['usuario_tipo'] = $row['tipo'];

    header("Location: index.php");
    exit();
  } else {
    echo "Contraseña incorrecta. <a href='iniciar-sesion.php'>Intentar de nuevo</a>";
  }
} else {
  echo "Usuario no encontrado. <a href='registrarse.php'>Registrarse</a>";
}


$stmt->close();
$conn->close();
?>