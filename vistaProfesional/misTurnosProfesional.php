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

    <?php require_once '../shared/scripts.php'; ?>
</body>

</html>