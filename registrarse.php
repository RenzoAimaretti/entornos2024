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
    // MODIFICACIÓN: Se agregó la validación de longitud (min 8)
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
    /* Estilo para que el botón del ojo se integre bien */
    .input-group-text {
      cursor: pointer;
      background-color: #fff;
    }
  </style>
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container mt-5">
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
                <input type="text" class="form-control" id="username" name="username" required
                  value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
              </div>
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required
                  value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>

              <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password" required>
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password" data-target="password">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
                <small class="text-muted">Mínimo 8 caracteres, al menos una mayúscula y un número.</small>
              </div>

              <div class="form-group">
                <label for="confirm_password">Repita la Contraseña</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password" data-target="confirm_password">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">Confirmar</button>
                <a href="iniciar-sesion.php" class="btn btn-secondary">Ya tengo una cuenta</a>
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
        // Obtener el ID del input asociado
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');

        // Alternar tipo de input y clase del icono
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