<?php require_once '../shared/logica_gestionar_hospitalizaciones.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Gestión de Hospitalización - San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="mb-4">
            <h2 class="text-dark font-weight-bold"><i class="fas fa-hospital-alt text-danger mr-2"></i> Control de
                Hospitalizaciones</h2>
            <p class="text-muted">Panel administrativo de monitoreo de internaciones.</p>
        </div>

        <div class="card shadow border-0 section-space">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0">Pacientes Actuales Internados</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaActivas">
                        <thead class="bg-light">
                            <tr>
                                <th>Mascota</th>
                                <th>Fecha Ingreso</th>
                                <th>Derivado por</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($h = $resActivas->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($h['mascota']) ?></strong></td>
                                    <td data-order="<?= strtotime($h['fecha_ingreso']) ?>">
                                        <?= date('d/m/Y H:i', strtotime($h['fecha_ingreso'])) ?> hs
                                    </td>
                                    <td><?= htmlspecialchars($h['profesional']) ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($h['motivo']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-secondary text-white py-3">
                <h5 class="mb-0">Historial de altas recientes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-4">Mascota</th>
                                <th>Ingreso</th>
                                <th>Egreso</th>
                                <th>Motivo</th>
                                <th>Profesional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resHistorial && $resHistorial->num_rows > 0): ?>
                                <?php while ($hist = $resHistorial->fetch_assoc()): ?>
                                    <tr>
                                        <td class="pl-4"><strong><?= htmlspecialchars($hist['mascota']) ?></strong></td>
                                        <td class="small"><?= date('d/m/Y', strtotime($hist['fecha_ingreso'])) ?></td>
                                        <td class="small font-weight-bold text-success">
                                            <?= date('d/m/Y', strtotime($hist['fecha_egreso_real'])) ?>
                                        </td>
                                        <td class="text-muted small"><?= htmlspecialchars($hist['motivo']) ?></td>
                                        <td class="small"><?= htmlspecialchars($hist['profesional']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay registros de altas recientes.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../shared/scripts.php'; ?>
    <script>
        $(document).ready(function () {
            $('#tablaActivas').DataTable({
                "pageLength": 5,
                "order": [[1, "asc"]],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });
        });
    </script>

    <?php require_once '../shared/footer.php'; ?>
</body>

</html>