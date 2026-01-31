<footer class="footer-custom mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6 mb-4">
        <h5>Veterinaria San Antón</h5>
        <p class="small mb-1">📞 Teléfono: 115673346</p>
        <p class="small mb-1">✉️ Mail: sanantonn24@gmail.com</p>
      </div>

      <div class="col-md-6 mb-4">
        <h5>Mapa del Sitio</h5>
        <div class="row">
          <div class="col-6">
            <ul class="list-unstyled small">
              <li><a href="../index.php">Inicio</a></li>
              <li><a href="../nosotros.php">Nosotros</a></li>
              <li><a href="../profesionales.php">Profesionales</a></li>
              <li><a href="../contactanos.php">Contacto</a></li>
            </ul>
          </div>
          <div class="col-6">
            <ul class="list-unstyled small">

              <?php
              if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'cliente'):
                ?>
                <li><a href="../vistaCliente/mis-mascotas.php">Mis Mascotas</a></li>
                <li><a href="../vistaCliente/mis-turnos.php">Mis Turnos</a></li>
              <?php endif; ?>

              <?php
              if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'):
                ?>
                <li><a href="../vistaAdmin/gestionar-hospitalizacion.php">Gestión de Hospitalizaciones</a></li>
                <li><a href="../vistaAdmin/gestionar-atenciones.php">Gestión de Atenciones</a></li>
                <li><a href="../vistaAdmin/gestionar-especialistas.php">Gestión de Profesionales</a></li>
                <li><a href="../vistaAdmin/gestionar-clientes.php">Gestión de Clientes</a></li>
                <li><a href="../vistaAdmin/gestionar-mascotas.php">Gestión de Mascotas</a></li>
              <?php endif; ?>

              <?php
              if (!isset($_SESSION['usuario_id'])):
                ?>
                <li><a href="../iniciar-sesion.php">Ingresar</a></li>
                <li><a href="../registrarse.php">Registrarse</a></li>
              <?php endif; ?>

            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="copyright text-center">
    <div class="container">
      <p class="mb-0 text-center">&copy; 2026 Veterinaria San Antón - Todos los derechos reservados.</p>
    </div>
  </div>
</footer>