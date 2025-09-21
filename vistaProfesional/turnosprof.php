<?php
session_start();


if ($_SESSION['usuario_tipo'] !== 'veterinario') {
    die("Acceso denegado. Debes ser veterinario para acceder.");
}

include('conexion.php'); 

$turno_id = $_GET['id']; 
$veterinario_id = $_SESSION['usuario_id']; 

$query = "SELECT * FROM atenciones WHERE id = ? AND personal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $turno_id, $veterinario_id); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No tienes acceso a este turno.");
}

$turno = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $observaciones = $_POST['observaciones'];
    $update_query = "UPDATE atenciones SET descripcion = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('si', $observaciones, $turno_id);
    $update_stmt->execute();

    echo "Observaciones actualizadas exitosamente.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Turno</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Detalles del Turno</h1>

<h3>Turno para la mascota: <?php
    
    $mascota_id = $turno['mascota_id'];
    $mascota_query = "SELECT nombre FROM mascotas WHERE id = ?";
    $mascota_stmt = $conn->prepare($mascota_query);
    $mascota_stmt->bind_param('i', $mascota_id);
    $mascota_stmt->execute();
    $mascota_result = $mascota_stmt->get_result();
    $mascota = $mascota_result->fetch_assoc();
    echo $mascota['nombre'];
?></h3>

<p><strong>Fecha y Hora:</strong> <?php echo $turno['fecha_hora']; ?></p>
<p><strong>Título:</strong> <?php echo $turno['titulo']; ?></p>

<h3>Observaciones:</h3>
<p><?php echo $turno['descripcion']; ?></p>

<
<h3>Agregar Observaciones</h3>
<form method="POST">
    <textarea name="observaciones" rows="5" cols="50"><?php echo $turno['descripcion']; ?></textarea><br><br>
    <button type="submit">Actualizar Observaciones</button>
</form>

</body>
</html>
