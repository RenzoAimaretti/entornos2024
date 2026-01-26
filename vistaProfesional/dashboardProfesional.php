<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

$nombre = $_SESSION['usuario_nombre'];
$profesional_id = $_SESSION['usuario_id'];

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


$hoy = date('Y-m-d');
$query = "SELECT 
            a.id,
            DATE_FORMAT(a.fecha, '%H:%i') AS hora,
            m.nombre AS nombre_mascota,
            s.nombre AS nombre_servicio
          FROM atenciones a
          INNER JOIN mascotas m ON a.id_mascota = m.id
          INNER JOIN servicios s ON a.id_serv = s.id
          WHERE a.id_pro = $profesional_id
            AND DATE(a.fecha) = '$hoy'
          ORDER BY a.fecha ASC";


$result = $conn->query($query);

$turnos_hoy = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $turnos_hoy[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Profesional</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>
    <div class="container my-4">
        <h2 class="text-center mb-4">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h2>
        <div class="row">

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">Turnos de Hoy (<?php echo date('d/m/Y'); ?>)</div>
                    <div class="card-body">
                        <?php if (count($turnos_hoy) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Hora</th>
                                            <th>Mascota</th>
                                            <th>Servicio</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($turnos_hoy as $turno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($turno['hora']); ?></td>
                                                <td><?php echo htmlspecialchars($turno['nombre_mascota']); ?></td>
                                                <td><?php echo htmlspecialchars($turno['nombre_servicio']); ?></td>
                                                <td>
                                                    <a href="editarAtencionProfesional.php?id=<?php echo $turno['id']; ?>"
                                                        class="btn btn-primary btn-sm">
                                                        Ingresar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No tienes turnos programados para hoy.</p>
                        <?php endif; ?>
                        <a href="misTurnosProfesional.php" class="btn btn-primary btn-block mt-3">Ver todos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">Historial de Atenciones</div>
                    <div class="card-body">
                        <p>Consulta el historial de atenciones realizadas.</p>
                        <a href="atencionesPreviasProfesional.php" class="btn btn-info btn-block">Ver historial</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white">Pacientes Atendidos</div>
                    <div class="card-body">
                        <p>Accede a la lista de mascotas que has atendido.</p>
                        <a href="pacientesMascotasProfesional.php" class="btn btn-warning btn-block">Ver pacientes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>