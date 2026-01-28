<?php
session_start();
$id = $_GET['id'] ?? 0;

if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error)
    die("Error: " . $conn->connect_error);

$registroExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO mascotas (nombre, raza, fecha_nac, fecha_mue, id_cliente) VALUES (?, ?, ?, ?, ?)");
    $nac = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $mue = !empty($_POST['fecha_muerte']) ? $_POST['fecha_muerte'] : null;
    $stmt->bind_param("ssssi", $_POST['nombre'], $_POST['raza'], $nac, $mue, $_POST['id_cliente']);

    if ($stmt->execute())
        $registroExitoso = true;
    $stmt->close();
}

$cliente = $conn->query("SELECT nombre FROM usuarios WHERE id = " . intval($id))->fetch_assoc();
$nombreCliente = $cliente ? $cliente['nombre'] : "Cliente no encontrado";
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
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-teal text-center py-4 text-white">
                        <h3 class="font-weight-bold mb-0"><i class="fas fa-paw mr-2"></i> Nueva Mascota</h3>
                        <p class="mb-0 text-white-50">Registrando para:
                            <strong><?php echo htmlspecialchars($nombreCliente); ?></strong>
                        </p>
                    </div>
                    <div class="card-body p-5">
                        <form method="POST" id="formMascota">
                            <input type="hidden" name="id_cliente" value="<?php echo $id; ?>">

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Nombre</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-signature"></i></span></div>
                                    <input type="text" class="form-control form-control-lg" name="nombre" required
                                        placeholder="Ej: Firulais" autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Raza / Especie</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dog"></i></span></div>
                                    <input type="text" class="form-control" name="raza" placeholder="Ej: Caniche, Gato">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted">Nacimiento</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-birthday-cake"></i></span></div>
                                        <input type="date" class="form-control" id="fecha_nacimiento"
                                            name="fecha_nacimiento" max="<?php echo $hoy; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted text-danger">Fallecimiento</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-cross"></i></span></div>
                                        <input type="date" class="form-control" id="fecha_muerte" name="fecha_muerte"
                                            max="<?php echo $hoy; ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="gestionar-clientes.php" class="btn btn-outline-secondary px-4 rounded-pill"><i
                                        class="fas fa-arrow-left mr-2"></i> Cancelar</a>
                                <button type="submit"
                                    class="btn btn-success px-5 rounded-pill font-weight-bold shadow-sm"><i
                                        class="fas fa-save mr-2"></i> Registrar</button>
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
        const iNac = document.getElementById('fecha_nacimiento');
        const iMue = document.getElementById('fecha_muerte');

        iNac.addEventListener('change', () => iMue.min = iNac.value || '');

        document.getElementById('formMascota').addEventListener('submit', function (e) {
            const hoy = new Date().setHours(0, 0, 0, 0);
            if (iNac.value && new Date(iNac.value) > hoy) {
                e.preventDefault();
                Swal.fire('Error', 'La fecha de nacimiento no puede ser futura.', 'error');
            } else if (iMue.value && iNac.value && new Date(iMue.value) < new Date(iNac.value)) {
                e.preventDefault();
                Swal.fire('Error', 'La fecha de muerte no puede ser anterior al nacimiento.', 'error');
            }
        });

        <?php if ($registroExitoso): ?>
            Swal.fire({
                title: '¡Registrado!',
                text: 'Mascota añadida correctamente.',
                icon: 'success',
                confirmButtonColor: '#00897b',
                allowOutsideClick: false
            }).then((r) => { if (r.isConfirmed) window.location.href = 'gestionar-mascotas.php'; });
        <?php endif; ?>
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>