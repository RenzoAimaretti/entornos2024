<?php require_once '../shared/logica_solicitar_turno_profesional.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno - Por Profesional</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link href="../styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if ($errorMascotaOcupada): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>¡Atención!</strong> Esta mascota ya tiene un turno
                en ese horario.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
            <h1 class="mb-0 font-weight-bold">Elegí a tu Profesional</h1>
            <p class="mb-0 mt-1" style="opacity: 0.9;">Busca por nombre y selecciona el horario que mejor te convenga
            </p>
        </div>

        <div class="d-flex justify-content-start mb-4">
            <a href="solicitar-turno.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver a selección
            </a>
        </div>

        <div class="form-group mb-5 position-relative">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="filtroProfesionales" class="form-control search-input shadow-sm"
                placeholder="Escribe el nombre del veterinario...">
        </div>

        <table id="tablaProfesionales" class="table" style="width:100%; border:none; background:transparent;">
            <thead style="display:none;">
                <tr>
                    <th>Profesional</th>
                </tr>
            </thead>
            <tbody class="row m-0">
                <?php foreach ($profesionales as $profesional): ?>
                    <tr class="col-md-6 col-lg-4 mb-4 d-flex">
                        <td class="p-0 border-0 bg-transparent w-100">
                            <div class="card card-profesional shadow-sm h-100 w-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px; color: #00897b;">
                                                <i class="fas fa-user-md fa-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0 font-weight-bold text-dark">
                                                <?= htmlspecialchars($profesional['nombre']) ?>
                                            </h5>
                                            <small class="text-uppercase text-teal font-weight-bold"
                                                style="font-size: 0.75rem;"><?= htmlspecialchars($profesional['especialidad']) ?></small>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-muted mb-3" style="font-size: 0.9rem;"><i class="far fa-clock mr-1"></i>
                                        Horarios de atención:</h6>
                                    <ul class="list-unstyled mb-3 small text-secondary">
                                        <?php
                                        $diasAtencion = $horariosPorProfesional[$profesional['id']] ?? [];
                                        if (!empty($diasAtencion)): ?>
                                            <?php foreach ($diasAtencion as $horario): ?>
                                                <li class="mb-1">
                                                    <i class="fas fa-calendar-day mr-2 text-teal"></i>
                                                    <strong><?= $horario['diaSem'] ?>:</strong> <?= $horario['horaIni'] ?> -
                                                    <?= $horario['horaFin'] ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="text-muted font-italic">Sin horarios asignados.</li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="text-center mt-auto">
                                        <button type="button"
                                            class="btn btn-outline-info btn-block rounded-pill font-weight-bold mostrar-formulario-btn"
                                            style="color: #00897b; border-color: #00897b;">
                                            Sacar Turno
                                        </button>
                                    </div>
                                    <form class="booking-form mt-3" data-id-pro="<?= $profesional['id'] ?>"
                                        data-pro-nombre="<?= htmlspecialchars($profesional['nombre']) ?>"
                                        data-id-esp="<?= $profesional['id_esp'] ?>" style="display:none;">
                                        <h6 class="text-teal font-weight-bold mb-3">Reservar Cita</h6>
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Fecha:</label>
                                            <input type="date" class="form-control form-control-sm" name="fecha_turno"
                                                min="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="small font-weight-bold">Hora:</label>
                                            <select class="form-control form-control-sm" name="hora_turno" required
                                                disabled>
                                                <option value="" disabled selected>Seleccione fecha primero</option>
                                            </select>
                                            <small class="form-text text-danger" style="display:none;"></small>
                                        </div>
                                        <button type="button"
                                            class="btn btn-success btn-sm btn-block sacar-turno-btn font-weight-bold shadow-sm"
                                            disabled>Continuar</button>
                                        <button type="button"
                                            class="btn btn-link btn-sm btn-block text-secondary cancelar-turno-btn">Cancelar</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-teal text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i> Confirmar Turno
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border text-center mb-4">
                        <h5 class="text-teal mb-0" id="summary-profesional"></h5>
                        <small class="text-muted">Profesional Seleccionado</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><small class="text-muted d-block">Fecha:</small><strong id="summary-fecha"
                                style="font-size: 1.1rem;"></strong></div>
                        <div class="col-6 text-right"><small class="text-muted d-block">Hora:</small><strong
                                id="summary-hora" style="font-size: 1.1rem;"></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><small class="text-muted d-block">Mascota:</small><strong
                                id="summary-mascota" style="font-size: 1.1rem;">--</strong></div>
                        <div class="col-6 text-right"><small class="text-muted d-block">Modalidad:</small><strong
                                id="summary-modalidad" style="font-size: 1.1rem;">Presencial</strong></div>
                    </div>
                    <form method="POST" id="confirmacionForm">
                        <input type="hidden" name="profesional_id" id="form-profesional-id">
                        <input type="hidden" name="fecha_turno" id="form-fecha">
                        <input type="hidden" name="hora_turno" id="form-hora">
                        <input type="hidden" name="id_serv" id="form-service-id">
                        <div class="form-group">
                            <label class="font-weight-bold">¿Qué mascota vendrá?</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-white"><i
                                            class="fas fa-paw text-teal"></i></span></div>
                                <select class="form-control" name="id_mascota" required>
                                    <?php if (!empty($mascotas)): ?>
                                        <?php foreach ($mascotas as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= $m['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No tienes mascotas registradas.</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Servicio:</label>
                            <select class="form-control" name="id_serv" id="id_serv_modal" required>
                                <option value="">Cargando servicios...</option>
                            </select>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">Costo estimado:</small>
                                <strong class="text-success" id="summary-precio">--</strong>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold d-block mb-2">Modalidad de atención:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-secondary active"><input type="radio" name="modalidad"
                                        value="Presencial" checked><i class="fas fa-hospital mr-1"></i>
                                    Presencial</label>
                                <label class="btn btn-outline-secondary"><input type="radio" name="modalidad"
                                        value="A domicilio"><i class="fas fa-home mr-1"></i> A domicilio</label>
                            </div>
                        </div>
                        <button type="submit"
                            class="btn btn-success btn-block btn-lg mt-4 font-weight-bold shadow-sm">CONFIRMAR
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

    <script>
        $(document).ready(function () {
            const diasSemana = { 'Lun': 1, 'Mar': 2, 'Mie': 3, 'Jue': 4, 'Vie': 5, 'Sab': 6, 'Dom': 0 };
            const horariosProfesionales = <?php echo json_encode($horariosPorProfesional); ?>;
            const servicios = <?php echo json_encode($servicios); ?>;

            const serviciosPorEspecialidad = {};
            servicios.forEach(s => {
                if (!serviciosPorEspecialidad[s.id_esp]) serviciosPorEspecialidad[s.id_esp] = [];
                serviciosPorEspecialidad[s.id_esp].push(s);
            });

            const table = $('#tablaProfesionales').DataTable({
                "pageLength": 6,
                "dom": 'tp',
                "language": { "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
                "ordering": false,
                "info": false,
                "lengthChange": false
            });

            $('#filtroProfesionales').on('input', function () {
                table.search(this.value).draw();
            });

            $(document).on('click', '.mostrar-formulario-btn', function () {
                $(this).hide().closest('.card-body').find('.booking-form').slideDown();
            });

            $(document).on('click', '.cancelar-turno-btn', function () {
                const form = $(this).closest('.booking-form');
                form.slideUp(function () {
                    form.closest('.card-body').find('.mostrar-formulario-btn').show();
                    form.find('input[type="date"]').val('');
                    form.find('select[name="hora_turno"]').empty().prop('disabled', true).append('<option value="" disabled selected>Seleccione fecha primero</option>');
                    form.find('.sacar-turno-btn').prop('disabled', true);
                    form.find('.form-text').hide();
                });
            });

            $(document).on('change', '.booking-form input[type="date"]', function () {
                const form = $(this).closest('.booking-form');
                const proId = form.data('id-pro');
                const fecha = $(this).val();
                const horaSelect = form.find('select[name="hora_turno"]');
                const errorSpan = form.find('.form-text');

                horaSelect.prop('disabled', true).empty().append('<option value="" disabled selected>Cargando...</option>');
                form.find('.sacar-turno-btn').prop('disabled', true);
                errorSpan.hide();

                if (fecha) {
                    const fechaObj = new Date(fecha.replace(/-/g, '/'));
                    const diaStr = Object.keys(diasSemana).find(key => diasSemana[key] === fechaObj.getDay());
                    const horariosPro = horariosProfesionales[proId];

                    if (horariosPro && horariosPro.find(h => h.diaSem === diaStr)) {
                        $.ajax({
                            url: 'verificar-turno-disponible.php',
                            method: 'POST',
                            dataType: 'json',
                            data: { id_pro: proId, fecha: fecha },
                            success: function (disponibles) {
                                horaSelect.empty().append('<option value="" disabled selected>Seleccione hora</option>');
                                if (disponibles.length > 0) {
                                    disponibles.forEach(h => horaSelect.append(`<option value="${h}">${h.substring(0, 5)}</option>`));
                                    horaSelect.prop('disabled', false);
                                } else {
                                    errorSpan.text('No hay horarios disponibles.').show();
                                }
                            }
                        });
                    } else {
                        horaSelect.empty().append('<option value="" disabled selected>Día no laboral</option>');
                        errorSpan.text('El profesional no atiende este día.').show();
                    }
                }
            });

            $(document).on('change', '.booking-form select[name="hora_turno"]', function () {
                $(this).closest('.booking-form').find('.sacar-turno-btn').prop('disabled', false);
            });

            $(document).on('click', '.sacar-turno-btn', function () {
                const form = $(this).closest('.booking-form');
                const idEsp = form.data('id-esp');

                $('#summary-profesional').text(form.data('pro-nombre'));
                $('#summary-fecha').text(form.find('input[name="fecha_turno"]').val().split('-').reverse().join('/'));
                $('#summary-hora').text(form.find('select[name="hora_turno"]').val());

                $('#form-profesional-id').val(form.data('id-pro'));
                $('#form-fecha').val(form.find('input[name="fecha_turno"]').val());
                $('#form-hora').val(form.find('select[name="hora_turno"]').val());

                const selectServ = $('#id_serv_modal').empty().append('<option value="">Selecciona un servicio</option>');
                (serviciosPorEspecialidad[idEsp] || []).forEach(s => {
                    selectServ.append(`<option value="${s.id}" data-precio="${s.precio}">${s.nombre}</option>`);
                });
                $('#summary-precio').text('--');

                $('#confirmacionModal').modal('show');
                $('#summary-mascota').text('--');
                $('#summary-modalidad').text('Presencial');
            });

            $('#id_serv_modal').on('change', function () {
                const precio = $(this).find(':selected').data('precio');
                $('#summary-precio').text(precio ? `$${precio}` : '--');
                $('#form-service-id').val($(this).val());
            });

            $('#confirmacionForm select[name="id_mascota"]').on('change', function () {
                const nombreMascota = $(this).find(':selected').text();
                $('#summary-mascota').text(nombreMascota);
            });

            $('#confirmacionForm input[name="modalidad"]').on('change', function () {
                const modalidad = $(this).val();
                $('#summary-modalidad').text(modalidad);
            });

            if (<?php echo json_encode($turnoExitoso); ?>) {
                $('#turnoExitosoModal').modal('show');
            }
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>