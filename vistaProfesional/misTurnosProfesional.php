<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

$profesionalId = $_SESSION['usuario_id'];
$modo = 'especialista';
$nombreProfesional = $_SESSION['usuario_nombre'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Atenciones</title>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />

    <link href="../styles.css" rel="stylesheet" />
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <?php include '../shared/form-turno.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>