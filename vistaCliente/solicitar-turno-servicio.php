<?php require_once '../shared/logica_solicitar_turno_servicio.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno - San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if ($errorMascotaOcupada): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>¡Atención!</strong> Esta mascota ya tiene un turno
                para ese horario.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($service_id_selected && $servicio_seleccionado): ?>
            <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
                <h1 class="mb-0 font-weight-bold">Elegí tu Profesional</h1>
                <p class="mb-0 mt-1">Servicio: <strong><?= htmlspecialchars($servicio_seleccionado['nombre']) ?></strong>
                </p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="solicitar-turno-servicio.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Cambiar Servicio
                </a>
                <span class="badge badge-success px-3 py-2" style="font-size: 1rem;">
                    Precio estimado: $<?= number_format($servicio_seleccionado['precio'], 2) ?>
                </span>
            </div>

            <div class="row">
                <?php foreach ($profesionales as $profesional): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-profesional shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                        style="width: 50px; height: 50px; color: #00897b;">
                                        <i class="fas fa-user-md fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 font-weight-bold">
                                            <?= htmlspecialchars($profesional['nombre']) ?></h5>
                                        <small
                                            class="text-teal font-weight-bold"><?= htmlspecialchars($profesional['especialidad']) ?></small>
                                    </div>
                                </div>
                                <hr>
                                <ul class="list-unstyled mb-3 small text-secondary">
                                    <?php
                                    $horarios = $horariosPorProfesional[$profesional['id']] ?? [];
                                    foreach ($horarios as $h): ?>
                                        <li class="mb-1"><i
                                                class="fas fa-calendar-day mr-2 text-teal"></i><strong><?= $h['diaSem'] ?>:</strong>
                                            <?= $h['horaIni'] ?> - <?= $h['horaFin'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button"
                                    class="btn btn-outline-info btn-block rounded-pill font-weight-bold mostrar-formulario-btn"
                                    style="color:#00897b; border-color:#00897b;">Reservar Cita</button>

                                <form class="booking-form mt-3" data-id-pro="<?= $profesional['id'] ?>"
                                    data-pro-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                                    data-id-serv="<?= $servicio_seleccionado['id'] ?>"
                                    data-service-nombre="<?= htmlspecialchars($servicio_seleccionado['nombre']) ?>"
                                    data-service-precio="<?= $servicio_seleccionado['precio'] ?>" style="display:none;">
                                    <div class="form-group"><label class="small font-weight-bold">Fecha:</label><input
                                            type="date" class="form-control form-control-sm" name="fecha_turno"
                                            min="<?= date('Y-m-d') ?>" required></div>
                                    <div class="form-group"><label class="small font-weight-bold">Hora:</label><select
                                            class="form-control form-control-sm" name="hora_turno" required disabled>
                                            <option value="" disabled selected>Seleccione fecha</option>
                                        </select></div>
                                    <button type="button"
                                        class="btn btn-success btn-sm btn-block sacar-turno-btn font-weight-bold"
                                        disabled>Continuar</button>
                                    <button type="button"
                                        class="btn btn-link btn-sm btn-block text-secondary cancelar-turno-btn">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
                <h1 class="mb-0 font-weight-bold">Selecciona un Servicio</h1>
                <p class="mb-0 mt-1">¿Qué necesita tu mascota hoy?</p>
            </div>

            <div class="d-flex justify-content-start mb-4">
                <a href="solicitar-turno.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver atrás
                </a>
            </div>

            <table id="tablaServicios" class="table" style="width:100%">
                <thead>
                    <tr>
                        <th>Servicio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td class="p-0 border-0">
                                <a href="solicitar-turno-servicio.php?service_id=<?= $s['id'] ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center service-item py-3 shadow-sm mb-2">
                                    <div>
                                        <h5 class="mb-1 text-dark font-weight-bold"><?= htmlspecialchars($s['nombre']) ?></h5>
                                        <small class="text-muted">Clic para ver profesionales disponibles</small>
                                    </div>
                                    <span class="badge badge-success badge-pill px-3 py-2"
                                        style="font-size: 0.9rem;">$<?= number_format($s['precio'], 2) ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title font-weight-bold">Confirmar Turno</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border text-center mb-4">
                        <h5 class="text-teal mb-0" id="summary-servicio-nombre"></h5>
                        <small class="text-muted">Servicio Seleccionado</small>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="profesional_id" id="form-profesional-id">
                        <input type="hidden" name="fecha_turno" id="form-fecha">
                        <input type="hidden" name="hora_turno" id="form-hora">
                        <input type="hidden" name="id_serv" id="form-service-id">
                        <div class="form-group"><label class="font-weight-bold">Mascota:</label>
                            <select class="form-control" name="id_mascota" required>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= $m['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label class="font-weight-bold d-block">Modalidad:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active"><input type="radio" name="modalidad"
                                        value="Presencial" checked> Presencial</label>
                                <label class="btn btn-outline-secondary"><input type="radio" name="modalidad"
                                        value="A domicilio"> Domicilio</label>
                            </div>
                        </div>
                        <button type="submit"
                            class="btn btn-success btn-block btn-lg font-weight-bold mt-4 shadow-sm">CONFIRMAR
                            RESERVA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="turnoExitosoModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg text-center">
                <div class="modal-header bg-success text-white justify-content-center">
                    <h5 class="modal-title font-weight-bold">¡Reserva Exitosa!</h5>
                </div>
                <div class="modal-body p-5">
                    <div class="mb-4 text-success"><i class="fas fa-check-circle fa-5x"></i></div>
                    <h4 class="mb-3">¡Tu turno está confirmado!</h4>
                    <p class="text-muted">Hemos enviado los detalles a tu correo electrónico.</p>
                </div>
                <div class="modal-footer justify-content-center bg-light">
                    <a href="mis-turnos.php" class="btn btn-primary px-4">Ir a Mis Turnos</a>
                    <a href="solicitar-turno-profesional.php" class="btn btn-outline-secondary px-4">Nueva Reserva</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaServicios').DataTable({
                "pageLength": 10,
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
                "ordering": false,
                "info": false,
                "lengthChange": false
            });

            $('.mostrar-formulario-btn').on('click', function () {
                $(this).hide().closest('.card-body').find('.booking-form').slideDown();
            });

            $('.cancelar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                form.slideUp(() => form.closest('.card-body').find('.mostrar-formulario-btn').show());
            });

            $('.booking-form').on('change', 'input[type="date"]', function () {
                const form = $(this).closest('.booking-form');
                const proId = form.data('id-pro');
                const fecha = $(this).val();
                const horaSelect = form.find('select[name="hora_turno"]');
                if (fecha) {
                    $.post('verificar-turno-disponible-servicio.php', { id_pro: proId, fecha: fecha }, function (disponibles) {
                        horaSelect.empty().append('<option value="" disabled selected>Seleccione hora</option>');
                        disponibles.forEach(h => horaSelect.append(`<option value="${h}">${h.substring(0, 5)}</option>`));
                        horaSelect.prop('disabled', disponibles.length === 0);
                    }, 'json');
                }
            });

            $('.booking-form').on('change', 'select[name="hora_turno"]', function () {
                $(this).closest('.booking-form').find('.sacar-turno-btn').prop('disabled', false);
            });

            $('.sacar-turno-btn').on('click', function () {
                const form = $(this).closest('.booking-form');
                $('#summary-servicio-nombre').text(form.data('service-nombre'));
                $('#form-profesional-id').val(form.data('id-pro'));
                $('#form-fecha').val(form.find('input[name="fecha_turno"]').val());
                $('#form-hora').val(form.find('select[name="hora_turno"]').val());
                $('#form-service-id').val(form.data('id-serv'));
                $('#confirmacionModal').modal('show');
            });

            if (<?php echo json_encode($turnoExitoso); ?>) {
                $('#turnoExitosoModal').modal('show');
            }
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>