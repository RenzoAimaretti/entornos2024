<?php
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

$servicios = [];
$mascotas = [];

if ($modo === 'especialista' && $profesionalId) {
    require_once '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

    $stmt = $conn->prepare("SELECT s.id, s.nombre FROM servicios s INNER JOIN especialidad esp ON esp.id = s.id_esp INNER JOIN profesionales p ON p.id_esp = esp.id WHERE p.id = ?");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $servicios[] = $row;
    }

    $stmt = $conn->prepare("SELECT DISTINCT m.id, m.nombre FROM mascotas m INNER JOIN atenciones a ON a.id_mascota = m.id WHERE a.id_pro = ?");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $mascotas[] = $row;
    }
    $conn->close();
}
?>

<form action="../shared/alta-atencion.php" method="POST">
    <div class="form-group">
        <label for="fecha">Fecha</label>
        <input type="date" class="form-control" id="fecha" name="fecha" required>
    </div>

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
            <input type="text" class="form-control" id="mascota" name="mascota" placeholder="Escriba nombre..."
                autocomplete="off" required>
            <input type="hidden" id="mascota_id" name="mascota_id">
            <div id="mascota_sugerencias" class="autocomplete-list" style="display:none;"></div>
        <?php endif; ?>
    </div>

    <?php if ($modo === 'especialista'): ?>
        <input type="hidden" id="especialista_id" name="especialista_id" value="<?= htmlspecialchars($profesionalId) ?>">
    <?php else: ?>
        <div class="form-group position-relative">
            <label for="especialista">Especialista</label>
            <input type="text" class="form-control" id="especialista" name="especialista" placeholder="Buscar médico..."
                autocomplete="off" required>
            <input type="hidden" id="especialista_id" name="especialista_id">
            <div id="especialista_sugerencias" class="autocomplete-list" style="display:none;"></div>
        </div>
    <?php endif; ?>

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
            <select class="form-control" id="servicio" name="servicio_id" required>
                <option value="">Seleccione un servicio</option>
            </select>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Registrar Atención</button>
</form>

<script>
    $(document).ready(function () {

        function buscar(tipo, texto, callback) {
            $.get('../shared/buscar-' + tipo + '.php', { q: texto }, function (data) {
                callback(data);
            }).fail(function () {
                alert('Error de conexión al buscar ' + tipo);
            });
        }

        $('#mascota').on('input', function () {
            const query = $(this).val();
            if (query.length > 1) {
                buscar('mascota', query, function (data) {
                    mostrarSugerencias('mascota', data);
                });
            } else {
                $('#mascota_sugerencias').hide();
            }
        });

        $('#especialista').on('input', function () {
            const fecha = $('#fecha').val();
            const hora = $('#hora').val();
            if (fecha && hora) {
                $.get('../shared/buscar-especialista.php', { q: $(this).val(), fecha: fecha, hora: hora }, function (data) {
                    mostrarSugerencias('especialista', data);
                }).fail(function () {
                    alert('Error de conexión al buscar especialista');
                });
            } else {
                alert('Primero seleccione fecha y hora');
                $(this).val('');
            }
        });

        function mostrarSugerencias(tipo, data) {
            const sugerenciasDiv = $('#' + tipo + '_sugerencias');
            sugerenciasDiv.empty();
            if (data.length > 0) {
                data.forEach(function (item) {
                    const div = $('<div></div>').text(item.nombre).click(function () {
                        $('#' + tipo).val(item.nombre);
                        $('#' + tipo + '_id').val(item.id);
                        if (tipo === 'especialista') {

                            $.get('../shared/buscar-servicio.php', { especialista_id: item.id }, function (data) {
                                $('#servicio').empty();
                                $('#servicio').append('<option value="">Seleccione un servicio</option>');
                                data.forEach(function (serv) {
                                    $('#servicio').append('<option value="' + serv.id + '">' + serv.nombre + '</option>');
                                });
                            }).fail(function () {
                                alert('Error al cargar servicios');
                            });
                        }
                        sugerenciasDiv.hide();
                    });
                    sugerenciasDiv.append(div);
                });
                sugerenciasDiv.show();
            } else {
                sugerenciasDiv.hide();
            }
        }

        $(document).click(function (e) {
            if (!$(e.target).closest('.position-relative').length) {
                $('.autocomplete-list').hide();
            }
        });
    });
</script>