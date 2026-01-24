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
  } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
    $error = "La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.";
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="styles.css" rel="stylesheet">
  <style>
    /* Estilos para los inputs con iconos integrados */
    .input-group-text {
      background-color: #fff;
      border-right: 0;
    }

    .input-with-icon {
      border-left: 0;
    }

    .input-group-append .input-group-text {
      border-left: 0;
      border-right: 1px solid #ced4da;
      cursor: pointer;
    }

    /* Estilo del icono a la izquierda */
    .icon-prepend {
      color: #00897b;
      /* Color de la marca */
      width: 40px;
      justify-content: center;
      border: 1px solid #ced4da;
      border-right: 0;
    }
  </style>
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-lg">

          <div class="card-header bg-green text-center p-4">
            <h3 class="font-weight-bold text-white mb-0">Crear Cuenta</h3>
            <small class="text-white-50">Únete a nuestra comunidad</small>
          </div>

          <div class="card-body p-4 p-md-5">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger text-center shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

              <div class="form-group mb-3">
                <label for="username" class="font-weight-bold text-muted">Nombre de usuario</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control input-with-icon" id="username" name="username" required
                    placeholder="Tu nombre completo"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
              </div>

              <div class="form-group mb-3">
                <label for="email" class="font-weight-bold text-muted">Correo electrónico</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-envelope"></i></span>
                  </div>
                  <input type="email" class="form-control input-with-icon" id="email" name="email" required
                    placeholder="ejemplo@email.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>

              <div class="form-group mb-3">
                <label for="password" class="font-weight-bold text-muted">Contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" class="form-control input-with-icon border-right-0" id="password"
                    name="password" required placeholder="••••••••">
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password" data-target="password">
                      <i class="fas fa-eye text-muted"></i>
                    </span>
                  </div>
                </div>
                <small class="form-text text-muted mt-2">
                  <i class="fas fa-info-circle mr-1"></i> Mínimo 8 caracteres, una mayúscula y un número.
                </small>
              </div>

              <div class="form-group mb-4">
                <label for="confirm_password" class="font-weight-bold text-muted">Repetir Contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text icon-prepend"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" class="form-control input-with-icon border-right-0" id="confirm_password"
                    name="confirm_password" required placeholder="••••••••">
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password" data-target="confirm_password">
                      <i class="fas fa-eye text-muted"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-group mt-4">
                <button type="submit" class="btn btn-block font-weight-bold text-white py-2 shadow-sm"
                  style="background-color: #00897b; font-size: 1.1rem; border-radius: 50px;">
                  REGISTRARSE
                </button>
              </div>

              <div class="text-center mt-3">
                <p class="mb-0 text-muted">¿Ya tienes una cuenta?</p>
                <a href="iniciar-sesion.php" class="font-weight-bold" style="color: #00897b;">Iniciar Sesión</a>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once 'shared/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Script para alternar la visibilidad de la contraseña
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