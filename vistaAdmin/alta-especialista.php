<?php
session_start();
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

// --- LÓGICA AJAX PARA VERIFICAR EMAIL EXISTENTE (NUEVO) ---
if (isset($_POST['check_email'])) {
    $emailToCheck = $_POST['email'];
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $emailToCheck);
    $stmt->execute();
    $stmt->store_result();
    // Devolvemos JSON: true si existe, false si no
    echo json_encode(['exists' => $stmt->num_rows > 0]);
    $stmt->close();
    exit(); // Terminamos la ejecución aquí para no cargar el HTML
}
// ----------------------------------------------------------

$query = "select id, nombre from especialidad";
$result = $conn->query($query);
$especialidades = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $especialidades[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Especialistas - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../styles.css" rel="stylesheet">
    <style>
        .bg-teal {
            background-color: #00897b;
            color: white;
        }

        .text-teal {
            color: #00897b;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #555;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
        }

        .day-row {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }

        /* AJUSTE PARA MOSTRAR ERROR EN INPUT GROUP */
        .input-group.is-invalid~.invalid-feedback {
            display: block;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-teal text-center py-4">
                        <h3 class="mb-0 font-weight-bold text-white"><i class="fas fa-user-md mr-2"></i> Nuevo
                            Especialista</h3>
                        <p class="mb-0 text-white-50">Registrar un nuevo profesional en el sistema</p>
                    </div>

                    <div class="card-body p-5">
                        <form action="../shared/alta-especialista.php" method="POST" id="formAlta">

                            <div class="form-section-title"><i class="fas fa-id-card mr-2 text-teal"></i> Datos
                                Personales</div>

                            <div class="form-group">
                                <label class="font-weight-bold small text-muted">Nombre Completo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i
                                                class="fas fa-user text-teal"></i></span>
                                    </div>
                                    <input type="text" class="form-control border-left-0" id="nombre" name="nombre"
                                        required placeholder="Ej: Dr. Juan Pérez">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Email Profesional</label>
                                        <div class="input-group" id="grupo-email">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-envelope text-teal"></i></span>
                                            </div>
                                            <input type="email" class="form-control border-left-0" id="email"
                                                name="email" required placeholder="dr.juan@vet.com">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-email">
                                            Este correo electrónico ya está registrado.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Teléfono</label>
                                        <div class="input-group" id="grupo-tel">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-phone text-teal"></i></span>
                                            </div>
                                            <input type="text" class="form-control border-left-0" id="tel" name="tel"
                                                required placeholder="Ej: 1156781234" maxlength="10">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-tel">
                                            El teléfono debe contener 10 números válidos.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Contraseña</label>
                                        <div class="input-group" id="grupo-pass">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-lock text-teal"></i></span>
                                            </div>
                                            <input type="password" class="form-control border-left-0" id="password"
                                                name="password" required placeholder="******">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-pass">
                                            Debe tener 8 caracteres, 1 mayúscula y 1 número.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Repetir Contraseña</label>
                                        <div class="input-group" id="grupo-repass">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i
                                                        class="fas fa-lock text-teal"></i></span>
                                            </div>
                                            <input type="password" class="form-control border-left-0" id="repassword"
                                                name="repassword" required placeholder="******">
                                        </div>
                                        <div class="invalid-feedback font-weight-bold" id="error-repass">
                                            Las contraseñas no coinciden.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-title mt-4"><i class="fas fa-briefcase-medical mr-2 text-teal"></i>
                                Perfil Profesional</div>

                            <div class="form-group">
                                <label class="font-weight-bold small text-muted">Especialidad</label>
                                <select class="form-control custom-select" id="esp" name="esp" required>
                                    <option value="" disabled selected>Seleccione una especialidad</option>
                                    <?php foreach ($especialidades as $especialidad): ?>
                                        <option value="<?php echo htmlspecialchars($especialidad['id']); ?>">
                                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-section-title mt-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="far fa-clock mr-2 text-teal"></i> Disponibilidad Horaria</span>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-success rounded-pill font-weight-bold"
                                        id="add-dia-btn">
                                        <i class="fas fa-plus mr-1"></i> Agregar Día
                                    </button>
                                </div>
                            </div>

                            <div id="dias-container">
                                <div class="text-center text-muted py-3 empty-state">
                                    <small>No se han asignado horarios aún. Haga clic en "Agregar Día".</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="gestionar-especialistas.php"
                                    class="btn btn-outline-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-success px-5 font-weight-bold shadow-sm"
                                    id="btn-submit">Registrar
                                    Especialista</button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {

            // --- 1. VALIDACIÓN AJAX EMAIL (NUEVO) ---
            $('#email').on('blur', function () {
                var email = $(this).val();
                var $input = $(this);

                if (email.length > 0) {
                    $.ajax({
                        url: window.location.href, // Envía a este mismo archivo PHP
                        type: 'POST',
                        data: { check_email: true, email: email },
                        dataType: 'json',
                        success: function (response) {
                            if (response.exists) {
                                $input.addClass('is-invalid');
                                $('#grupo-email').addClass('is-invalid');
                                $('#error-email').show();
                                $('#btn-submit').prop('disabled', true); // Bloquear botón
                            } else {
                                $input.removeClass('is-invalid');
                                $('#grupo-email').removeClass('is-invalid');
                                $('#error-email').hide();
                                $('#btn-submit').prop('disabled', false); // Habilitar botón
                            }
                        },
                        error: function () {
                            console.log('Error al verificar el email.');
                        }
                    });
                }
            });

            // --- 2. VALIDACIÓN DE TELÉFONO (10 dígitos) ---
            $('#tel').on('input', function () {
                var valor = $(this).val();
                var esValido = /^\d{10}$/.test(valor);

                if (!esValido) {
                    $(this).addClass('is-invalid');
                    $('#grupo-tel').addClass('is-invalid');
                    $('#error-tel').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $('#grupo-tel').removeClass('is-invalid');
                    $('#error-tel').hide();
                }
            });

            // --- 3. VALIDACIÓN PASSWORD ---
            $('#password').on('input', function () {
                var pass = $(this).val();
                var regexPass = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

                if (!regexPass.test(pass) && pass.length > 0) {
                    $(this).addClass('is-invalid');
                    $('#grupo-pass').addClass('is-invalid');
                    $('#error-pass').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $('#grupo-pass').removeClass('is-invalid');
                    $('#error-pass').hide();
                }
                $('#repassword').trigger('input');
            });

            // --- 4. VALIDACIÓN REPETIR PASSWORD ---
            $('#repassword').on('input', function () {
                var pass = $('#password').val();
                var repass = $(this).val();

                if (pass !== repass && repass.length > 0) {
                    $(this).addClass('is-invalid');
                    $('#grupo-repass').addClass('is-invalid');
                    $('#error-repass').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $('#grupo-repass').removeClass('is-invalid');
                    $('#error-repass').hide();
                }
            });

            // --- 5. PREVENIR ENVÍO CON ERRORES ---
            $('#formAlta').on('submit', function (e) {
                var valid = true;

                // Verificar email (clase is-invalid puesta por Ajax)
                if ($('#email').hasClass('is-invalid')) valid = false;

                // Verificar teléfono
                if (!/^\d{10}$/.test($('#tel').val())) {
                    $('#tel').addClass('is-invalid');
                    $('#grupo-tel').addClass('is-invalid');
                    $('#error-tel').show();
                    valid = false;
                }

                // Verificar password
                const passVal = $('#password').val();
                if (!/^(?=.*[A-Z])(?=.*\d).{8,}$/.test(passVal)) {
                    $('#password').addClass('is-invalid');
                    $('#grupo-pass').addClass('is-invalid');
                    $('#error-pass').show();
                    valid = false;
                }

                // Verificar repassword
                if (passVal !== $('#repassword').val()) {
                    $('#repassword').addClass('is-invalid');
                    $('#grupo-repass').addClass('is-invalid');
                    $('#error-repass').show();
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    alert("Por favor corrija los errores marcados en rojo antes de enviar.");
                }
            });
        });

        // --- LÓGICA DE DÍAS Y HORARIOS (Existente) ---
        document.getElementById('add-dia-btn').addEventListener('click', function () {
            const container = document.getElementById('dias-container');
            const emptyState = container.querySelector('.empty-state');
            if (emptyState) emptyState.style.display = 'none';

            const index = container.querySelectorAll('.day-row').length;
            const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

            const div = document.createElement('div');
            div.className = 'day-row shadow-sm';
            div.innerHTML = `
                <div class="form-row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-day text-muted"></i></span>
                            </div>
                            <select name="dias[${index}][dia]" class="form-control" required>
                                <option value="" disabled selected>Día de la semana</option>
                                ${dias.map(d => `<option value="${d.substring(0, 3)}">${d}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="dias[${index}][horaInicio]" class="form-control form-control-sm hora-inicio" required>
                            <option value="" disabled selected>Inicio</option>
                            ${generarOpcionesHoras()}
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="dias[${index}][horaFin]" class="form-control form-control-sm hora-fin" required>
                            <option value="" disabled selected>Fin</option>
                            ${generarOpcionesHoras()}
                        </select>
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-dia-btn" title="Eliminar horario">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(div);

            div.querySelector('.remove-dia-btn').onclick = function () {
                div.remove();
                if (container.querySelectorAll('.day-row').length === 0) {
                    if (emptyState) emptyState.style.display = 'block';
                }
            };

            const horaInicio = div.querySelector('.hora-inicio');
            const horaFin = div.querySelector('.hora-fin');

            function validarHoras() {
                if (horaInicio.value && horaFin.value) {
                    if (horaFin.value <= horaInicio.value) {
                        horaFin.setCustomValidity('La hora de fin debe ser mayor.');
                        horaFin.classList.add('is-invalid');
                    } else {
                        horaFin.setCustomValidity('');
                        horaFin.classList.remove('is-invalid');
                    }
                }
            }
            horaInicio.addEventListener('change', validarHoras);
            horaFin.addEventListener('change', validarHoras);
        });

        function generarOpcionesHoras() {
            let options = '';
            for (let i = 8; i <= 20; i++) {
                let hour = i < 10 ? '0' + i : i;
                options += `<option value="${hour}:00">${hour}:00</option>`;
            }
            return options;
        }
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>