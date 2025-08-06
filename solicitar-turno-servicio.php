<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Solicitar Turno Servicio</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  <style>
    .calendar {
      font-family: Arial, sans-serif;
      width: 100%;
    }

    .month {
      padding: 20px 25px;
      width: 100%;
      background: #1abc9c;
      text-align: center;
    }

    .month ul {
      margin: 0;
      padding: 0;
      list-style-type: none;
    }

    .month ul li {
      color: white;
      font-size: 20px;
      text-transform: uppercase;
      letter-spacing: 3px;
    }

    .month .prev,
    .month .next {
      cursor: pointer;
      float: left;
      padding-top: 10px;
      padding-bottom: 10px;
    }

    .month .next {
      float: right;
    }

    .weekdays {
      margin: 0;
      padding: 10px 0;
      background-color: #ddd;
    }

    .weekdays li {
      display: inline-block;
      width: 13.6%;
      color: #666;
      text-align: center;
    }

    .days {
      padding: 10px 0;
      background: #eee;
      margin: 0;
    }

    .days li {
      list-style-type: none;
      display: inline-block;
      width: 13.6%;
      text-align: center;
      margin-bottom: 5px;
      font-size: 12px;
      color: #777;
    }

    .days li.ocupado {
      background: #e74c3c;
      color: white;
      cursor: not-allowed;
    }

    .days li.disponible {
      background: #2ecc71;
      color: white;
      cursor: pointer;
    }

    .days li.disponible:hover {
      background: #27ae60;
    }
  </style>
</head>

<body>
  <!-- Navegación -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
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
            <a class="nav-link active" href="index.php">Inicio</a>
          </li>
          <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <li class="nav-item dropdown d-flex align-items-center">
              <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Usuario" width="40" height="40"
                class="mr-2">
              <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <?php echo $_SESSION['usuario_nombre']; ?>
              </a>
              <div class="dropdown-menu" aria-labelledby="usuarioDropdown">
                <a class="dropdown-item" href="mis-mascotas.php">Mis Mascotas</a>
                <a class="dropdown-item" href="mis-turnos.php">Mis Turnos</a>
                <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
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
              <a class="dropdown-item" href="profesionales.php">Profesionales</a>
              <a class="dropdown-item" href="nosotros.php">Nosotros</a>
              <a class="dropdown-item" href="contactanos.php">Contacto</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Sección de Solicitar Turno Servicio -->
  <section class="container text-center my-4">
    <h3>Solicitar turno para Servicio</h3>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <input type="text" class="form-control mb-3" id="search" placeholder="Buscar servicio...">
        <div class="list-group" id="service-list">
          <?php
          // Conexión a la base de datos (ajusta los parámetros según tu configuración)
          $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

          if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
          }

          // Consulta para obtener todos los servicios
          $sql = "SELECT * FROM servicios";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
              echo "<span>" . $row['nombre'] . "</span>";
              echo "<div>";
              echo "<button class='btn btn-warning btn-sm mr-2' data-toggle='modal' data-target='#horariosModal' data-id='" . $row['id'] . "'>Horarios</button>";
              echo "<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#calendarioModal' data-id='" . $row['id'] . "'>&#x2192;</button>";
              echo "</div>";
              echo "</div>";
            }
          } else {
            echo "<p>No se encontraron servicios.</p>";
          }

          $conn->close();
          ?>
        </div>
      </div>
    </div>
    <a href="solicitar-turno.php" class="btn btn-secondary mt-3">Cancelar</a>
  </section>

  <!-- Modal para mostrar horarios -->
  <div class="modal fade" id="horariosModal" tabindex="-1" role="dialog" aria-labelledby="horariosModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="horariosModalLabel">Horarios de atención</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="horariosContent"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para mostrar calendario -->
  <div class="modal fade" id="calendarioModal" tabindex="-1" role="dialog" aria-labelledby="calendarioModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="calendarioModalLabel">Seleccione una fecha</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="calendarioContent">
            <!-- Aquí se cargará el calendario -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para mostrar horarios del día -->
  <div class="modal fade" id="horariosDiaModal" tabindex="-1" role="dialog" aria-labelledby="horariosDiaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="horariosDiaModalLabel">Seleccione un horario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="horariosDiaContent"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    var servicioId;
    var mesActual = 3; // Marzo
    var anioActual = 2025;

    // Filtrar servicios
    document.getElementById('search').addEventListener('input', function () {
      var filter = this.value.toUpperCase();
      var items = document.getElementById('service-list').getElementsByClassName('list-group-item');
      for (var i = 0; i < items.length; i++) {
        var text = items[i].getElementsByTagName('span')[0].textContent || items[i].getElementsByTagName('span')[0].innerText;
        if (text.toUpperCase().indexOf(filter) > -1) {
          items[i].style.display = '';
        } else {
          items[i].style.display = 'none';
        }
      }
    });

    // Cargar horarios en el modal
    $('#horariosModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      servicioId = button.data('id');

      $.ajax({
        url: 'obtener-horarios-servicio.php',
        method: 'GET',
        data: { id: servicioId },
        success: function (response) {
          $('#horariosContent').html(response);
        }
      });
    });

    // Cargar calendario en el modal
    $('#calendarioModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      servicioId = button.data('id');

      cargarCalendario(mesActual, anioActual);
    });

    // Cambiar mes
    function cambiarMes(direccion) {
      mesActual += direccion;

      if (mesActual < 1) {
        mesActual = 12;
        anioActual--;
      } else if (mesActual > 12) {
        mesActual = 1;
        anioActual++;
      }

      cargarCalendario(mesActual, anioActual);
    }

    // Cargar calendario
    function cargarCalendario(mes, anio) {
      $.ajax({
        url: 'obtener-calendario-servicio.php',
        method: 'GET',
        data: { id: servicioId, mes: mes, anio: anio },
        success: function (response) {
          $('#calendarioContent').html(response);
        }
      });
    }

    // Seleccionar día
    function seleccionarDia(fecha) {
      $('#calendarioModal').modal('hide');
      $('#horariosDiaModal').modal('show');

      $.ajax({
        url: 'obtener-horarios-dia-servicio.php',
        method: 'GET',
        data: { id: servicioId, fecha: fecha },
        success: function (response) {
          $('#horariosDiaContent').html(response);
        }
      });
    }
  </script>
</body>

</html>