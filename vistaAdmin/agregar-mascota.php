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

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          WHERE u.id = $id";

$result = $conn->query($query);
$nombre = "Cliente no encontrado";
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombre = $row['nombre'];
}

// Obtener la fecha de hoy en formato YYYY-MM-DD para el atributo 'max' del HTML
$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Mascota - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container-fluid my-4">
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d; width: 100%;">
            Detalles de <?php echo htmlspecialchars($nombre); ?>
        </h2>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Alta de mascota para
                            <?php echo htmlspecialchars($nombre); ?>
                        </h3>
                        <form action="../shared/alta-mascota.php" method="POST" id="formMascota">
                            <input type="hidden" name="id_cliente" value="<?php echo $id; ?>">

                            <div class="form-group">
                                <label for="nombre">Nombre de la mascota</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    placeholder="Ej: Firulais">
                            </div>

                            <div class="form-group">
                                <label for="raza">Raza (opcional)</label>
                                <input type="text" class="form-control" id="raza" name="raza"
                                    placeholder="Ej: Labrador">
                            </div>

                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de nacimiento (opcional)</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                    max="<?php echo $hoy; ?>">
                            </div>

                            <div class="form-group">
                                <label for="fecha_muerte">Fecha de muerte (opcional)</label>
                                <input type="date" class="form-control" id="fecha_muerte" name="fecha_muerte"
                                    max="<?php echo $hoy; ?>">
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-5">Registrar Mascota</button>
                                <a href="gestionar-clientes.php" class="btn btn-secondary ml-2">Cancelar</a>
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

        // UX: Cuando cambia la fecha de nacimiento, la fecha de muerte no puede ser anterior a esa
        inputNacimiento.addEventListener('change', function () {
            if (this.value) {
                inputMuerte.min = this.value;
            } else {
                inputMuerte.removeAttribute('min');
            }
        });

        // Validación al enviar el formulario
        document.getElementById('formMascota').addEventListener('submit', function (e) {
            const fechaNacStr = inputNacimiento.value;
            const fechaMueStr = inputMuerte.value;
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaNacStr) {
                const fechaNac = new Date(fechaNacStr);
                fechaNac.setHours(24, 0, 0, 0); // Ajuste zona horaria JS

                // 1. Validar que nacimiento no sea futuro
                if (fechaNac > hoy) {
                    e.preventDefault();
                    alert("La fecha de nacimiento no puede ser posterior a hoy.");
                    return;
                }

                // 2. Validar que muerte no sea anterior a nacimiento
                if (fechaMueStr) {
                    const fechaMue = new Date(fechaMueStr);
                    fechaMue.setHours(24, 0, 0, 0);

                    if (fechaMue < fechaNac) {
                        e.preventDefault();
                        alert("La fecha de muerte no puede ser anterior a la fecha de nacimiento.");
                        return;
                    }
                }
            }
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>