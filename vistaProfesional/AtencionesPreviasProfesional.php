<?php
session_start();

// 1. Seguridad
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

// 2. Consulta
$sql = "SELECT a.id, 
               a.fecha, 
               m.id AS id_mascota, 
               m.nombre AS paciente, 
               m.raza,
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
    <title>Historial de Atenciones - San Antón</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">

    <style>
        .text-teal { color: #00897b; }
        .page-item.active .page-link { background-color: #00897b; border-color: #00897b; }
        
        /* --- ESTILO DE FLECHAS ORIGINALES CON DISTANCIA NORMAL --- */
        
        /* Restauramos el espaciado normal de la cabecera */
        table.dataTable thead th {
            padding-right: 30px !important;
            position: relative;
            white-space: nowrap;
        }

        /* Posicionamiento de las dos flechas originales de DataTables */
        table.dataTable thead .sorting:before, 
        table.dataTable thead .sorting_asc:before, 
        table.dataTable thead .sorting_desc:before,
        table.dataTable thead .sorting:after, 
        table.dataTable thead .sorting_asc:after, 
        table.dataTable thead .sorting_desc:after {
            right: 10px !important; /* Distancia estándar */
        }

        /* Quitamos las flechas de la columna Observaciones (índice 3) */
        table.dataTable thead .sorting_disabled:before, 
        table.dataTable thead .sorting_disabled:after {
            display: none !important;
        }

        #tablaHistorial { margin-top: 20px !important; margin-bottom: 20px !important; }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-teal mr-2"></i> Historial Médico</h2>
                <p class="text-muted">Registro completo de atenciones realizadas</p>
            </div>
            <a href="dashboardProfesional.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table id="tablaHistorial" class="table table-hover" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Servicio</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $fechaF = date("d/m/Y", strtotime($row['fecha']));
                                    $horaF = date("H:i", strtotime($row['fecha']));
                                    ?>
                                    <tr>
                                        <td data-order="<?php echo strtotime($row['fecha']); ?>">
                                            <span class="font-weight-bold"><?php echo $fechaF; ?></span>
                                            <small class="d-block text-muted"><?php echo $horaF; ?> hs</small>
                                        </td>
                                        <td>
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo $row['id_mascota']; ?>" class="text-dark font-weight-bold">
                                                <?php echo htmlspecialchars($row['paciente']); ?>
                                            </a>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($row['raza']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($row['servicio']); ?></span>
                                        </td>
                                        <td class="text-muted" style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['detalle']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-medical-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron atenciones pasadas.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('#tablaHistorial')) {
                $('#tablaHistorial').DataTable().destroy();
            }

            $('#tablaHistorial').DataTable({
                "pageLength": 10,
                "autoWidth": true, // Restauramos el cálculo de anchos de Bootstrap
                "order": [[0, "desc"]], 
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2] }, 
                    { "orderable": false, "targets": 3 }         
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>
</html>