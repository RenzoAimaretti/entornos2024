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
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.html">
        <img src="https://doctoravanevet.com/wp-content/uploads/2020/04/Servicios-vectores-consulta-integral.png"
          alt="Logo" class="logo">
        <span>Veterinaria San Antón</span>
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="iniciar-sesion.php">Iniciar sesión</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="registrarse.php">Registrarse</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              Secciones
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item" href="contactanos.php">Contacto</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Barra de Navegación Secundaria -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
      <li class="breadcrumb-item"><a href="iniciar-sesion.php">Iniciar sesión</a></li>
      <li class="breadcrumb-item active" aria-current="page">Registrarse</li>
    </ol>
  </nav>

  <!-- Formulario de Registro -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card bg-light mb-3">
          <div class="card-header text-center">Registrarse</div>
          <div class="card-body">
            <form action="registro.php" method="post">
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