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

    <div class="container">
        <h2 class="text-center mt-3">Calendario de Atenciones</h2>
        <div id="calendario"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: '../shared/atenciones.php',
                eventClick: function (info) {
                    let confirmacion = confirm(`Ver detalles de: "${info.event.title}"?`);
                    if (confirmacion) {
                        verDetalles(info.event.id);
                    }
                }
            });
            calendar.render();
        });

        function verDetalles(id) {
            window.location.href = '../shared/detalle-atencionAP.php?id=' + id;
        }
    </script>
    <?php $modo = 'default';
include '../shared/form-turno.php';?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>