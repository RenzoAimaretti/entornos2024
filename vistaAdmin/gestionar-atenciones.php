<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Atenciones</title>

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>

    <!-- jQuery y Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link href="../styles.css" rel="stylesheet">

    <style>
    .autocomplete-list {
        border: 1px solid #ccc;
        background: #fff;
        position: absolute;
        z-index: 999;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
    }
    .autocomplete-list div {
        padding: 5px;
        cursor: pointer;
    }
    .autocomplete-list div:hover {
        background: #eee;
    }
    </style>
</head>
<body>
    <?php require_once '../shared/navbar.php'; ?>

    <?php $modo = 'default';
include '../shared/form-turno.php';?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
