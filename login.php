<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinaria";

// Crear conexión
$conn = new mysqli("localhost", "root", "laclavedeustedes", "veterinaria");

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
$sql = "SELECT id, nombre, password FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();

  if (password_verify($password, $row['password'])) {
    $_SESSION['usuario_id'] = $row['id'];
    $_SESSION['usuario_nombre'] = $row['nombre'];
    header("Location: index.php");
    exit();

  } else {
    echo "Contraseña incorrecta. <a href='iniciar-sesion.html'>Intentar de nuevo</a>";
  }
} else {
  echo "Usuario no encontrado. <a href='registrarse.html'>Registrarse</a>";
}


$stmt->close();
$conn->close();
?>
