<?php
session_start();

if (!isset($_SESSION['usuario_tipo'])) {
    header('Location: ../iniciar-sesion.php');
    exit();
}

if (!isset($_GET['idMascota'])) {
    die("ID de mascota no proporcionado.");
}

$idMascota = intval($_GET['idMascota']);

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 1. Obtener datos de la mascota
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
    $idPropietario = $row['id_cliente'];
} else {
    die("Mascota no encontrada.");
}

// VALIDACIÓN DE SEGURIDAD
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

<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if (isset($_GET['res']) && $_GET['res'] == 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ¡Mascota actualizada correctamente!
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0">Detalles de <?php echo htmlspecialchars($mascota_nombre); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3"><strong>Nombre</strong>
                                <p><?php echo htmlspecialchars($mascota_nombre); ?></p>
                            </div>
                            <div class="col-md-3"><strong>Raza</strong>
                                <p><?php echo htmlspecialchars($raza); ?></p>
                            </div>
                            <div class="col-md-3"><strong>Nacimiento</strong>
                                <p><?php echo $fecha_nac ? $fecha_nac : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-3"><strong>Estado</strong>
                                <p><?php echo $fecha_mue ? "<span class='badge badge-danger'>Fallecido ($fecha_mue)</span>" : "<span class='badge badge-success'>Vivo</span>"; ?>
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                <a href="../vistaAdmin/detalle-cliente.php?id=<?php echo $idPropietario; ?>"
                                    class="btn btn-secondary">Volver al Cliente</a>
                            <?php else: ?>
                                <a href="../vistaCliente/mis-mascotas.php" class="btn btn-secondary">Volver</a>
                            <?php endif; ?>

                            <button type="button" class="btn btn-warning" data-toggle="modal"
                                data-target="#editarModal">Editar Mascota</button>
                        </div>
                    </div>
                </div>

                <div class="card shadow mt-4">
                    <div class="card-body">
                        <h3>Historia Clínica</h3>
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Profesional</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $queryAt = "SELECT a.id, a.fecha, s.nombre as servicio, u.nombre as profesional 
                                            FROM atenciones a
                                            INNER JOIN servicios s ON a.id_serv = s.id
                                            INNER JOIN usuarios u ON a.id_pro = u.id
                                            WHERE a.id_mascota = $idMascota ORDER BY a.fecha DESC";
                                $resAt = $conn->query($queryAt);
                                if ($resAt && $resAt->num_rows > 0) {
                                    while ($at = $resAt->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . date('d/m/Y H:i', strtotime($at['fecha'])) . "</td>
                                                <td>{$at['servicio']}</td>
                                                <td>{$at['profesional']}</td>
                                                <td><a href='detalle-atencionAP.php?id={$at['id']}' class='btn btn-sm btn-info'>Ver</a></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No hay atenciones registradas</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="editar-mascota.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarModalLabel">Editar Datos de Mascota</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idMascota" value="<?php echo $idMascota; ?>">

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre"
                                value="<?php echo htmlspecialchars($mascota_nombre); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Raza</label>
                            <input type="text" class="form-control" name="raza"
                                value="<?php echo htmlspecialchars($raza); ?>">
                        </div>

                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nac" value="<?php echo $fecha_nac; ?>">
                        </div>

                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <div class="form-group">
                                <label>Fecha de Muerte (Opcional)</label>
                                <input type="date" class="form-control" name="fecha_mue" value="<?php echo $fecha_mue; ?>">
                            </div>
                        <?php endif; ?>
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
    <?php $conn->close(); ?>
</body>

</html>