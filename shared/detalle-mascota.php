<?php require_once '../shared/consultas_mascotas.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Detalle de <?php echo htmlspecialchars($mascota_nombre); ?> - San Antón</title>
    <?php require_once 'head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="card shadow-lg border-0 mb-4">
            <div class="profile-header text-center">
                <div class="profile-img-container mb-3">
                    <?php if (!empty($fotoMascota)): ?>
                        <img src="<?php echo htmlspecialchars($fotoMascota); ?>" alt="Foto">
                    <?php else: ?>
                        <i class="fas fa-paw fa-4x text-teal" style="opacity: 0.5;"></i>
                    <?php endif; ?>
                </div>
                <h2 class="font-weight-bold"><?php echo htmlspecialchars($mascota_nombre); ?></h2>
                <p class="mb-0 text-white-50"><?php echo htmlspecialchars($raza); ?></p>
            </div>

            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Nacimiento</small>
                        <h5 class="mt-1"><?php echo $fecha_nac ? date('d/m/Y', strtotime($fecha_nac)) : 'N/A'; ?></h5>
                    </div>
                    <div class="col-md-4 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Edad</small>
                        <h5 class="mt-1">
                            <?php
                            if ($fecha_nac) {
                                $diff = (new DateTime($fecha_nac))->diff(new DateTime($fecha_mue ?: 'now'));
                                echo $diff->y . " años";
                            } else
                                echo "-";
                            ?>
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase font-weight-bold">Estado</small>
                        <h5 class="mt-1">
                            <?php echo $fecha_mue ? "<span class='badge badge-danger'>Fallecido</span>" : "<span class='badge badge-success'>Activo</span>"; ?>
                        </h5>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-center">
                    <?php if ($esAdmin): ?>
                        <button type="button" class="btn btn-warning rounded-pill px-4 mr-2 font-weight-bold shadow-sm"
                            data-toggle="modal" data-target="#editarModal">
                            <i class="fas fa-edit mr-2"></i> Editar Datos
                        </button>
                    <?php endif; ?>

                    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                        <a href="../vistaAdmin/detalle-cliente.php?id=<?php echo $idPropietario; ?>"
                            class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-user mr-2"></i> Ver Dueño
                        </a>
                    <?php elseif ($_SESSION['usuario_tipo'] === 'especialista'): ?>
                        <a href="../vistaProfesional/pacientesMascotasProfesional.php"
                            class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    <?php else: ?>
                        <a href="../vistaCliente/mis-mascotas.php"
                            class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <ul class="nav nav-tabs card-header-tabs" id="mascotaTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="historia-tab" data-toggle="tab"
                            href="#historia"><i class="fas fa-file-medical-alt mr-2"></i> Historia Clínica</a></li>
                    <li class="nav-item"><a class="nav-link" id="vacunas-tab" data-toggle="tab" href="#vacunas"><i
                                class="fas fa-syringe mr-2"></i> Vacunas</a></li>
                    <li class="nav-item"><a class="nav-link" id="estetica-tab" data-toggle="tab" href="#estetica"><i
                                class="fas fa-cut mr-2"></i> Estética</a></li>
                    <li class="nav-item"><a class="nav-link" id="internacion-tab" data-toggle="tab"
                            href="#internacion"><i class="fas fa-procedures mr-2"></i> Hospitalizaciones</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="mascotaTabContent">
                    <?php
                    $tabs = [
                        'historia' => ['res' => $resH, 'cols' => ['Fecha', 'Servicio', 'Profesional', 'Detalle']],
                        'vacunas' => ['res' => $resVac, 'cols' => ['Fecha', 'Vacuna', 'Profesional', 'Detalle']],
                        'estetica' => ['res' => $resEst, 'cols' => ['Fecha', 'Servicio', 'Esteticista', 'Detalle']]
                    ];

                    foreach ($tabs as $id => $data): ?>
                        <div class="tab-pane fade <?php echo $id === 'historia' ? 'show active' : ''; ?>"
                            id="<?php echo $id; ?>" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover tab-datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <?php foreach ($data['cols'] as $col): ?>
                                                <?php if ($col === 'Detalle' && !($esAdmin || $esProfesional))
                                                    continue; ?>
                                                <th <?php echo $col === 'Detalle' ? 'class="text-center"' : ''; ?>>
                                                    <?php echo $col; ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $data['res']->fetch_assoc()):
                                            $btnHref = $esProfesional ? "detalle-atencionAP.php?id={$row['id']}" : "#"; ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                                <td><?php echo $row['servicio']; ?></td>
                                                <td><?php echo $row['profesional']; ?></td>
                                                <?php if ($esAdmin || $esProfesional): ?>
                                                    <td class="text-center"><a href="<?php echo $btnHref; ?>"
                                                            class="btn btn-sm btn-info rounded-pill px-3"><i
                                                                class="fas fa-eye"></i></a></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="tab-pane fade" id="internacion" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover tab-datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Ingreso</th>
                                        <th>Egreso</th>
                                        <th>Derivado por</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($h = $resHosp->fetch_assoc()):
                                        $egreso = ($h['estado'] == 'Activa') ? '<span class="badge badge-warning">En curso</span>' : date('d/m/Y', strtotime($h['fecha_egreso_real'])); ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($h['fecha_ingreso'])); ?></td>
                                            <td><?php echo $egreso; ?></td>
                                            <td><?php echo $h['profesional']; ?></td>
                                            <td><?php echo htmlspecialchars($h['motivo']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($esAdmin): ?>
        <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-teal text-white">
                        <h5 class="modal-title font-weight-bold">Editar Mascota</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="editar-mascota.php" method="POST" id="formEditarMascota">
                        <div class="modal-body p-4">
                            <input type="hidden" name="idMascota" value="<?php echo $idMascota; ?>">
                            <div class="form-group"><label class="font-weight-bold text-muted small">Nombre</label><input
                                    type="text" class="form-control" name="nombre"
                                    value="<?php echo htmlspecialchars($mascota_nombre); ?>" required></div>
                            <div class="form-group"><label class="font-weight-bold text-muted small">Raza /
                                    Especie</label><input type="text" class="form-control" name="raza"
                                    value="<?php echo htmlspecialchars($raza); ?>"></div>
                            <div class="form-group"><label class="font-weight-bold text-muted small">Fecha de
                                    Nacimiento</label><input type="date" class="form-control" name="fecha_nac"
                                    value="<?php echo $fecha_nac; ?>" max="<?php echo $hoy; ?>" required></div>
                            <div class="form-group"><label class="font-weight-bold text-danger small">Fecha de
                                    Fallecimiento</label><input type="date" class="form-control border-danger"
                                    name="fecha_mue" value="<?php echo $fecha_mue; ?>" max="<?php echo $hoy; ?>"></div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success font-weight-bold">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php require_once '../shared/scripts.php'; ?>

    <script>
        $(document).ready(function () {
            $('.tab-datatable').DataTable({
                "pageLength": 5,
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
                "order": [[0, "desc"]]
            });
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });
        });
    </script>
</body>

</html>