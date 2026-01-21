<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];

// Lógica para determinar el filtro y el orden
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$sqlFilter = '';
$sqlOrder = '';
$activeUpcoming = '';
$activeCompleted = '';

$now = date('Y-m-d H:i:s');

if ($filter === 'completed') {
  $sqlFilter = "AND atenciones.fecha < '$now'";
  $sqlOrder = "ORDER BY atenciones.fecha DESC";
  $activeCompleted = 'active';
} else {
  $sqlFilter = "AND atenciones.fecha >= '$now'";
  $sqlOrder = "ORDER BY atenciones.fecha ASC";
  $activeUpcoming = 'active';
}

// Obteniene los turnos del usuario con el filtro dinámico
$sql = "SELECT atenciones.id, atenciones.fecha, servicios.nombre AS servicio, 
               usuarios.nombre AS profesional, mascotas.nombre AS mascota
        FROM atenciones
        INNER JOIN servicios ON atenciones.id_serv = servicios.id
        INNER JOIN profesionales ON atenciones.id_pro = profesionales.id
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN mascotas ON atenciones.id_mascota = mascotas.id
        WHERE mascotas.id_cliente = ? $sqlFilter $sqlOrder";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$turnos = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $turnos[] = $row;
  }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Turnos</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="../styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5">
    <h1>Mis Turnos</h1>

    <?php if (isset($_SESSION['cancelacion_status']) && $_SESSION['cancelacion_status'] === 'ok'): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> El turno fue cancelado y se envió un correo de confirmación.
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php unset($_SESSION['cancelacion_status']); ?>
    <?php endif; ?>
    <div class="btn-group mb-3" role="group" aria-label="Filtro de turnos">
      <a href="mis-turnos.php?filter=upcoming" class="btn btn-outline-primary <?= $activeUpcoming ?>">Próximos
        Turnos</a>
      <a href="mis-turnos.php?filter=completed" class="btn btn-outline-primary <?= $activeCompleted ?>">Turnos
        Completados</a>
    </div>

    <?php if (count($turnos) > 0): ?>
      <ul class="list-group">
        <?php foreach ($turnos as $turno): ?>
          <li class="list-group-item">
            <div class="d-flex justify-content-between">
              <h5><?php echo htmlspecialchars($turno['servicio']); ?></h5>
            </div>
            <p>Profesional: <?php echo htmlspecialchars($turno['profesional']); ?></p>
            <p>Mascota: <?php echo htmlspecialchars($turno['mascota']); ?></p>
            <p>Fecha: <?php echo date('d-m-Y H:i', strtotime($turno['fecha'])); ?></p>
            <?php if ($filter === 'upcoming'): ?>
              <button class="btn btn-danger btn-sm cancelar-turno" data-id="<?php echo $turno['id']; ?>">Cancelar
                Turno</button>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay turnos pendientes.</p>
    <?php endif; ?>
    <a href="solicitar-turno.php" class="btn btn-primary mt-3">Solicitar Nuevo Turno</a>
  </div>


  <div class="modal fade" id="cancelacionModal" tabindex="-1" role="dialog" aria-labelledby="cancelacionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelacionModalLabel">Confirmar Cancelación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ¿Está seguro de que desea cancelar este turno?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Volver</button>
          <button type="button" class="btn btn-danger" id="confirmar-cancelacion">Sí, Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function () {
      let turnoIdParaCancelar;

      $('.cancelar-turno').on('click', function () {
        turnoIdParaCancelar = $(this).data('id');
        $('#cancelacionModal').modal('show');
      });

      $('#confirmar-cancelacion').on('click', function () {
        const form = $('<form action="cancelar-turno.php" method="post" style="display:none;"></form>');
        const input = $('<input type="hidden" name="id" value="' + turnoIdParaCancelar + '">');
        form.append(input);
        $('body').append(form);
        form.submit();
      });
    });
  </script>
  <?php require_once '../shared/footer.php'; ?>
</body>

</html>