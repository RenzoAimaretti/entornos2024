<?php require_once '../shared/logica_editar_atencion.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Evolución Médica - San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container my-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-4">
                <li class="breadcrumb-item"><a href="panel-profesional.php" class="text-teal">Panel</a></li>
                <li class="breadcrumb-item active" aria-current="page">Realizar Atención</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Datos del Turno</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="label-dato">Paciente</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-paw text-teal fa-lg"></i>
                                </div>
                                <div>

                                    <div class="valor-dato"><?php echo htmlspecialchars($atencion['mascota']); ?></div>
                                    <sma ll class="text-muted">
                                        <?php echo htmlspecialchars($atencion['raza'] ?? 'Raza no esp.'); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="label-dato">Servicio Solicitado</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-stethoscope text-teal fa-lg"></i>
                                </div>

                                <div class="valor-dato"><?php echo htmlspecialchars($atencion['servicio']); ?></div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="label-dato">Fecha y Hora</div>
                            <div class="d-flex align-items-center mt-1">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                    style="width: 45px; height: 45px;">
                                    <i class="fas fa-calendar-alt text-teal fa-lg"></i>
                                </div>
                                <div>
                                    <div c lass="valor-dato"><?php echo htmlspecialchars($fecha); ?></div>
                                    <span class="badge badge-pill badge-info"><?php echo htmlspecialchars($hora); ?>
                                        hs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <for m method="post" class="card shadow border-0 h-100">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($atencion['id']); ?>">


                    <div class="card-header bg-teal text-white d-flex justify-content-between align-items-cent
                  e                 r">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-file-medical-alt mr-2"></i> Informe Médico
                        </h5>
                        <span class="badge badge-light text-teal">N° <?php echo $atencion['id']; ?></span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <div class="form-group flex-grow-1">
                            <lab el class="font-weight-bold text-secondary mb-2">Evolución / Diagnóstico /
                                Tratamiento:</label>
                                <tex tarea name="detalle" class="form-control editor-area h-100" placehold e
                                    r="Escriba aquí los detalles de la consulta, diagnóstico y tratamiento indicado..."
                                    rows="12" required><?php echo htmlspecialchars($atencion['detalle']); ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white borde
            r                       -top-0 pb-4 pt-0 text-right">
                        <a href="dashboardProfesional.php" class="btn btn-outline-secondary px-4 mr-2">Cancelar</a>
                        <but ton type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i> Guardar Informe
                            </button>
                    </div>
                    </form>
            </div>
        </div>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script> -->
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>