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
  $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
  $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : null;

  if ($email && $password) {
    $sql = "SELECT id, nombre, tipo, password FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      if ($password === $row['password']) {
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nombre'] = $row['nombre'];
        $_SESSION['usuario_tipo'] = $row['tipo'];

        header("Location: index.php");
        exit();
      } else {
        $error = "Contraseña incorrecta.";
      }
    } else {
      $error = "Usuario no encontrado.";
    }

    $stmt->close();
  } else {
    $error = "Por favor, complete todos los campos.";
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Iniciar Sesión</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-lg">

          <div class="card-header bg-green text-center p-4">
            <h3 class="font-weight-bold text-white mb-0">¡Hola de nuevo!</h3>
            <small class="text-white-50">Ingresa tus datos para continuar</small>
          </div>

          <div class="card-body p-5">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger text-center" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

              <div class="form-group mb-4">
                <label for="email" class="font-weight-bold" style="color: #00897b;">Correo electrónico</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-envelope"></i></span>
                  </div>
                  <input type="email" class="form-control input-with-icon" id="email" name="email"
                    placeholder="ejemplo@email.com" required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>

              <div class="form-group mb-4">
                <label for="password" class="font-weight-bold" style="color: #00897b;">Contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" class="form-control border-left-0 border-right-0" id="password" name="password"
                    placeholder="••••••••" required>
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password bg-white" data-target="password">
                      <i class="fas fa-eye text-muted"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-group mt-4">
                <button type="submit" class="btn btn-block font-weight-bold text-white py-2"
                  style="background-color: #00897b; font-size: 1.1rem; border-radius: 50px;">
                  INGRESAR
                </button>
              </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
              <p class="text-muted mb-2">¿Aún no tienes cuenta?</p>
              <a href="registrarse.php" class="btn btn-outline-secondary btn-sm px-4" style="border-radius: 50px;">
                Crear cuenta nueva
              </a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once 'shared/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.querySelectorAll('.toggle-password').forEach(item => {
      item.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');

        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });
    });
  </script>
</body>

</html>