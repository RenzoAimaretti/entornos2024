<?php require_once '../shared/consultas_detalle_cliente.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Detalle Cliente - San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>
<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if ($cliente): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-4">
                    <li class="breadcrumb-item"><a href="gestionar-clientes.php" class="text-teal">Clientes</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($cliente['nombre']); ?></li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 profile-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="avatar-initial shadow-sm">
                                <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?></div>
                            <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($cliente['nombre']); ?></h4>
                            <p class="text-muted small mb-4">Cliente San Antón</p>
                            <ul class="list-group list-group-flush text-left mb-4">
                                <li class="list-group-item px-0 border-0 py-1"><i
                                        class="fas fa-envelope text-teal mr-2"></i><?php echo htmlspecialchars($cliente['email']); ?>
                                </li>
                                <li class="list-group-item px-0 border-0 py-1"><i
                                        class="fas fa-phone text-teal mr-2"></i><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?>
                                </li>
                                <li class="list-group-item px-0 border-0 py-1"><i
                                        class="fas fa-map-marker-alt text-teal mr-2"></i><?php echo htmlspecialchars($cliente['direccion'] ?? 'N/A'); ?>
                                </li>
                            </ul>
                            <button class="btn btn-outline-primary btn-block rounded-pill font-weight-bold"
                                data-toggle="modal" data-target="#editarModal"><i class="fas fa-user-edit mr-2"></i> Editar
                                Datos</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div
                            class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="font-weight-bold text-secondary mb-0"><i class="fas fa-paw mr-2 text-teal"></i>
                                Mascotas Asociadas</h5>
                            <a href="agregar-mascota.php?id=<?php echo $id ?>"
                                class="btn btn-sm btn-success rounded-pill font-weight-bold shadow-sm"><i
                                    class="fas fa-plus mr-1"></i> Nueva Mascota</a>
                        </div>
                        <div class="card-body">
                            <?php if ($resultMascotas && $resultMascotas->num_rows > 0): ?>
                                <table id="tablaMascotas" class="table w-100">
                                    <thead>
                                        <tr>
                                            <th>Información Mascota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($mascota = $resultMascotas->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($mascota['foto'])): ?>
                                                                <img src="<?php echo htmlspecialchars($mascota['foto']); ?>"
                                                                    alt="avatar de mascota" class="pet-avatar mr-3">
                                                            <?php else: ?>
                                                                <div
                                                                    class="pet-avatar d-flex align-items-center justify-content-center mr-3 bg-light text-muted">
                                                                    <i class="fas fa-dog"></i></div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold text-dark">
                                                                    <?php echo htmlspecialchars($mascota['mascota_nombre']); ?></h6>
                                                                <small
                                                                    class="text-muted"><?php echo htmlspecialchars($mascota['raza'] ?? 'Raza desconocida'); ?></small>
                                                                <?php if ($mascota['fecha_mue']): ?><span
                                                                        class="badge badge-danger ml-2">Fallecido</span><?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <small class="d-block text-muted mb-1"
                                                                data-order="<?php echo strtotime($mascota['fecha_nac']); ?>">Nac:
                                                                <?php echo date('d/m/Y', strtotime($mascota['fecha_nac'])); ?></small>
                                                            <a href="../shared/detalle-mascota.php?idMascota=<?php echo $mascota['id']; ?>"
                                                                class="btn btn-sm btn-info rounded-pill px-3 shadow-sm">Ver
                                                                Historial</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-dog fa-3x text-muted mb-3 opacity-50"></i>
                                    <p class="text-muted">Este cliente no tiene mascotas registradas.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-teal text-white">
                            <h5 class="modal-title font-weight-bold">Editar Información de Cliente</h5>
                            <button type="button" class="close text-white"
                                data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <form method="POST" action="../shared/editar-cliente.php">
                            <div class="modal-body p-4">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Dirección</label>
                                    <input type="text" class="form-control" name="direccion"
                                        value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono"
                                        value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-danger text-center shadow-sm">
                <h4>Cliente no encontrado</h4>
                <a href="gestionar-clientes.php" class="btn btn-outline-danger mt-3">Volver al listado</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script> -->

    <script>
        $(document).ready(function () {
            $('#tablaMascotas').DataTable({
                "pageLength": 5,
                "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "Todos"]],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "ordering": true
            });
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>
</html>