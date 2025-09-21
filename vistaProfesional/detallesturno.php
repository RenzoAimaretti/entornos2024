<?php
session_start();

// Verificación de si es veterinario
if ($_SESSION['usuario_tipo'] !== 'veterinario') {
    die("Acceso denegado. Debes ser veterinario para acceder.");
}

include('conexion.php'); // Incluye la conexión a la base de datos

$veterinario_id = $_SESSION['usuario_id']; // ID del veterinario (ya debe estar guardado en la sesión)

$query = "SELECT * FROM atenciones WHERE personal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $veterinario_id); // Vinculamos el ID del veterinario
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Veterinario</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Bienvenido al Panel de Veterinario</h1>
<h2>Turnos Pendientes</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Mascota</th>
            <th>Fecha y Hora</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td>
                    <?php
                    // Mostrar nombre de la mascota
                    $mascota_id = $row['mascota_id'];
                    $mascota_query = "SELECT nombre FROM mascotas WHERE id = ?";
                    $mascota_stmt = $conn->prepare($mascota_query);
                    $mascota_stmt->bind_param('i', $mascota_id);
                    $mascota_stmt->execute();
                    $mascota_result = $mascota_stmt->get_result();
                    $mascota = $mascota_result->fetch_assoc();
                    echo $mascota['nombre'];
                    ?>
                </td>
                <td><?php echo $row['fecha_hora']; ?></td>
                <td><a href="detalle_turno.php?id=<?php echo $row['id']; ?>">Ver Detalles</a></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>
