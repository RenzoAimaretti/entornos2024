<?php require_once '../shared/logica_atenciones_previas.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Historial de Atenciones - San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0"><i class="fas fa-history text-teal mr-2"></i> Historial
                    Médico</h2>
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
                        <table id="tablaHistorial" class="table table-hover w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 15%;">Fecha</th>
                                    <th style="width: 20%;">Paciente</th>
                                    <th style="width: 20%;">Servicio</th>
                                    <th style="width: 45%; text-align: left !important; padding-left: 15px;">Observaciones
                                    </th>
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
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo $row['id_mascota']; ?>"
                                                class="text-dark font-weight-bold">
                                                <?php echo htmlspecialchars($row['paciente']); ?>
                                            </a>
                                            <small
                                                class="d-block text-muted"><?php echo htmlspecialchars($row['raza']); ?></small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($row['servicio']); ?></span>
                                        </td>
                                        <td style="text-align: left !important; white-space: pre-wrap; padding-left: 15px;"
                                            class="text-muted"><?php echo htmlspecialchars(trim($row['detalle'])); ?></td>
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

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script> -->

    <script>
        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('#tablaHistorial')) {
                $('#tablaHistorial').DataTable().destroy();
            }

            $('#tablaHistorial').DataTable({
                "pageLength": 10,
                "autoWidth": false,
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