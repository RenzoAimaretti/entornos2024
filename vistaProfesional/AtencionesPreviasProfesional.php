<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}
$profesionalId = $_SESSION['usuario_id'];

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SELECT a.id, 
               DATE(a.fecha) AS fecha, 
               TIME(a.fecha)  AS hora, 
               m.id AS id_mascota, 
               m.nombre AS paciente, 
               s.nombre AS servicio, 
               a.detalle
        FROM atenciones a
        INNER JOIN mascotas m ON a.id_mascota = m.id
        INNER JOIN servicios s ON a.id_serv = s.id
        WHERE a.id_pro = ? AND a.fecha < NOW()
        ORDER BY a.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesionalId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Atenciones</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }
        .table thead th {
            background-color: #343a40;
            color: white;
            text-transform: uppercase;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .table a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .table a:hover {
            text-decoration: underline;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-title {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .alert {
            font-size: 1.2rem;
        }
        .container {
            max-width: 900px;
        }
    </style>
</head>
<body>

<?php require_once '../shared/navbar.php'; ?>

<div class="container my-5">
    <div class="card">
        <div class="card-header text-center">
            <h2 class="card-title">Historial de Atenciones</h2>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="text-center">
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Servicio</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($row['hora']); ?></td>
                                <td>
                                    <a href="../shared/detalle-mascota.php?idMascota=<?php echo urlencode($row['id_mascota']); ?>">
                                        <?php echo htmlspecialchars($row['paciente']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['servicio']); ?></td>
                                <td><?php echo htmlspecialchars($row['detalle']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    <strong>No hay atenciones registradas.</strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>