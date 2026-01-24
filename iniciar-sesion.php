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
  <style>
    /* Estilo para el botón del ojo */
    .input-group-text {
      cursor: pointer;
      background-color: #fff;
    }
  </style>
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card bg-light mb-3">
          <div class="card-header text-center">Iniciar sesión</div>
          <div class="card-body">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico"
                  required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>

              <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña"
                    required>
                  <div class="input-group-append">
                    <span class="input-group-text toggle-password" data-target="password">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-secondary">Confirmar</button>
                <button type="button" class="btn btn-primary" onclick="location.href='registrarse.php'">
                  Registrarse
                </button>
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