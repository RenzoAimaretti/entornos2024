<?php require_once '../shared/logica_gestionar_mascotas.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Gestionar Mascotas - Veterinaria San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-weight-bold text-dark mb-0">Gestión de Mascotas</h2>
                <p class="text-muted small mb-0">Listado general de pacientes</p>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaMascotas">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th>Nombre</th>
                                <th>Raza</th>
                                <th>Dueño</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mascotas)): ?>
                                <?php foreach ($mascotas as $mascota): ?>
                                    <tr>
                                        <td class="align-middle font-weight-bold text-dark">
                                            <?php echo htmlspecialchars($mascota['nombre']); ?>
                                        </td>
                                        <td class="align-middle text-muted">
                                            <?php echo htmlspecialchars($mascota['raza'] ?? 'Desconocida'); ?>
                                        </td>
                                        <td class="align-middle">
                                            <i class="fas fa-user-circle text-muted mr-1"></i>
                                            <?php echo htmlspecialchars($mascota['nombreDueño']); ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($mascota['fecha_mue']): ?>
                                                <span class="badge badge-danger px-2">Fallecido</span>
                                            <?php else: ?>
                                                <span class="badge badge-success px-2">Activo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo $mascota['id']; ?>"
                                                class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm">
                                                Ver Ficha
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
 -->
    <script>
        $(document).ready(function () {
            $('#tablaMascotas').DataTable({
                "pageLength": 10,
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2, 3] },
                    { "orderable": false, "targets": 4 }
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
<?php $conn->close(); ?>