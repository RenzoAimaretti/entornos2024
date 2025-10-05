<?php
// ✅ Aseguramos que existan las variables si este archivo se incluye desde otro contexto
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($modo)) {
    $modo = $_SESSION['usuario_tipo'] ?? 'default';
}

if (!isset($profesionalId)) {
    $profesionalId = $_SESSION['usuario_id'] ?? null;
}

if (!isset($nombreProfesional)) {
    $nombreProfesional = $_SESSION['usuario_nombre'] ?? '';
}

error_log("Modo: " . $modo);
error_log("Profesional ID: " . $profesionalId);

// ✅ Si el modo es especialista, precargamos datos
$servicios = [];
$mascotas = [];

if ($modo === 'especialista' && $profesionalId) {
    require_once '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Servicios del profesional
    $stmt = $conn->prepare("
        SELECT s.id, s.nombre
        FROM servicios s
        INNER JOIN especialidad esp ON esp.id = s.id_esp
        INNER JOIN profesionales p ON p.id_esp = esp.id
        WHERE p.id = ?
    ");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $servicios[] = $row;
    }
    $stmt->close();

    // Mascotas asociadas a atenciones previas del profesional
    $stmt = $conn->prepare("
        SELECT DISTINCT m.id, m.nombre
        FROM mascotas m
        INNER JOIN atenciones a ON a.id_mascota = m.id
        WHERE a.id_pro = ?
    ");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $mascotas[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>

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
                        <!-- Fecha -->
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>

                        <!-- Hora -->
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <select class="form-control" id="hora" name="hora" required>
                                <option value="">Seleccione hora</option>
                                <?php
                                for ($h = 9; $h <= 16; $h++) {
                                    foreach (['00', '15', '30', '45'] as $m) {
                                        $hora = sprintf("%02d:%s", $h, $m);
                                        echo "<option value='$hora'>$hora</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Mascota -->
                        <div class="form-group position-relative">
                            <label for="mascota">Mascota</label>
                            <?php if ($modo === 'especialista'): ?>
                                <select class="form-control" id="mascota_id" name="mascota_id" required>
                                    <option value="">Seleccione mascota</option>
                                    <?php foreach ($mascotas as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" id="mascota" name="mascota" autocomplete="off" required>
                                <input type="hidden" id="mascota_id" name="mascota_id">
                                <div id="mascota_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            <?php endif; ?>
                        </div>

                        <!-- Especialista -->
                        <?php if ($modo === 'especialista'): ?>
                            <div class="form-group">
                                <label>Especialista</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($nombreProfesional) ?>" disabled>
                                <input type="hidden" id="especialista_id" name="especialista_id" value="<?= htmlspecialchars($profesionalId) ?>">
                            </div>
                        <?php else: ?>
                            <div class="form-group position-relative">
                                <label for="especialista">Especialista</label>
                                <input type="text" class="form-control" id="especialista" name="especialista" autocomplete="off" required>
                                <input type="hidden" id="especialista_id" name="especialista_id">
                                <div id="especialista_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            </div>
                        <?php endif; ?>

                        <!-- Servicio -->
                        <div class="form-group position-relative">
                            <label for="servicio">Servicio</label>
                            <?php if ($modo === 'especialista'): ?>
                                <select class="form-control" id="servicio_id" name="servicio_id" required>
                                    <option value="">Seleccione un servicio</option>
                                    <?php foreach ($servicios as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" id="servicio" name="servicio" autocomplete="off" required>
                                <input type="hidden" id="servicio_id" name="servicio_id">
                                <div id="servicio_sugerencias" class="autocomplete-list" style="display:none;"></div>
                            <?php endif; ?>
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
    //Busqueda de datos para autocompletar

    // Función genérica de autocomplete
    function buscar(tipo, texto) {
        if (tipo === "especialista" || tipo==="servicio") return; // Ya manejado por la función específica

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
    
    //busquedas especificas
    function buscarEspecialista(texto, fecha, hora) {
        if (texto.length < 2) {
            $("#especialista_sugerencias").hide();
            return;
        }

        $.getJSON("../shared/buscar-especialista.php", { 
            q: texto, 
            fecha: fecha, 
            hora: hora 
        }, function(data) {
            let contenedor = $("#especialista_sugerencias");
            contenedor.empty();

            if (data.length > 0) {
                data.forEach(function(item) {
                    contenedor.append("<div data-id='" + item.id + "'>" + item.nombre + "</div>");
                });
                contenedor.show();

                contenedor.find("div").on("click", function() {
                    $("#especialista").val($(this).text());
                    $("#especialista_id").val($(this).data("id"));
                    contenedor.hide();
                });
            } else {
                contenedor.append("<div class='text-danger'>No hay especialistas disponibles en este horario</div>");
                contenedor.show();
            }
        });
    }

    function buscarServicioPorEspecialista(texto, especialistaId) {
        if (texto.length < 2) {
            $("#servicio_sugerencias").hide();
            return;
        }

        $.getJSON("../shared/buscar-servicio.php", { 
            q: texto, 
            especialista_id: especialistaId 
        }, function(data) {
            let contenedor = $("#servicio_sugerencias");
            contenedor.empty();

            if (data.length > 0) {
                data.forEach(function(item) {
                    contenedor.append("<div data-id='" + item.id + "'>" + item.nombre + "</div>");
                });
                contenedor.show();

                contenedor.find("div").on("click", function() {
                    $("#servicio").val($(this).text());
                    $("#servicio_id").val($(this).data("id"));
                    contenedor.hide();
                });
            } else {
                contenedor.append("<div class='text-danger'>No hay servicios disponibles para este especialista</div>");
                contenedor.show();
            }
        });
    }

    //Limpieza de campos dependientes
    $("#fecha, #hora").on("change", function() {
        // Limpiar el campo especialista y sugerencias al cambiar la fecha u hora
        $("#especialista").val('');
        $("#especialista_id").val('');
        $("#especialista_sugerencias").hide();
        // Limpiar sugerencias de servicio
        $("#servicio").val('');
        $("#servicio_id").val('');
        $("#servicio_sugerencias").hide();
    });

    $("#especialista").on("change", function() {
        // Limpiar el campo servicio y sugerencias al cambiar el especialista
        $("#servicio").val('');
        $("#servicio_id").val('');
        $("#servicio_sugerencias").hide();
    });

    // Eventos de input para autocompletar
    $("#mascota").on("input", function() {
        buscar("mascota", $(this).val());
    });

    $("#especialista").on("input", function() {
        // Obtener fecha y hora seleccionadas
        const fecha = $("#fecha").val();
        const hora = $("#hora").val();
        
        if (fecha && hora) {
            buscarEspecialista($(this).val(), fecha, hora);
        } else {
            alert("Primero seleccione fecha y hora");
            $(this).val('');
        }
    });
    $("#servicio").on("input", function() {
        // Verificar que haya un especialista seleccionado
        const especialistaId = $("#especialista_id").val();
        
        if (especialistaId) {
            buscarServicioPorEspecialista($(this).val(), especialistaId);
        } else {
            alert("Primero seleccione un especialista");
            $(this).val('');
        }
    });
    </script>