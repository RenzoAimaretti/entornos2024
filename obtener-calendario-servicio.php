<?php
if (isset($_GET['id']) && isset($_GET['mes']) && isset($_GET['anio'])) {
  $servicioId = $_GET['id'];
  $mesActual = $_GET['mes'];
  $anioActual = $_GET['anio'];

  // Conexión a la base de datos (ajusta los parámetros según tu configuración)
  $conn = new mysqli('localhost', 'root', 'marcoruben9', 'veterinaria');

  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }

  // Obtener los días ocupados del servicio
  $sql = "SELECT DISTINCT DATE_FORMAT(dia, '%Y-%m-%d') as dia FROM horarios_servicio WHERE servicio_id = ? AND MONTH(dia) = ? AND YEAR(dia) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iii", $servicioId, $mesActual, $anioActual);
  $stmt->execute();
  $result = $stmt->get_result();

  $ocupados = [];
  while ($row = $result->fetch_assoc()) {
    $ocupados[] = $row['dia'];
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
    $clase = in_array($fecha, $ocupados) ? 'disponible' : 'ocupado';
    echo '<li class="' . $clase . '" onclick="seleccionarDia(\'' . $fecha . '\')">' . $dia . '</li>';
  }

  echo '</ul>';
  echo '</div>';
}
?>