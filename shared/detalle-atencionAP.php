<?php
session_start();

// Validación de sesión corregida
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

// Validamos que exista el ID en la URL
if (!isset($_GET['id'])) {
    die("ID de atención no proporcionado.");
}

$idAtencion = intval($_GET['id']);

$query = "SELECT a.id, 
                m.nombre as nombreMascota,
                m.id as idMascota,
                s.nombre as nombreServicio,
                a.fecha,
                a.detalle,
                u.nombre as nombrePro,
                u.id as idPro
        FROM atenciones a
        INNER JOIN mascotas m on a.id_mascota = m.id
        INNER JOIN servicios s on a.id_serv = s.id
        INNER JOIN usuarios u on a.id_pro = u.id
        WHERE a.id = $idAtencion";

$result = $conn->query($query);
$atencion = $result->fetch_assoc();

if (!$atencion) {
    die("Atención no encontrada.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Atención</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-center">
            <div class="card text-center" style="width:50rem;">
                <div class="card-header bg-info text-white">
                    <h2 class="mb-0">Detalle de la atención de
                        <?php echo htmlspecialchars($atencion['nombreMascota']) ?>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title font-weight-bold">Nombre del Servicio:</h5>
                        <p class="card-text text-secondary"><?php echo htmlspecialchars($atencion['nombreServicio']) ?>
                        </p>
                    </div>
                    <div class="mb-4">
                        <h5 class="card-title font-weight-bold">Fecha de la Atención:</h5>
                        <p class="card-text text-secondary">
                            

                                                    <?php echo date('d/m/Y H:i', strtotime($atencion['fecha'])); ?></p>
                    </div>
                    <div class="mb-4">
                        <h5 class="card-title font-weight-bold">Detalles de la Atención:</h5>
                        <p class="card-text text-secondary"><?php echo nl2br(htmlspecialchars($atencion['detalle'])); ?>
                        </p>
                    </div>
                    <div class="mb-4">
                        <h5 class="card-title font-weight-bold">Nombre del Profesional:</h5>
                        <p class="card-text text-secondary"><?php echo htmlspecialchars($atencion['nombrePro']) ?></p>
                    </div>

                    <hr>

                    <div class="d-flex flex-wrap justify-content-center" style="gap: 10px;">

                        <a href="../shared/detalle-mascota.php?idMascota=<?php echo $atencion['idMascota']; ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-paw"></i> Volver a Mascota
                        </a>

                        <a href="../vistaAdmin/detalle-especialista.php?id=<?php echo $atencion['idPro']; ?>"
                            class="btn btn-outline-success">
                            <i class="fas fa-user-md"></i> Volver a Especialista
                        </a>

                        <button type="button" class="btn btn-warning" data-toggle="modal"
                            data-target="#editarAtencionModal">
                            Editar Atención
                        </button>

                    <?php if (strtotime($atencion['fecha']) < time()): ?>
                            <button class="btn btn-danger disabled"
                                    title="No se puede eliminar una atención pasada">Eliminar</button>
                    <?php else: ?>
                            <form action="../shared/eliminar-atencion.php" method="POST" style="display:inline;"
                                onsubmit="return confirm('¿Seguro que deseas eliminar esta atención?');">
                                <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editarAtencionModal" tabindex="-1" role="dialog"
        aria-labelledby="editarAtencionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content text-left">
                <form action="../shared/editar-atencion.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarAtencionModalLabel">Editar Detalles de Atención</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">
                        <div class="form-group">
                            <label for="fecha">Fecha y Hora</label>
                            <input type="datetime-local" class="form-control" id="fecha" name="fecha"
                                value="<?php echo date('Y-m-d\TH:i', strtotime($atencion['fecha'])); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="detalle">Observaciones</label>
                            <textarea class="form-control" id="detalle" name="detalle" rows="4"
                                required><?php echo htmlspecialchars($atencion['detalle']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>