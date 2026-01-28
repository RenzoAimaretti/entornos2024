<?php require_once '../shared/logica_pacientes_profesional.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pacientes - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0"><i class="fas fa-paw text-teal mr-2"></i> Mis Pacientes</h2>
                <p class="text-muted">Listado de mascotas atendidas históricamente</p>
            </div>
            <a href="dashboardProfesional.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table id="tablaPacientes" class="table table-hover w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 35%;">Nombre</th>
                                    <th style="width: 25%;">Raza</th>
                                    <th style="width: 20%;">Edad</th>
                                    <th style="width: 20%;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $edadTexto = "Desconocida";
                                    $edadSort = 0;
                                    if (!empty($row['fecha_nac'])) {
                                        $nac = new DateTime($row['fecha_nac']);
                                        $hoy = new DateTime();
                                        $diff = $hoy->diff($nac);
                                        if ($diff->y > 0) {
                                            $edadTexto = $diff->y . " años";
                                            $edadSort = $diff->y * 12;
                                        } elseif ($diff->m > 0) {
                                            $edadTexto = $diff->m . " meses";
                                            $edadSort = $diff->m;
                                        } else {
                                            $edadTexto = $diff->d . " días";
                                            $edadSort = 0;
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="nombre-container">
                                                <div class="avatar-paw"><i class="fas fa-dog"></i></div>
                                                <span
                                                    class="font-weight-bold text-dark"><?php echo htmlspecialchars($row['nombre']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['raza']); ?></td>
                                        <td data-order="<?php echo $edadSort; ?>">
                                            <span class="badge badge-light border text-muted"><?php echo $edadTexto; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo urlencode($row['id']); ?>"
                                                class="btn btn-sm btn-info rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-notes-medical mr-1"></i> Ver Ficha
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-dog fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aún no has atendido pacientes.</h5>
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
            if ($.fn.DataTable.isDataTable('#tablaPacientes')) {
                $('#tablaPacientes').DataTable().destroy();
            }

            $('#tablaPacientes').DataTable({
                "pageLength": 10,
                "autoWidth": false,
                "order": [[0, "asc"]],
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