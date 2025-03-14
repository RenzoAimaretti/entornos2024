<?php
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinaria";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          where u.id = $id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes - Veterinaria San Antón</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body>
    <!-- Título -->
    <div class="container-fluid my-4">
        <?php 
        $nombre = "Cliente no encontrado";
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
        }
        ?>
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d; width: 100%;">Detalles de <?php echo htmlspecialchars($nombre); ?></h2>
    </div>
<div class="d-flex justify-content-center">
    <div class="card text-center" style="width:50rem;">
        <div class="card-header">
            <h2>Detalle del Cliente</h2>
        </div>
        <div class="card-body">
            <?php
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
            ?>
            <table class="table table-bordered">
                <tr>
                    <th>Nombre</th>
                    <td><?php echo htmlspecialchars($nombre ?? 'No definido'); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($row['email'] ?? 'No definido'); ?></td>
                </tr>
                <tr>
                    <th>Dirección</th>
                    <td><?php echo htmlspecialchars($row['direccion'] ?? 'No definido'); ?></td>
                </tr>
                <tr>
                    <th>Teléfono</th>
                    <td><?php echo htmlspecialchars($row['telefono'] ?? 'No definido'); ?></td>
                </tr>
            </table>
            <button class='btn btn-primary' style="margin: 1rem;">Editar informacion</button>

            <table class="table table-striped" >
                <thead class="thead">
                    <tr>
                        <th>
                            Nombre
                        </th>
                        <th>
                            Raza
                        </th>
                        <th>
                            Color
                        </th>
                        <th>
                            Fecha de Nacimiento
                        </th>
                        <th>
                            Fecha de Muerte
                        </th>
                        <th>
                            Acciones
                        </th>
                    </tr>

                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center">Pendiente de desarrollo</td>

                        </td>
                    </tr>
                </tbody>
                <!-- Agregar para agregar mascota?? -->


            </table>
            <button class='btn btn-primary' style="margin: 1rem;">Agregar mascota</button>

            
            
            <?php
            } else {
                echo "Cliente no encontrado";
            }
            ?>
        </div>
    </div>
</div>
<!-- Bootstrap Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>