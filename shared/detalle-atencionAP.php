<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || !in_array($_SESSION['usuario_tipo'], ['admin', 'especialista'])) {
    header('Location: ../index.php');
    exit();
}

require_once 'db.php';
require_once '../shared/consultas_atenciones.php';

$idAtencion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idAtencion <= 0)
    die("ID no válido.");

$atencion = obtenerDetalleAtencion($conn, $idAtencion);

if (!$atencion)
    die("Atención no encontrada.");

$esAdmin = ($_SESSION['usuario_tipo'] === 'admin');

$ruta_base = "../";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Ficha Médica #<?= $atencion['id']; ?> - San Antón</title>
    <?php require_once 'head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-4">
                <li class="breadcrumb-item"><a href="../vistaAdmin/gestionar-atenciones.php">Atenciones</a></li>
                <li class="breadcrumb-item active">Ficha #<?= $atencion['id']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="ficha-medica p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                        <div>
                            <h2 class="mb-0 text-teal font-weight-bold">Reporte de Atención</h2>
                            <span class="badge badge-info px-3 py-2 mt-2" style="font-size: 0.9rem;">
                                <?= htmlspecialchars($atencion['nombreServicio']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <h5 class="text-muted mb-0"><?= date('d/m/Y', strtotime($atencion['fecha'])); ?></h5>
                            <small class="text-muted"><?= date('H:i', strtotime($atencion['fecha'])); ?> hs</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="label-dato">Paciente</div>
                            <div class="valor-dato font-weight-bold">
                                <?= htmlspecialchars($atencion['nombreMascota']) ?>
                                <small
                                    class="text-muted font-weight-normal">(<?= htmlspecialchars($atencion['raza'] ?? '') ?>)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="label-dato">Profesional a Cargo</div>
                            <div class="valor-dato">Dr/a. <?= htmlspecialchars($atencion['nombrePro']) ?></div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="label-dato">Observaciones / Diagnóstico</div>
                        <div class="p-3 bg-light rounded border" style="min-height: 150px;">
                            <?= nl2br(htmlspecialchars($atencion['detalle'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white font-weight-bold text-secondary">
                        <i class="fas fa-cogs mr-2"></i> Acciones
                    </div>
                    <div class="card-body">
                        <?php if ($esAdmin): ?>
                            <button type="button" class="btn btn-warning btn-block font-weight-bold mb-3 shadow-sm"
                                data-toggle="modal" data-target="#editarAtencionModal">
                                <i class="fas fa-edit mr-2"></i> Editar Informe
                            </button>
                            <div class="border-top pt-3 mb-3"></div>
                        <?php endif; ?>

                        <a href="../shared/detalle-mascota.php?idMascota=<?= $atencion['idMascota']; ?>"
                            class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-paw mr-2"></i> Ver Historia Clínica
                        </a>

                        <a href="../vistaAdmin/detalle-especialista.php?id=<?= $atencion['idPro']; ?>"
                            class="btn btn-outline-secondary btn-block mb-2">
                            <i class="fas fa-user-md mr-2"></i> Perfil Profesional
                        </a>

                        <?php if ($esAdmin): ?>
                            <div class="border-top pt-3 mb-3"></div>
                            <?php if (strtotime($atencion['fecha']) < time()): ?>
                                <button class="btn btn-light btn-block text-muted" disabled>
                                    <i class="fas fa-lock mr-2"></i> Eliminar (Bloqueado)
                                </button>
                                <small class="text-center d-block text-muted mt-1">No se puede borrar historial pasado.</small>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-danger btn-block" data-toggle="modal"
                                    data-target="#modalEliminar">
                                    <i class="fas fa-trash-alt mr-2"></i> Cancelar Turno
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../shared/modales_atencion.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>