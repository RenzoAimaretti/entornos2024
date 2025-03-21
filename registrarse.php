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
//Verificar si el formulario fue enviado
if($_SERVER['REQUEST_METHOD']==='POST'){
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


}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">


<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Registrarse</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
 <!-- Navegación -->
 <?php require_once 'shared/navbar.php'; ?>

  <!-- Formulario de Registro -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card bg-light mb-3">
          <div class="card-header text-center">Registrarse</div>
          <div class="card-body">
            <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">
              <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de usuario"
                  required>
              </div>
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico"
                  required>
              </div>
              <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required
                  oninput="verificarPassword()">
                <small class="text-muted">Debe contener al menos una letra mayúscula y un número.</small>
                <ul id="password-requisitos" class="text-danger" style="display: none;">
                  <li id="mayuscula">Debe contener al menos una letra mayúscula</li>
                  <li id="numero">Debe contener al menos un número</li>
                </ul>
              </div>
              <div class="form-group">
                <label for="confirm_password">Repita la Contraseña</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                  oninput="verificarPassword()">
                <small id="error-contraseña" class="text-danger" style="display: none;">Las contraseñas no
                  coinciden</small>
              </div>
              <button type="submit" class="btn btn-primary">Confirmar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <!-- Pie de página -->
  <footer class="bg-light py-4">
    <div class="container text-center">
      <p>Teléfono de contacto: 115673346</p>
      <p>Mail: sananton24@gmail.com</p>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>