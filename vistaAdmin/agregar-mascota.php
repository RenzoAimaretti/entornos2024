<?php
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// --- LÓGICA DE REGISTRO ---
$registroExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_mascota = $_POST['nombre'];
    $raza = $_POST['raza'];
    $fecha_nac = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $fecha_mue = !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;
    $id_cliente = $_POST['id_cliente'];

    // Insertar en la base de datos
    $sqlInsert = "INSERT INTO mascotas (nombre, raza, fecha_nac, fecha_mue, id_cliente) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("ssssi", $nombre_mascota, $raza, $fecha_nac, $fecha_mue, $id_cliente);

    if ($stmt->execute()) {
        $registroExitoso = true;
    } else {
        $error = "Error al registrar: " . $conn->error;
    }
    $stmt->close();
}
// --------------------------

$query = "SELECT u.id, u.nombre FROM usuarios u WHERE u.id = $id";
$result = $conn->query($query);
$nombreCliente = "Cliente no encontrado";

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombreCliente = $row['nombre'];
}

$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Mascota - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../styles.css" rel="stylesheet">
    <style>
        .bg-teal {
            background-color: #00897b;
            color: white;
        }

        .text-teal {
            color: #00897b;
        }

        .input-group-text {
            background-color: #fff;
            border-right: none;
            color: #00897b;
            min-width: 45px;
            justify-content: center;
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        /* Efecto focus en el grupo */
        .input-group:focus-within .input-group-text {
            border-color: #80bdff;
            color: #0056b3;
        }

        .input-group:focus-within .form-control {
            border-color: #80bdff;
        }

        /* Estilo especial para input de muerte */
        .input-death .input-group-text {
            color: #dc3545;
        }

        .input-death:focus-within .input-group-text {
            color: #bd2130;
            border-color: #dc3545;
        }

        .input-death:focus-within .form-control {
            border-color: #dc3545;
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
                        <h3 class="font-weight-bold mb-0"><i class="fas fa-paw mr-2"></i> Nueva Mascota</h3>
                        <p class="mb-0 text-white-50">Registrando para:
                            <strong><?php echo htmlspecialchars($nombreCliente); ?></strong>
                        </p>
                    </div>

                    <div class="card-body p-5">

                        <form method="POST" id="formMascota">
                            <input type="hidden" name="id_cliente" value="<?php echo $id; ?>">

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Nombre de la Mascota</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                    </div>
                                    <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
                                        required placeholder="Ej: Firulais" autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Raza / Especie</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dog"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="raza" name="raza"
                                        placeholder="Ej: Caniche, Gato Siamés, Mestizo">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted">Fecha de Nacimiento</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="fecha_nacimiento"
                                            name="fecha_nacimiento" max="<?php echo $hoy; ?>">
                                    </div>
                                    <small class="form-text text-muted">Aproximada si no se sabe exacta.</small>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted text-danger">Fecha de
                                        Fallecimiento</label>
                                    <div class="input-group input-death">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-cross"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="fecha_muerte" name="fecha_muerte"
                                            max="<?php echo $hoy; ?>">
                                    </div>
                                    <small class="form-text text-muted">Solo si aplica.</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="gestionar-clientes.php" class="btn btn-outline-secondary px-4 rounded-pill">
                                    <i class="fas fa-arrow-left mr-2"></i> Cancelar
                                </a>
                                <button type="submit"
                                    class="btn btn-success px-5 rounded-pill font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Registrar Mascota
                                </button>
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
        const inputNacimiento = document.getElementById('fecha_nacimiento');
        const inputMuerte = document.getElementById('fecha_muerte');

        // UX: Actualizar mínimo de fecha de muerte
        inputNacimiento.addEventListener('change', function () {
            if (this.value) {
                inputMuerte.min = this.value;
            } else {
                inputMuerte.removeAttribute('min');
            }
        });

        // Validación Front-End
        document.getElementById('formMascota').addEventListener('submit', function (e) {
            const fechaNacStr = inputNacimiento.value;
            const fechaMueStr = inputMuerte.value;
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaNacStr) {
                const fechaNac = new Date(fechaNacStr);
                fechaNac.setHours(24, 0, 0, 0); // Ajuste zona horaria

                if (fechaNac > hoy) {
                    e.preventDefault();
                    Swal.fire('Error', 'La fecha de nacimiento no puede ser futura.', 'error');
                    return;
                }

                if (fechaMueStr) {
                    const fechaMue = new Date(fechaMueStr);
                    fechaMue.setHours(24, 0, 0, 0);

                    if (fechaMue < fechaNac) {
                        e.preventDefault();
                        Swal.fire('Error', 'La fecha de muerte no puede ser anterior al nacimiento.', 'error');
                        return;
                    }
                }
            }
        });

        // --- ALERTA DE ÉXITO (PHP) ---
        <?php if ($registroExitoso): ?>
            Swal.fire({
                title: '¡Mascota Registrada!',
                text: 'Se ha añadido correctamente al sistema.',
                icon: 'success',
                confirmButtonText: 'Ir a Gestionar Mascotas',
                confirmButtonColor: '#00897b',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'gestionar-mascotas.php';
                }
            });
        <?php endif; ?>
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>