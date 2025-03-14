<?php
if (isset($_GET['id']) && isset($_GET['mes']) && isset($_GET['anio'])) {
  $profesionalId = $_GET['id'];
  $mesActual = $_GET['mes'];
  $anioActual = $_GET['anio'];

  require 'vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  // Crear conexión
  $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Obtener los días con turnos disponibles del profesional en el mes y año especificados
  $sql = "SELECT DISTINCT DATE_FORMAT(dia, '%Y-%m-%d') as dia FROM horarios WHERE profesional_id = ? AND MONTH(dia) = ? AND YEAR(dia) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iii", $profesionalId, $mesActual, $anioActual);
  $stmt->execute();
  $result = $stmt->get_result();

  $disponibles = [];
  while ($row = $result->fetch_assoc()) {
    $disponibles[] = $row['dia'];
  }

  $stmt->close();
  $conn->close();

  // Generar el calendario
  $meses = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
  ];

  $diasSemana = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'];

  echo '<div class="calendar">';
  echo '<div class="month">';
  echo '<ul>';
  echo '<li class="prev" onclick="cambiarMes(-1)">&#10094;</li>';
  echo '<li class="next" onclick="cambiarMes(1)">&#10095;</li>';
  echo '<li id="mesAnio">' . $meses[$mesActual - 1] . ' ' . $anioActual . '</li>';
  echo '</ul>';
  echo '</div>';

  echo '<ul class="weekdays">';
  foreach ($diasSemana as $dia) {
    echo '<li>' . $dia . '</li>';
  }
  echo '</ul>';

  echo '<ul class="days">';
  $primerDiaMes = date('w', strtotime("$anioActual-$mesActual-01"));
  $diasMes = date('t', strtotime("$anioActual-$mesActual-01"));

  for ($i = 0; $i < $primerDiaMes; $i++) {
    echo '<li></li>';
  }

  for ($dia = 1; $dia <= $diasMes; $dia++) {
    $fecha = "$anioActual-$mesActual-$dia";
    $clase = in_array($fecha, $disponibles) ? 'disponible' : 'ocupado';
    echo '<li class="' . $clase . '" onclick="seleccionarDia(\'' . $fecha . '\')">' . $dia . '</li>';
  }

  echo '</ul>';
  echo '</div>';
}
?>

<!-- Modal para confirmar turno -->
<div class="modal fade" id="confirmarTurnoModal" tabindex="-1" role="dialog" aria-labelledby="confirmarTurnoModalLabel"
  aria-hidden="true">
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

<script>
  var profesionalId;
  var mesActual = 3; // Marzo
  var anioActual = 2025;

  // Filtrar profesionales
  document.getElementById('search').addEventListener('input', function () {
    var filter = this.value.toUpperCase();
    var items = document.getElementById('professional-list').getElementsByClassName('list-group-item');
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
    profesionalId = button.data('id');

    $.ajax({
      url: 'obtener-horarios.php',
      method: 'GET',
      data: { id: profesionalId },
      success: function (response) {
        $('#horariosContent').html(response);
      }
    });
  });

  // Cargar calendario en el modal
  $('#calendarioModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    profesionalId = button.data('id');

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
    var profesional = $('#professional-list .list-group-item[data-id="' + profesionalId + '"] span').text();

    // Llenar los campos del modal de confirmación
    $('#confirmarTurnoModal #profesional').val(profesional);
    $('#confirmarTurnoModal #fecha').val(fecha);
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

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $profesional = $_POST['profesional'];
  $fecha = $_POST['fecha'];
  $hora = $_POST['hora'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $celular = $_POST['celular'];
  $icalendar = $_POST['icalendar'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Insertar el turno confirmado en la base de datos
  $sql = "INSERT INTO turnos_confirmados (profesional, fecha, hora, correo, telefono, celular, icalendar) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssss", $profesional, $fecha, $hora, $correo, $telefono, $celular, $icalendar);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo 'Turno confirmado con éxito';
  } else {
    echo 'Error al confirmar el turno';
  }

  $stmt->close();
  $conn->close();
}
?>