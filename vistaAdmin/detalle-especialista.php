<!-- quizas dejar este para admin y hacer otro aparte para el especialista -->
 <?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    if ($id === null) {
        echo "ID no proporcionado.";
    } else {
        require '../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
        // Crear conexión
        $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        if ($_SESSION['usuario_tipo'] !== 'admin') {
            die("Acceso denegado");
        }

        $query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
                  FROM usuarios u 
                  inner JOIN profesionales p ON u.id = p.id
                  inner JOIN especialidad e on p.id_esp = e.id
                  WHERE u.id = $id";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $email = $row['email'];
            $telefono = $row['telefono'];
            $especialidad = $row['especialidad'];
        }

        
                              
    }
}
 ?>

 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de especialista</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
 </head>
 <body>
    <?php require_once '../shared/navbar.php'; ?>
    <div class="d-flex justify-content-center">
    <div class="card text-center" style="width:50rem;">
        <div class="card-header">
            <h2>Detalles de <?php echo $nombre?> </h2>
        </div>
    <div class="card-body">
        <div>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Telefono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
            <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($especialidad); ?></p>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editarModal">Editar</button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarModalLabel">Editar Mascota</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="../shared/editar-especialista.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="especialidad">Especialidad</label>
                                <select class="form-control" id="especialidad" name="especialidad" required>
                                    <?php
                                    $especialidadesQuery = "SELECT id, nombre FROM especialidad";
                                    $especialidadesResult = $conn->query($especialidadesQuery);
                                    if ($especialidadesResult->num_rows > 0) {
                                        while ($especialidadRow = $especialidadesResult->fetch_assoc()) {
                                            $selected = ($especialidadRow['nombre'] === $especialidad) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($especialidadRow['id']) . "' $selected>" . htmlspecialchars($especialidadRow['nombre']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No hay especialidades disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top:1rem;" class="tabla-historia-clinica">
            <h3>Historia Clínica</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Profesional a cargo</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $proximosTurnos = "SELECT a.fecha, s.nombre as nombreServicio, u.nombre as nombrePro from atenciones a
                    inner join usuarios u on a.id_pro = u.id
                    inner join servicios s on a.id_serv = s.id
                    where a.fecha >= now() and a.id_pro = $id";
                    $result = $conn->query($proximosTurnos);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(date("d-m-Y H:i:s", strtotime($row['fecha']))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombreServicio']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombrePro']) . "</td>";
                            echo "<td><a class=\"btn btn-info\" href='detalle-atencion.php'>Ver detalles</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td class='text-center' colspan='4'>No hay registros</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>