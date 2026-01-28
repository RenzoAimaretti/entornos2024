<?php
session_start();

date_default_timezone_set('America/Argentina/Buenos_Aires');

$id = $_SESSION['usuario_id'] ?? 0;
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$hoy = date('Y-m-d');

$query = "SELECT u.id, u.nombre FROM usuarios u WHERE u.id = $id";
$result = $conn->query($query);
$nombre_cliente = ($result && $row = $result->fetch_assoc()) ? $row['nombre'] : "Cliente";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Mascotas - Veterinaria San Antón</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="../styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once '../shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">

    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-5">
      <h1 class="mb-0 font-weight-bold">Mis Mascotas</h1>
      <p class="mb-0 mt-1" style="opacity: 0.9;">Gestiona el perfil de tus compañeros fieles</p>
    </div>

    <div class="row">

      <div class="col-lg-4 mb-4">
        <div class="card shadow-lg border-0 sticky-top" style="top: 100px; z-index: 1;">
          <div class="card-header bg-white text-center border-0 pt-4">
            <h4 class="text-teal font-weight-bold"><i class="fas fa-plus-circle"></i> Nueva Mascota</h4>
          </div>
          <div class="card-body p-4">

            <?php if (isset($_GET['error']) && $_GET['error'] == 'fecha'): ?>
              <div class="alert alert-danger small">
                <i class="fas fa-exclamation-circle"></i> La fecha no puede ser futura.
              </div>
            <?php endif; ?>

            <form action="../shared/alta-mascota.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id_cliente" value="<?php echo $id ?>">

              <div id="preview-container">
                <img id="preview-image" src="#" alt="Preview">
              </div>

              <div class="form-group">
                <label class="font-weight-bold small text-muted">Nombre</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-paw text-teal"></i></span>
                  </div>
                  <input type="text" class="form-control bg-light border-0" id="nombre" name="nombre"
                    placeholder="Ej: Firulais" required>
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold small text-muted">Raza / Especie</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-dog text-teal"></i></span>
                  </div>
                  <input type="text" class="form-control bg-light border-0" id="raza" name="raza"
                    placeholder="Ej: Caniche / Gato">
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold small text-muted">Fecha de Nacimiento</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0"><i
                        class="fas fa-birthday-cake text-teal"></i></span>
                  </div>
                  <input type="date" class="form-control bg-light border-0" id="fecha_nacimiento"
                    name="fecha_nacimiento" max="<?php echo $hoy; ?>" required>
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold small text-muted">Foto de perfil</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*"
                    onchange="previewFile()">
                  <label class="custom-file-label" for="foto" data-browse="Elegir">Seleccionar archivo...</label>
                </div>
              </div>

              <button type="submit" class="btn btn-block font-weight-bold text-white shadow-sm mt-4"
                style="background-color: #00897b; border-radius: 50px;">
                Registrar Mascota
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <h3 class="mb-4 text-secondary">Tus Mascotas Registradas</h3>

        <?php
        $sql = "SELECT * FROM mascotas WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result_mascotas = $stmt->get_result();

        if ($result_mascotas->num_rows > 0) {
          echo '<div class="row">';
          while ($row = $result_mascotas->fetch_assoc()) {
            $tieneFoto = !empty($row['foto']) && file_exists($row['foto']);
            $imagenSrc = !empty($row['foto']) ? $row['foto'] : '';
            ?>

            <div class="col-md-6 mb-4">
              <div class="card pet-card shadow-sm h-100">

                <div class="pet-img-container">
                  <?php if (!empty($imagenSrc)): ?>
                    <img src="<?php echo htmlspecialchars($imagenSrc); ?>"
                      alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                  <?php else: ?>
                    <i class="fas fa-paw no-photo-icon"></i>
                  <?php endif; ?>
                </div>

                <div class="card-body text-center">
                  <h4 class="card-title font-weight-bold mb-1"><?php echo htmlspecialchars($row['nombre']); ?></h4>
                  <p class="text-muted mb-3"><?php echo htmlspecialchars($row['raza'] ?? 'Raza desconocida'); ?></p>

                  <div class="d-flex justify-content-center">
                    <a href="../shared/detalle-mascota.php?idMascota=<?php echo $row['id']; ?>"
                      class="btn btn-outline-info rounded-pill px-4" style="color: #00897b; border-color: #00897b;">
                      <i class="fas fa-notes-medical mr-1"></i> Ver Historia Clínica
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <?php
          }
          echo '</div>';
        } else {
          ?>
          <div class="text-center py-5 bg-light rounded shadow-sm border border-light">
            <div class="mb-3">
              <i class="fas fa-dog fa-4x text-muted" style="opacity: 0.3;"></i>
            </div>
            <h4 class="text-muted">Aún no tienes mascotas registradas.</h4>
            <p class="text-muted">Utiliza el formulario de la izquierda para agregar a tu primer amigo.</p>
          </div>
          <?php
        }
        $stmt->close();
        $conn->close();
        ?>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    function previewFile() {
      var preview = document.querySelector('#preview-image');
      var container = document.querySelector('#preview-container');
      var file = document.querySelector('input[type=file]').files[0];
      var reader = new FileReader();

      reader.onloadend = function () {
        preview.src = reader.result;
        container.style.display = 'block';
      }

      if (file) {
        reader.readAsDataURL(file);
        document.querySelector('.custom-file-label').textContent = file.name;
      } else {
        preview.src = "";
        container.style.display = 'none';
        document.querySelector('.custom-file-label').textContent = "Seleccionar archivo...";
      }
    }
  </script>

  <?php require_once '../shared/footer.php'; ?>
</body>

</html>