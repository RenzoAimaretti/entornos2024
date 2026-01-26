<?php
// 1. Detectamos la página actual al inicio para usarla en los botones
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Definimos qué páginas pertenecen a la sección "Gestión" para resaltar el menú padre
$paginas_gestion = [
  'gestionar-hospitalizacion.php',
  'gestionar-atenciones.php',
  'gestionar-especialistas.php',
  'alta-especialista.php',
  'detalle-especialista.php',
  'gestionar-clientes.php',
  'detalle-cliente.php',
  'gestionar-mascotas.php',
  'agregar-mascota.php',
  'detalle-mascota.php',
  'detalle-atencionAP.php'
];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
  /* Estilos personalizados para la Navbar */
  .navbar-custom {
    background-color: #00897b;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 0.8rem 1rem;
  }

  .navbar-custom .navbar-brand {
    color: #fff !important;
    font-weight: 700;
    font-size: 1.3rem;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .navbar-custom .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
    /* Peso normal */
    transition: all 0.3s ease;
    margin: 0 5px;
  }

  /* ESTILO ACTIVO: Fondo semitransparente y NEGRITA */
  .navbar-custom .nav-link:hover,
  .navbar-custom .nav-link.active {
    color: #fff !important;
    background-color: rgba(255, 255, 255, 0.2);
    /* Un poco más visible */
    border-radius: 5px;
    transform: translateY(-1px);
    font-weight: 800 !important;
    /* Negrita fuerte */
  }

  /* Color específico para el dropdown de gestión cuando NO está activo */
  #adminDropdown {
    color: #b2dfdb !important;
  }

  /* Cuando gestión está activo o hover */
  #adminDropdown:hover,
  #adminDropdown.active {
    color: #fff !important;
  }

  .navbar-toggler {
    border-color: rgba(255, 255, 255, 0.5);
  }

  .navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
  }

  /* Dropdown Menus */
  .dropdown-menu {
    border: none;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin-top: 10px;
  }

  .dropdown-item {
    padding: 10px 20px;
    color: #555;
  }

  .dropdown-item:hover {
    background-color: #e0f2f1;
    color: #00897b;
  }

  /* Item del dropdown activo */
  .dropdown-item.active {
    background-color: #00897b;
    color: white;
    font-weight: bold;
  }

  /* Breadcrumb limpio */
  .breadcrumb-container {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
  }

  .breadcrumb-item a {
    color: #00897b;
    text-decoration: none;
  }

  .breadcrumb-item.active {
    color: #6c757d;
  }
</style>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center" href="../index.php">
      <img src="https://doctoravanevet.com/wp-content/uploads/2020/04/Servicios-vectores-consulta-integral.png"
        alt="Logo" class="mr-2 bg-white rounded-circle p-1" width="45" height="45">
      <span>San Antón</span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="../index.php"><i
              class="fas fa-home mr-1"></i> Inicio</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?php echo ($pagina_actual == 'profesionales.php') ? 'active' : ''; ?>"
            href="../profesionales.php">Profesionales</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?php echo ($pagina_actual == 'nosotros.php') ? 'active' : ''; ?>"
            href="../nosotros.php">Nosotros</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?php echo ($pagina_actual == 'contactanos.php') ? 'active' : ''; ?>"
            href="../contactanos.php">Contacto</a>
        </li>

        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] !== 'cliente'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle font-weight-bold <?php echo (in_array($pagina_actual, $paginas_gestion)) ? 'active' : ''; ?>"
              href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-cogs mr-1"></i> Gestión
            </a>

            <div class="dropdown-menu shadow" aria-labelledby="adminDropdown">
              <h6 class="dropdown-header text-uppercase text-muted small">Panel de Control</h6>

              <a class="dropdown-item <?php echo ($pagina_actual == 'gestionar-hospitalizacion.php') ? 'active' : ''; ?>"
                href="../vistaAdmin/gestionar-hospitalizacion.php">
                <i class="fas fa-procedures mr-2 text-info"></i> Hospitalización
              </a>

              <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                <div class="dropdown-divider"></div>

                <a class="dropdown-item <?php echo ($pagina_actual == 'gestionar-atenciones.php' || $pagina_actual == 'detalle-atencionAP.php') ? 'active' : ''; ?>"
                  href="../vistaAdmin/gestionar-atenciones.php">
                  <i class="fas fa-notes-medical mr-2 text-success"></i> Atenciones
                </a>

                <a class="dropdown-item <?php echo ($pagina_actual == 'gestionar-especialistas.php' || $pagina_actual == 'alta-especialista.php' || $pagina_actual == 'detalle-especialista.php') ? 'active' : ''; ?>"
                  href="../vistaAdmin/gestionar-especialistas.php">
                  <i class="fas fa-user-md mr-2 text-primary"></i> Profesionales
                </a>

                <a class="dropdown-item <?php echo ($pagina_actual == 'gestionar-clientes.php' || $pagina_actual == 'detalle-cliente.php') ? 'active' : ''; ?>"
                  href="../vistaAdmin/gestionar-clientes.php">
                  <i class="fas fa-users mr-2 text-warning"></i> Clientes
                </a>

                <a class="dropdown-item <?php echo ($pagina_actual == 'gestionar-mascotas.php' || $pagina_actual == 'agregar-mascota.php' || $pagina_actual == 'detalle-mascota.php') ? 'active' : ''; ?>"
                  href="../vistaAdmin/gestionar-mascotas.php">
                  <i class="fas fa-paw mr-2 text-danger"></i> Mascotas
                </a>
              <?php endif; ?>
            </div>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ml-auto">
        <?php if (isset($_SESSION['usuario_nombre'])): ?>
          <li class="nav-item dropdown d-flex align-items-center">

            <img
              src="https://static.vecteezy.com/system/resources/previews/007/296/443/non_2x/user-icon-person-icon-client-symbol-profile-icon-vector.jpg"
              alt="Usuario" class="rounded-circle mr-2 border border-white shadow-sm" width="38" height="38"
              style="object-fit: cover;">

            <a class="nav-link dropdown-toggle font-weight-bold" href="#" id="usuarioDropdown" role="button"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="usuarioDropdown">
              <div class="px-3 py-2 text-muted small border-bottom mb-2">
                Conectado como <strong><?php echo ucfirst($_SESSION['usuario_tipo']); ?></strong>
              </div>

              <?php if ($_SESSION['usuario_tipo'] === 'cliente'): ?>
                <a class="dropdown-item <?php echo ($pagina_actual == 'mis-turnos.php') ? 'active' : ''; ?>"
                  href="../vistaCliente/mis-turnos.php">
                  <i class="fas fa-calendar-check mr-2 text-primary"></i> Mis Turnos
                </a>
                <a class="dropdown-item <?php echo ($pagina_actual == 'mis-mascotas.php') ? 'active' : ''; ?>"
                  href="../vistaCliente/mis-mascotas.php">
                  <i class="fas fa-dog mr-2 text-warning"></i> Mis Mascotas
                </a>
                <div class="dropdown-divider"></div>
              <?php endif; ?>

              <a class="dropdown-item text-danger" href="../logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
              </a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'iniciar-sesion.php') ? 'active' : ''; ?>"
              href="../iniciar-sesion.php"><i class="fas fa-sign-in-alt mr-1"></i> Iniciar sesión</a>
          </li>
          <li class="nav-item ml-2">
            <a class="btn btn-light text-teal font-weight-bold shadow-sm rounded-pill px-4"
              href="../registrarse.php">Registrarse</a>
          </li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<div class="breadcrumb-container">
  <div class="container py-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 p-0" style="background: transparent; font-size: 0.9rem;">
        <?php
        // Icono de casa para el inicio
        echo '<li class="breadcrumb-item"><a href="../index.php"><i class="fas fa-home"></i> Inicio</a></li>';

        if ($pagina_actual !== 'index.php') {
          $nombres_paginas = [
            'mis-turnos.php' => 'Mis Turnos',
            'mis-mascotas.php' => 'Mis Mascotas',
            'profesionales.php' => 'Nuestros Profesionales',
            'nosotros.php' => 'Sobre Nosotros',
            'contactanos.php' => 'Contacto',
            'iniciar-sesion.php' => 'Acceso de Usuarios',
            'registrarse.php' => 'Crear Cuenta',
            'panel-profesional.php' => 'Panel Profesional',
            'autogestion-turnos.php' => 'Autogestión',
            'solicitar-turno.php' => 'Solicitar Turno',
            'solicitar-turno-profesional.php' => 'Turno por Profesional',
            'solicitar-turno-servicio.php' => 'Turno por Servicio',
            'detalle-mascota.php' => 'Detalle de Mascota',
            'detalle-atencionAP.php' => 'Detalle de Atención',
            'gestionar-hospitalizacion.php' => 'Administración de Internaciones',
            'gestionar-clientes.php' => 'Gestión de Clientes',
            'detalle-cliente.php' => 'Perfil de Cliente',
            'agregar-mascota.php' => 'Alta de Mascota',
            'gestionar-mascotas.php' => 'Gestión de Mascotas',
            'gestionar-atenciones.php' => 'Gestión de Atenciones',
            'gestionar-especialistas.php' => 'Gestión de Especialistas',
            'alta-especialista.php' => 'Alta de Especialista',
            'detalle-especialista.php' => 'Perfil de Especialista'
          ];

          $label = $nombres_paginas[$pagina_actual] ?? ucfirst(str_replace(['-', '.php'], [' ', ''], $pagina_actual));
          echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($label) . '</li>';
        }
        ?>
      </ol>
    </nav>
  </div>
</div>