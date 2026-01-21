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

    // Servicios del profesional
    $stmt = $conn->prepare("SELECT s.id, s.nombre FROM servicios s INNER JOIN especialidad esp ON esp.id = s.id_esp INNER JOIN profesionales p ON p.id_esp = esp.id WHERE p.id = ?");
    $stmt->bind_param('i', $profesionalId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $servicios[] = $row;
    }

    // Mascotas
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
            <input type="text" class="form-control" id="servicio" name="servicio" placeholder="Buscar servicio..."
                autocomplete="off" required>
            <input type="hidden" id="servicio_id" name="servicio_id">
            <div id="servicio_sugerencias" class="autocomplete-list" style="display:none;"></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Registrar Atención</button>
</form>