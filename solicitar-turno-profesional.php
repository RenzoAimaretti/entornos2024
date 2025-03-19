<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

// Conexión a la base de datos (ajusta los parámetros según tu configuración)
$conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los profesionales y sus especialidades
$sql = "SELECT profesionales.id, usuarios.nombre, especialidad.nombre AS especialidad 
        FROM profesionales 
        INNER JOIN usuarios ON profesionales.id = usuarios.id 
        INNER JOIN especialidad ON profesionales.id_esp = especialidad.id";
$result = $conn->query($sql);

$profesionales = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $profesionales[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitar Turno</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
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
              <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                <a class="dropdown-item" href="./vistaAdmin/gestionar-especialistas.php">Especialistas</a>
                <a class="dropdown-item" href="./vistaAdmin/gestionar-clientes.php">Gestionar clientes</a>
              <?php endif; ?>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <h1>Solicitar Turno</h1>
    <div class="row">
      <div class="col-md-6">
        <h2>Seleccionar Profesional</h2>
        <form>
          <div class="form-group">
            <label for="profesional">Profesional:</label>
            <select class="form-control" id="profesional" name="profesional" required>
              <?php foreach ($profesionales as $profesional): ?>
                <option value="<?php echo $profesional['id']; ?>">
                  <?php echo $profesional['nombre'] . ' - ' . $profesional['especialidad']; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#calendarioModal">Seleccionar
            Fecha</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal de Calendario -->
  <div class="modal fade" id="calendarioModal" tabindex="-1" role="dialog" aria-labelledby="calendarioModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="calendarioModalLabel">Seleccionar Fecha</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="calendarioContent">
          <!-- El contenido del calendario se cargará aquí -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Horarios -->
  <div class="modal fade" id="horariosDiaModal" tabindex="-1" role="dialog" aria-labelledby="horariosDiaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="horariosDiaModalLabel">Seleccionar Horario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="horariosDiaContent">
          <!-- El contenido de los horarios se cargará aquí -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para confirmar turno -->
  <div class="modal fade" id="confirmarTurnoModal" tabindex="-1" role="dialog"
    aria-labelledby="confirmarTurnoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmarTurnoModalLabel">Confirme el turno</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="confirmarTurnoForm">
            <div class="form-group">
              <label for="profesional">Profesional</label>
              <input type="text" class="form-control" id="profesional" readonly>
            </div>
            <div class="form-group">
              <label for="fecha">Fecha</label>
              <input type="text" class="form-control" id="fecha" readonly>
            </div>
            <div class="form-group">
              <label for="hora">Hora</label>
              <input type="text" class="form-control" id="hora" readonly>
            </div>
            <div class="form-group">
              <label for="correo">Ingrese su correo electrónico</label>
              <input type="email" class="form-control" id="correo" required>
            </div>
            <div class="form-group">
              <label for="telefono">Ingrese su teléfono</label>
              <input type="text" class="form-control" id="telefono" required>
            </div>
            <div class="form-group">
              <label for="celular">Ingrese su celular (10 dígitos y sólo números)</label>
              <input type="text" class="form-control" id="celular" required>
            </div>
            <div class="form-group">
              <label for="icalendar">¿Recibir iCalendar por correo electrónico?</label>
              <select class="form-control" id="icalendar">
                <option value="si">Sí</option>
                <option value="no">No</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Confirmar</button>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    var profesionalId;
    var mesActual = new Date().getMonth() + 1; // Mes actual
    var anioActual = new Date().getFullYear(); // Año actual

    // Cargar calendario en el modal
    $('#calendarioModal').on('show.bs.modal', function (event) {
      profesionalId = $('#profesional').val();
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
        url: 'obtener-calendario.php',
        method: 'GET',
        data: { id: profesionalId, mes: mes, anio: anio },
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
        url: 'obtener-horarios-dia.php',
        method: 'GET',
        data: { id: profesionalId, fecha: fecha },
        success: function (response) {
          $('#horariosDiaContent').html(response);
        }
      });
    }

    // Seleccionar horario
    function seleccionarHorario(hora) {
      $('#horariosDiaModal').modal('hide');
      $('#confirmarTurnoModal').modal('show');

      // Obtener datos del profesional
      var profesional = $('#profesional option:selected').text();

      // Llenar los campos del modal de confirmación
      $('#confirmarTurnoModal #profesional').val(profesional);
      $('#confirmarTurnoModal #fecha').val(fechaSeleccionada);
      $('#confirmarTurnoModal #hora').val(hora);
    }

    // Manejar la confirmación del turno
    $('#confirmarTurnoForm').on('submit', function (event) {
      event.preventDefault();

      // Obtener los datos del formulario
      var datos = {
        profesional: $('#confirmarTurnoModal #profesional').val(),
        fecha: $('#confirmarTurnoModal #fecha').val(),
        hora: $('#confirmarTurnoModal #hora').val(),
        correo: $('#confirmarTurnoModal #correo').val(),
        telefono: $('#confirmarTurnoModal #telefono').val(),
        celular: $('#confirmarTurnoModal #celular').val(),
        icalendar: $('#confirmarTurnoModal #icalendar').val()
      };

      // Enviar los datos al servidor (puedes ajustar la URL y el método según tu configuración)
      $.ajax({
        url: 'confirmar-turno.php',
        method: 'POST',
        data: datos,
        success: function (response) {
          alert('Turno confirmado con éxito');
          $('#confirmarTurnoModal').modal('hide');
        },
        error: function () {
          alert('Error al confirmar el turno');
        }
      });
    });
  </script>
</body>

</html>