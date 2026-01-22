<?php
session_start();
// Seguridad: Solo admin y especialista
if (!isset($_SESSION['usuario_tipo']) || ($_SESSION['usuario_tipo'] !== 'admin' && $_SESSION['usuario_tipo'] !== 'especialista')) {
  header('Location: ../index.php');
  exit();
}

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Hospitalizaciones Activas
$queryActivas = "SELECT h.*, m.nombre as mascota, u.nombre as profesional 
                 FROM hospitalizaciones h
                 INNER JOIN mascotas m ON h.id_mascota = m.id
                 INNER JOIN usuarios u ON h.id_pro_deriva = u.id
                 WHERE h.estado = 'Activa' ORDER BY h.fecha_ingreso ASC";
$resActivas = $conn->query($queryActivas);

// Historial
$queryHistorial = "SELECT h.*, m.nombre as mascota, u.nombre as profesional 
                   FROM hospitalizaciones h
                   INNER JOIN mascotas m ON h.id_mascota = m.id
                   INNER JOIN usuarios u ON h.id_pro_deriva = u.id
                   WHERE h.estado = 'Finalizada' ORDER BY h.fecha_egreso_real DESC LIMIT 10";
$resHistorial = $conn->query($queryHistorial);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Hospitalización - San Antón</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2><i class="fas fa-hospital-alt text-danger"></i> Pacientes Hospitalizados</h2>
      <?php if ($_SESSION['usuario_tipo'] === 'especialista'): ?>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalIngreso">
          <i class="fas fa-plus"></i> Registrar Nuevo Ingreso
        </button>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['res'])): ?>
      <?php if ($_GET['res'] == 'ok'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          ¡Ingreso registrado correctamente!
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      <?php elseif ($_GET['res'] == 'alta_ok'): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          Alta médica procesada con éxito.
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow mb-5">
      <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Pacientes Actuales</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Mascota</th>
              <th>Ingreso</th>
              <th>Derivado por</th>
              <th>Motivo</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($resActivas && $resActivas->num_rows > 0): ?>
              <?php while ($h = $resActivas->fetch_assoc()): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($h['mascota']) ?></strong></td>
                  <td><?= date('d/m/Y H:i', strtotime($h['fecha_ingreso'])) ?></td>
                  <td><?= htmlspecialchars($h['profesional']) ?></td>
                  <td><?= htmlspecialchars($h['motivo']) ?></td>
                  <td>
                    <?php if ($_SESSION['usuario_tipo'] === 'especialista'): ?>
                      <button type="button" class="btn btn-success btn-sm"
                        onclick="abrirModalAlta(<?= $h['id'] ?>, '<?= htmlspecialchars($h['mascota']) ?>')">
                        Dar de Alta
                      </button>
                    <?php else: ?>
                      <span class="badge badge-secondary">Solo lectura (Admin)</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center py-4">No hay mascotas internadas ahora.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Historial Reciente de Altas</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>Mascota</th>
              <th>Ingreso</th>
              <th>Egreso</th>
              <th>Motivo</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($hist = $resHistorial->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($hist['mascota']) ?></td>
                <td><?= date('d/m/Y', strtotime($hist['fecha_ingreso'])) ?></td>
                <td><?= date('d/m/Y', strtotime($hist['fecha_egreso_real'])) ?></td>
                <td><?= htmlspecialchars($hist['motivo']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalIngreso" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="../shared/guardar-hospitalizacion.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Nuevo Ingreso Hospitalario</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Seleccionar Mascota</label>
              <select name="id_mascota" class="form-control" required>
                <option value="" disabled selected>Seleccione una mascota</option>
                <?php
                $mascotas = $conn->query("SELECT id, nombre FROM mascotas ORDER BY nombre ASC");
                while ($m = $mascotas->fetch_assoc())
                  echo "<option value='{$m['id']}'>" . htmlspecialchars($m['nombre']) . "</option>";
                ?>
              </select>
            </div>
            <div class="form-group">
              <label>Motivo de Internación</label>
              <textarea name="motivo" class="form-control" placeholder="Ej: Observación post-quirúrgica"
                required></textarea>
            </div>
            <input type="hidden" name="id_pro" value="<?= $_SESSION['usuario_id'] ?>">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Ingreso</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalConfirmarAlta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-success">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="fas fa-check-circle"></i> Confirmar Alta Médica</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body text-center py-4">
          <p class="h5">¿Está seguro de dar el alta a <strong><span id="nombreMascotaAlta"></span></strong>?</p>
          <p class="text-muted">Esta acción registrará la salida y la mascota aparecerá en el historial.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <form action="../shared/finalizar-hospitalizacion.php" method="POST">
            <input type="hidden" name="id_hosp" id="idHospAlta">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Sí, confirmar alta</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Función para abrir el modal de confirmación y cargar los datos dinámicamente
    function abrirModalAlta(id, nombre) {
      document.getElementById('idHospAlta').value = id;
      document.getElementById('nombreMascotaAlta').innerText = nombre;
      $('#modalConfirmarAlta').modal('show');
    }
  </script>
  <?php require_once '../shared/footer.php'; ?>
</body>

</html>