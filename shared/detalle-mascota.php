<?php
session_start();


if (!isset($_SESSION['usuario_tipo'])) {
    header('Location: ../iniciar-sesion.php');
    exit();
}

if (!isset($_GET['idMascota'])) {
    die("ID de mascota no proporcionado.");
}

$idMascota = $_GET['idMascota'];

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


$query = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue, m.id_cliente
          FROM mascotas m 
          WHERE m.id = $idMascota";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mascota_nombre = $row['mascota_nombre'];
    $raza = $row['raza'];
    $fecha_nac = $row['fecha_nac'];
    $fecha_mue = $row['fecha_mue'];
    $idMascota = $row['id'];
    $idPropietario = $row['id_cliente'];
} else {
    die("Mascota no encontrada.");
}

// VALIDACIÓN DE SEGURIDAD PARA CLIENTES: solo pueden ver sus propias mascotas
if ($_SESSION['usuario_tipo'] === 'cliente' && $_SESSION['usuario_id'] != $idPropietario) {
    die("Acceso denegado: esta mascota no le pertenece.");
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de mascota - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow" style="width:50rem;">
            <div class="card-header bg-green text-white">
                <h2 class="mb-0">Detalles de <?php echo htmlspecialchars($mascota_nombre) ?> </h2>
            </div>
            <div class="card-body">
                <div class="text-left">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($mascota_nombre); ?></p>
                    <p><strong>Raza:</strong> <?php echo htmlspecialchars($raza); ?></p>
                    <p><strong>Fecha de Nacimiento:</strong>
                        <?php echo htmlspecialchars($fecha_nac ? $fecha_nac : 'N/A'); ?></p>
                    <p><strong>Estado:</strong>
                        <?php echo $fecha_mue ? '<span class="badge badge-danger">Fallecido (' . $fecha_mue . ')</span>' : '<span class="badge badge-success">Activo</span>'; ?>
                    </p>

                    <hr>

                    <?php if ($_SESSION['usuario_tipo'] === 'admin' || $_SESSION['usuario_tipo'] === 'cliente'): ?>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editarModal">
                            Editar Información
                        </button>
                    <?php endif; ?>
                </div>

                <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content text-left">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarModalLabel">Editar Mascota</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="editar-mascota.php" method="POST">
                                    <input type="hidden" name="idMascota" value="<?php echo $idMascota; ?>">
                                    <div class="form-group">
                                        <label>Nombre:</label>
                                        <input type="text" class="form-control" name="nombre"
                                            value="<?php echo htmlspecialchars($mascota_nombre); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Raza:</label>
                                        <input type="text" class="form-control" name="raza"
                                            value="<?php echo htmlspecialchars($raza); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento:</label>
                                        <input type="date" class="form-control" name="fecha_nac"
                                            value="<?php echo $fecha_nac; ?>">
                                    </div>

                                    <?php if ($_SESSION['usuario_tipo'] !== 'cliente'): ?>
                                        <div class="form-group">
                                            <label>Fecha de Muerte (si aplica):</label>
                                            <input type="date" class="form-control" name="fecha_mue"
                                                value="<?php echo $fecha_mue; ?>">
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>

                                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                            <button type="button" class="btn btn-danger"
                                                onclick="confirmarEliminar()">Eliminar Mascota</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:2rem;" class="tabla-historia-clinica">
                    <h3><i class="fas fa-notes-medical"></i> Historia Clínica</h3>
                    <table class="table table-striped table-hover mt-3">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Servicio</th>
                                <th>Profesional</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $queryAtenciones = "SELECT a.fecha, s.nombre as nombreServicio, u.nombre as nombrePro 
                                               FROM atenciones a
                                               INNER JOIN usuarios u on a.id_pro = u.id
                                               INNER JOIN servicios s on a.id_serv = s.id
                                               WHERE a.id_mascota = $idMascota
                                               ORDER BY a.fecha DESC";

                            $resultAtenciones = $conn->query($queryAtenciones);
                            if ($resultAtenciones && $resultAtenciones->num_rows > 0) {
                                while ($rowA = $resultAtenciones->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($rowA['fecha'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($rowA['nombreServicio']) . "</td>";
                                    echo "<td>" . htmlspecialchars($rowA['nombrePro']) . "</td>";
                                    echo "<td><a class='btn btn-info btn-sm' href='detalle-atencion.php'>Detalles</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No hay atenciones registradas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmarEliminar() {
            if (confirm('¿Está seguro de que desea eliminar esta mascota? Esta acción no se puede deshacer.')) {
                window.location.href = 'eliminar-mascota.php?idMascota=<?php echo $idMascota; ?>';
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>