<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    header('Location: ../index.php');
    exit();
}

$profesionalId = $_SESSION['usuario_id'];
$modo = 'especialista';
$nombreProfesional = $_SESSION['usuario_nombre'] ?? '';

$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Gestión de Atenciones</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <?php include '../shared/form-turno.php'; ?>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script> -->
</body>

</html>