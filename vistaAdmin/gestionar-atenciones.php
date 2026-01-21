<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Atenciones - San Antón</title>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js'></script>

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
            max-height: 150px;
            overflow-y: auto;
        }

        .autocomplete-list div {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .autocomplete-list div:hover {
            background: #f8f9fa;
        }

        #calendario {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .contenedor-registro {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container-fluid mt-4 px-lg-5">
        <h2 class="text-center mb-4">Panel de Gestión de Atenciones</h2>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div id="calendario"></div>
            </div>

            <div class="col-lg-4">
                <div class="contenedor-registro">
                    <h4 class="mb-4 text-primary border-bottom pb-2">Registrar Nueva Atención</h4>
                    <?php
                    $modo = 'default';
                    include '../shared/form-turno.php';
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es', // IDIOMA ESPAÑOL
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: '../shared/atenciones.php',
                eventClick: function (info) {
                    if (confirm(`¿Ver detalles de: "${info.event.title}"?`)) {
                        window.location.href = '../shared/detalle-atencionAP.php?id=' + info.event.id;
                    }
                }
            });
            calendar.render();
        });

        // Funciones de búsqueda para el autocompletado (Mascota, Especialista, Servicio)
        function buscar(tipo, texto) {
            if (tipo === "especialista" || tipo === "servicio") return;
            if (texto.length < 2) { $("#" + tipo + "_sugerencias").hide(); return; }
            $.getJSON("../shared/buscar-" + tipo + ".php", { q: texto }, function (data) {
                let contenedor = $("#" + tipo + "_sugerencias");
                contenedor.empty();
                if (data.length > 0) {
                    data.forEach(item => contenedor.append("<div data-id='" + item.id + "'>" + item.nombre + "</div>"));
                    contenedor.show();
                    contenedor.find("div").on("click", function () {
                        $("#" + tipo).val($(this).text());
                        $("#" + tipo + "_id").val($(this).data("id"));
                        contenedor.hide();
                    });
                } else { contenedor.hide(); }
            });
        }

        $("#mascota").on("input", function () { buscar("mascota", $(this).val()); });
        // (Agrega aquí el resto de tus funciones de búsqueda JS si las necesitas)
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>