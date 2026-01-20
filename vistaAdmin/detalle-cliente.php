<?php
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          where u.id = $id";

$result = $conn->query($query);

$queryMascotas = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue 
                  FROM mascotas m 
                  WHERE m.id_cliente = $id";

$resultMascotas = $conn->query($queryMascotas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <!-- Título -->
    <div class="container my-4">
        <?php
        $nombre = "Cliente no encontrado";
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $email = $row['email'];
            $direccion = $row['direccion'];
            $telefono = $row['telefono'];
        }
        ?>
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d; width: 100%;">Detalles de
            <?php echo htmlspecialchars($nombre); ?></h2>
    </div>
    <div class="d-flex justify-content-center">
        <div class="card text-center" style="width:50rem;">
            <div class="card-header">
                <h2>Detalle del Cliente</h2>
            </div>
            <div class="card-body">
                <?php
                if ($result && $result->num_rows > 0) {
                    ?>
                    <table class="table table-bordered">
                        <tr>
                            <th>Nombre</th>
                            <td><?php echo htmlspecialchars($nombre ?? 'No definido'); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($email ?? 'No definido'); ?></td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td><?php echo htmlspecialchars($direccion ?? 'No definido'); ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td><?php echo htmlspecialchars($telefono ?? 'No definido'); ?></td>
                        </tr>
                    </table>
                    <!-- Botón para abrir el modal -->
                    <button class='btn btn-primary' style="margin: 1rem;" data-toggle="modal"
                        data-target="#editarModal">Editar información</button>

                    <!-- Modal -->
                    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editarModalLabel">Editar Información del Cliente</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" action="../shared/editar-cliente.php">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" name="direccion"
                                                value="<?php echo htmlspecialchars($direccion ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono"
                                                value="<?php echo htmlspecialchars($telefono ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <table class="table table-striped">
                        <thead class="thead">
                            <tr>
                                <th>
                                    Nombre
                                </th>
                                <th>
                                    Raza
                                </th>
                                <th>
                                    Fecha de Nacimiento
                                </th>
                                <th>
                                    Fecha de Muerte
                                </th>
                                <th>
                                    Acciones
                                </th>
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            if ($resultMascotas && $resultMascotas->num_rows > 0) {
                                while ($mascota = $resultMascotas->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($mascota['mascota_nombre'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($mascota['raza'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($mascota['fecha_nac'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($mascota['fecha_mue'] ?? '-') . "</td>";
                                    echo "<td> <a class='btn btn-warning' href='../shared/detalle-mascota.php?idMascota=" . htmlspecialchars($mascota['id']) . "'>Ver Detalles</a> </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr>";
                                echo "<td colspan='6' class='text-center'>No posee mascotas.</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                        <!-- Agregar para agregar mascota?? -->


                    </table>
                    <a class='btn btn-primary' href="agregar-mascota.php?id=<?php echo $id ?>" style="margin: 1rem;">Agregar
                        mascota</a>



                    <?php
                } else {
                    echo "Cliente no encontrado";
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Bootstrap Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>


</html>