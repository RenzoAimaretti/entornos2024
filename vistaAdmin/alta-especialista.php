<?php require_once '../shared/logica_alta_especialista.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Alta Especialistas - Veterinaria San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-teal text-center py-4 text-white">
                        <h3 class="mb-0 font-weight-bold"><i class="fas fa-user-md mr-2"></i> Nuevo Especialista</h3>
                        <p class="mb-0 text-white-50">Registrar un nuevo profesional en el sistema</p>
                    </div>
                    <div class="card-body p-5">
                        <form action="../shared/alta-especialista.php" method="POST" id="formAlta">
                            <h5 class="text-teal mb-3 border-bottom pb-2"><i class="fas fa-id-card mr-2"></i> Datos
                                Personales</h5>

                            <div class="form-group">
                                <label class="font-weight-bold small text-muted">Nombre Completo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span
                                            class="input-group-text bg-white border-right-0"><i
                                                class="fas fa-user text-teal"></i></span></div>
                                    <input type="text" class="form-control border-left-0" name="nombre" required
                                        placeholder="Ej: Dr. Juan Pérez">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Email Profesional</label>
                                        <div class="input-group" id="grupo-email">
                                            <div class="input-group-prepend"><span
                                                    class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-envelope text-teal"></i></span></div>
                                            <input type="email" class="form-control border-left-0" id="email"
                                                name="email" required placeholder="dr.juan@vet.com">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-email">Este correo
                                            electrónico ya está registrado.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Teléfono</label>
                                        <div class="input-group" id="grupo-tel">
                                            <div class="input-group-prepend"><span
                                                    class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-phone text-teal"></i></span></div>
                                            <input type="text" class="form-control border-left-0" id="tel" name="tel"
                                                required placeholder="Ej: 1156781234" maxlength="10">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-tel">El teléfono debe
                                            contener 10 números válidos.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Contraseña</label>
                                        <div class="input-group" id="grupo-pass">
                                            <div class="input-group-prepend"><span
                                                    class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-lock text-teal"></i></span></div>
                                            <input type="password" class="form-control border-left-0" id="password"
                                                name="password" required placeholder="******">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-pass">Debe tener 8
                                            caracteres, 1 mayúscula y 1 número.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Repetir Contraseña</label>
                                        <div class="input-group" id="grupo-repass">
                                            <div class="input-group-prepend"><span
                                                    class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-lock text-teal"></i></span></div>
                                            <input type="password" class="form-control border-left-0" id="repassword"
                                                name="repassword" required placeholder="******">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-repass">Las contraseñas
                                            no coinciden.</div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="text-teal mt-4 mb-3 border-bottom pb-2"><i
                                    class="fas fa-briefcase-medical mr-2"></i> Perfil Profesional</h5>

                            <div class="form-group">
                                <label class="font-weight-bold small text-muted">Especialidad</label>
                                <select class="form-control custom-select" name="esp" required>
                                    <option value="" disabled selected>Seleccione una especialidad</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <option value="<?= htmlspecialchars($esp['id']) ?>">


                                            <?= htmlspecialchars($esp['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                                <span class="text-teal font-weight-bold"><i class="far fa-clock mr-2"></i>
                                    Disponibilidad Horaria</span>
                                <button type="button"
                                    class="btn btn-sm btn-outline-success rounded-pill font-weight-bold"
                                    id="add-dia-btn"><i class="fas fa-plus mr-1"></i> Agregar Día</button>
                            </div>

                            <div id="dias-container">
                                <div class="text-center text-muted py-3 empty-state"><small>No se han asignado horarios
                                        aún.</small></div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="gestionar-especialistas.php"
                                    class="btn btn-outline-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-success px-5 font-weight-bold shadow-sm"
                                    id="btn-submit">Registrar Especialista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../shared/scripts.php'; ?>

    <script>
        $(document).ready(function () {
            $('#email').on('blur', function () {
                var email = $(this).val();
                if (email.length > 0) {
                    $.post(window.location.href, { check_email: true, email: email }, function (res) {
                        if (res.exists) {
                            $('#email').addClass('is-invalid');
                            $('#error-email').show();
                            $('#btn-submit').prop('disabled', true);
                        } else {
                            $('#email').removeClass('is-invalid');
                            $('#error-email').hide();
                            $('#btn-submit').prop('disabled', false);
                        }
                    }, 'json');
                }
            });

            $('#tel').on('input', function () {
                var valid = /^\d{10}$/.test($(this).val());
                $(this).toggleClass('is-invalid', !valid);
                $('#error-tel').toggle(!valid);
            });

            $('#password').on('input', function () {
                var valid = /^(?=.*[A-Z])(?=.*\d).{8,}$/.test($(this).val());
                $(this).toggleClass('is-invalid', !valid);
                $('#error-pass').toggle(!valid);
                $('#repassword').trigger('input');
            });

            $('#repassword').on('input', function () {
                var valid = $(this).val() === $('#password').val() && $(this).val().length > 0;
                $(this).toggleClass('is-invalid', !valid);
                $('#error-repass').toggle(!valid);
            });

            $('#formAlta').on('submit', function (e) {
                if ($('.is-invalid').length > 0 || !this.checkValidity()) {
                    e.preventDefault();
                    alert("Por favor corrija los errores antes de enviar.");
                }
            });

            $('#add-dia-btn').click(function () {
                $('#dias-container .empty-state').hide();
                let idx = $('.day-row').length;
                let dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                let opts = dias.map(d => `<option value="${d.substring(0, 3)}">${d}</option>`).join('');
                let horas = '';
                for (let i = 8; i <= 20; i++) { let h = (i < 10 ? '0' : '') + i; horas += `<option value="${h}:00">${h}:00</option>`; }

                let row = $(`
                    <div class="day-row shadow-sm mb-2 p-2 bg-white rounded border">
                        <div class="form-row align-items-center">
                            <div class="col-md-4"><select name="dias[${idx}][dia]" class="form-control" required><option disabled selected>Día</option>${opts}</select></div>
                            <div class="col-md-3"><select name="dias[${idx}][horaInicio]" class="form-control hora-ini" required><option disabled selected>Inicio</option>${horas}</select></div>
                            <div class="col-md-3"><select name="dias[${idx}][horaFin]" class="form-control hora-fin" required><option disabled selected>Fin</option>${horas}</select></div>
                            <div class="col-md-2 text-right"><button type="button" class="btn btn-outline-danger btn-sm rounded-circle rm-btn"><i class="fas fa-trash-alt"></i></button></div>
                        </div>
                    </div>
                `);

                row.find('.rm-btn').click(function () {
                    row.remove();
                    if ($('.day-row').length === 0) $('.empty-state').show();
                });

                row.find('select').change(function () {
                    let ini = row.find('.hora-ini').val();
                    let fin = row.find('.hora-fin').val();
                    if (ini && fin && fin <= ini) {
                        row.find('.hora-fin').addClass('is-invalid');
                        alert('La hora de fin debe ser mayor.');
                    } else {
                        row.find('.hora-fin').removeClass('is-invalid');
                    }
                });

                $('#dias-container').append(row);
            });
        });
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>