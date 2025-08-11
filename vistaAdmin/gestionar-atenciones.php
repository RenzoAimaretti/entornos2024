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
        <div class="row">
            <!-- Calendario -->
            <div class="col-md-7 mb-4">
                <div id="calendario"></div>
            </div>
            <!-- Formulario -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class='card-title'>Registrar nueva atención</h2>
                        <form action="../shared/alta-atencion.php" method="POST">
                            <div class="form-group">
                                <label for="fecha">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>
                            <div class="form-group">
                                <label for="hora">Hora</label>
                                <select class="form-control" id="hora" name="hora" required>
                                    <option value="" disabled selected>Seleccione hora</option>
                                    <option value="09:00">09:00</option>
                                    <option value="09:15">09:15</option>
                                    <option value="09:30">09:30</option>
                                    <option value="09:45">09:45</option>
                                    <option value="10:00">10:00</option>
                                    <option value="10:15">10:15</option>
                                    <option value="10:30">10:30</option>
                                    <option value="10:45">10:45</option>
                                    <option value="11:00">11:00</option>
                                    <option value="11:15">11:15</option>
                                    <option value="11:30">11:30</option>
                                    <option value="11:45">11:45</option>
                                    <option value="12:00">12:00</option>
                                    <option value="12:15">12:15</option>
                                    <option value="12:30">12:30</option>
                                    <option value="12:45">12:45</option>
                                    <option value="13:00">13:00</option>
                                    <option value="13:15">13:15</option>
                                    <option value="13:30">13:30</option>
                                    <option value="13:45">13:45</option>
                                    <option value="14:00">14:00</option>
                                    <option value="14:15">14:15</option>
                                    <option value="14:30">14:30</option>
                                    <option value="14:45">14:45</option>
                                    <option value="15:00">15:00</option>
                                    <option value="15:15">15:15</option>
                                    <option value="15:30">15:30</option>
                                    <option value="15:45">15:45</option>
                                    <option value="16:00">16:00</option>
                                    <option value="16:15">16:15</option>
                                    <option value="16:30">16:30</option>
                                </select>
                            </div>

                            <!-- Campo Mascota con Autocomplete -->
                            <div class="form-group position-relative">
                                <label for="mascota">Mascota</label>
                                <input type="text" class="form-control" id="mascota" name="mascota" autocomplete="off" required>
                                <input type="hidden" id="mascota_id" name="mascota_id">
                                <div id="mascota_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            </div>

                            <!-- Campo especialista con Autocomplete -->
                            <div class="form-group position-relative">
                                <label for="especialista">Especialista</label>
                                <input type="text" class="form-control" id="especialista" name="especialista" autocomplete="off" required>
                                <input type="hidden" id="especialista_id" name="especialista_id">
                                <div id="especialista_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            </div>

                            <div class="form-group">
                                <label for="servicio">Servicio</label>
                                <input type="text" class="form-control" id="servicio" name="servicio" autocomplete="off" required>
                                <input type="hidden" id="servicio_id" name="servicio_id">
                                <div id="servicio_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            </div>

                            <button type="submit" class="btn btn-primary">Registrar Atención</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendario');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: '../shared/atenciones.php',
            eventClick: function(info) {
                let confirmacion = confirm(`Ver detalles de atención para: "${info.event.title}"?`);
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

    // Función genérica de autocomplete
    function buscar(tipo, texto) {
        if (texto.length < 2) {
            $("#" + tipo + "_sugerencias").hide();
            return;
        }

        $.getJSON("../shared/buscar-" + tipo + ".php", { q: texto }, function(data) {
            let contenedor = $("#" + tipo + "_sugerencias");
            contenedor.empty();
            if (data.length > 0) {
                data.forEach(function(item) {
                    contenedor.append("<div data-id='" + item.id + "'>" + item.nombre + "</div>");
                });
                contenedor.show();

                contenedor.find("div").on("click", function() {
                    $("#" + tipo).val($(this).text());
                    $("#" + tipo + "_id").val($(this).data("id"));
                    contenedor.hide();
                });
            } else {
                contenedor.hide();
            }
        });
    }

    $("#mascota").on("input", function() {
        buscar("mascota", $(this).val());
    });

    $("#especialista").on("input", function() {
        buscar("especialista", $(this).val());
    });
    $("#servicio").on("input", function() {
        buscar("servicio", $(this).val());
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
