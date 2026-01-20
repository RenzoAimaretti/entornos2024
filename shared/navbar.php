<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="../index.php">
      <img src="https://doctoravanevet.com/wp-content/uploads/2020/04/Servicios-vectores-consulta-integral.png"
        alt="Logo" class="logo" width="50" height="50">
      <span>Veterinaria San Antón</span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link active" href="../index.php">Inicio</a>
        </li>
        <?php if (isset($_SESSION['usuario_nombre'])): ?>
          <li class="nav-item dropdown d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Usuario" width="40" height="40"
              class="mr-2">
            <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              <?php echo $_SESSION['usuario_nombre']; ?>
            </a>
            <!-- hay que meter todos estos en una carpeta y meter validaciones de tipo de usuario -->
            <div class="dropdown-menu" aria-labelledby="usuarioDropdown">
              <?php if ($_SESSION['usuario_tipo'] === 'cliente'): ?>
                <a class="dropdown-item" href="../vistaCliente/mis-turnos.php">Mis Turnos</a>
                <a class="dropdown-item" href="../vistaCliente/mis-mascotas.php">Mis Mascotas</a>
              <?php endif; ?>
              <a class="dropdown-item" href="../logout.php">Cerrar sesión</a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="iniciar-sesion.php">Iniciar sesión</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="registrarse.php">Registrarse</a>
          </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            Secciones
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="../profesionales.php">Profesionales</a>
            <a class="dropdown-item" href="../nosotros.php">Nosotros</a>
            <a class="dropdown-item" href="../contactanos.php">Contacto</a>
            <?php
            if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
              <a class="dropdown-item" href="../vistaAdmin/gestionar-especialistas.php">Gestionar Profesionales</a>
              <a class="dropdown-item" href="../vistaAdmin/gestionar-clientes.php">Gestionar Clientes</a>
              <a class="dropdown-item" href="../vistaAdmin/gestionar-mascotas.php">Gestionar Mascotas</a>
              <a class="dropdown-item" href="../vistaAdmin/gestionar-atenciones.php">Gestionar Atenciones</a>

            <?php endif; ?>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>