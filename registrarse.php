<?php
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Validaciones
  if ($password !== $confirm_password) {
    $error = "Las contraseñas no coinciden.";
  } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
    $error = "La contraseña debe contener al menos una letra mayúscula y un número.";
  } else {
    // Verificar si el correo ya está registrado
    $sql_check = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "El correo ya está registrado.";
    } else {
      // Insertar usuario en la base de datos
      $sql = "INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, 'cliente')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sss", $nombre, $email, $password);

      if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;
        $sql2 = "INSERT INTO clientes (id) VALUES (?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $usuario_id);
        $stmt2->execute();

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_tipo'] = 'cliente';

        header("Location: index.php");
        exit();
      } else {
        $error = "Error al registrar usuario: " . $stmt->error;
      }
    }
  }
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
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card bg-light mb-3">
          <div class="card-header text-center">Registrarse</div>
          <div class="card-body">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger">
                <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input type="text" class="form-control" id="username" name="username" required>
              </div>
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="text-muted">Debe contener al menos una letra mayúscula y un número.</small>
              </div>
              <div class="form-group">
                <label for="confirm_password">Repita la Contraseña</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
              </div>
              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Confirmar</button>
                <a href="iniciar-sesion.php" class="btn btn-secondary">Ya tengo una cuenta</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <footer class="bg-light py-4">
    <div class="container text-center">
      <p>Teléfono de contacto: 115673346</p>
      <p>Mail: sananton24@gmail.com</p>
    </div>
  </footer>
</body>

</html>