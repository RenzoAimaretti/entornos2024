<!-- !!! -->
<!-- Este detalle de atencion solo aplica para el ADMIN y el PROFESIONAL -->
<!-- !!! -->
<?php
session_start();

if(!isset($_SESSION) &&(!$_SESSION['usuario_tipo'] !== 'admin' || !$_SESSION['usuario_tipo'] !== 'especialista')){
    header('Location: ../index.php');
    exit();
}else{
    require '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
    // Crear conexión
    $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }else{
        $idAtencion = $_GET['id'];

        $query = "SELECT a.id, 
                        m.nombre as nombreMascota,
                        m.id as idMascota,
                        s.nombre as nombreServicio,
                        a.fecha,
                        a.detalle,
                        u.nombre as nombrePro,
                        u.id as idPro
                FROM atenciones a
                inner join mascotas m on a.id_mascota = m.id
                inner join servicios s on a.id_serv = s.id
                inner join usuarios u on a.id_pro = u.id
                WHERE a.id = $idAtencion";
        $result = $conn->query($query);
        $atencion = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Atención</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../shared/navbar.php'; ?>
    <div class="d-flex justify-content-center">

        <div class="Card text-center" style="width:50rem;">
            <div class="card-header">
                <h2>Detalle de la atencion de <?php echo $atencion['nombreMascota'] ?> </h2>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="card-title font-weight-bold">Nombre del Servicio:</h5>
                    <p class="card-text text-secondary"><?php echo $atencion['nombreServicio'] ?></p>
                </div>
                <div class="mb-4">
                    <h5 class="card-title font-weight-bold">Fecha de la Atención:</h5>
                    <p class="card-text text-secondary"><?php echo date('d/m/Y H:i', strtotime($atencion['fecha'])); ?></p>
                </div>
                <div class="mb-4">
                    <h5 class="card-title font-weight-bold">Detalles de la Atención:</h5>
                    <p class="card-text text-secondary"><?php echo nl2br(htmlspecialchars($atencion['detalle'])); ?></p>
                </div>
                <div class="mb-4">
                    <h5 class="card-title font-weight-bold">Nombre del Profesional:</h5>
                    <p class="card-text text-secondary"><?php echo $atencion['nombrePro'] ?></p>
                </div>

                <div class="d-flex justify-content-between">
                <a href="../vistaAdmin/gestionar-atenciones.php" class="btn btn-secondary">Volver</a>
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editarAtencionModal">
                    Editar
                </button>

                <!-- Modal -->
                <div class="modal fade" id="editarAtencionModal" tabindex="-1" role="dialog" aria-labelledby="editarAtencionModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="../shared/editar-atencion.php" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editarAtencionModalLabel">Editar Atención</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">
                                    <div class="form-group">
                                        <label for="fecha">Fecha de la Atención</label>
                                        <input type="datetime-local" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d\TH:i', strtotime($atencion['fecha'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="detalle">Detalles de la Atención</label>
                                        <textarea class="form-control" id="detalle" name="detalle" rows="3" required><?php echo $atencion['detalle']; ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php if (strtotime($atencion['fecha']) < time()): ?>
                    <a href="#" class="btn btn-danger disabled" aria-disabled="true">Eliminar</a>
                <?php else: ?>
                    <form action="../shared/eliminar-atencion.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $atencion['id']; ?>">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                <?php endif; ?>
                </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>