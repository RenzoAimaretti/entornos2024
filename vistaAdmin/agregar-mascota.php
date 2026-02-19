<?php require_once '../shared/logica_agregar_mascota.php';
$ruta_base = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Alta de Mascota - Veterinaria San Antón</title>
    <?php require_once '../shared/head.php'; ?>
</head>

<body class="bg-light">
    <?php require_once '../shared/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-teal text-center py-4 text-white">
                        <h3 class="font-weight-bold mb-0"><i class="fas fa-paw mr-2"></i> Nueva Mascota</h3>
                        <p class="mb-0 text-white-50">Registrando para:
                            <strong><?php echo htmlspecialchars($nombreCliente); ?></strong>
                        </p>
                    </div>
                    <div class="card-body p-5">
                        <form method="POST" id="formMascota">
                            <input type="hidden" name="id_cliente" value="<?php echo $id; ?>">

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Nombre</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-signature"></i></span></div>
                                    <input type="text" class="form-control form-control-lg" name="nombre" required
                                        placeholder="Ej: Firulais" autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Raza / Especie</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-dog"></i></span></div>
                                    <input type="text" class="form-control" name="raza" placeholder="Ej: Caniche, Gato">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted">Nacimiento</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-birthday-cake"></i></span></div>
                                        <input type="date" class="form-control" id="fecha_nacimiento"
                                            name="fecha_nacimiento" max="<?php echo $hoy; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="font-weight-bold small text-muted text-danger">Fallecimiento</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-cross"></i></span></div>
                                        <input type="date" class="form-control" id="fecha_muerte" name="fecha_muerte"
                                            max="<?php echo $hoy; ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="gestionar-clientes.php" class="btn btn-outline-secondary px-4 rounded-pill"><i
                                        class="fas fa-arrow-left mr-2"></i> Cancelar</a>
                                <button type="submit"
                                    class="btn btn-success px-5 rounded-pill font-weight-bold shadow-sm"><i
                                        class="fas fa-save mr-2"></i> Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../shared/scripts.php'; ?>

    <script>
        const iNac = document.getElementById('fecha_nacimiento');
        const iMue = document.getElementById('fecha_muerte');

        iNac.addEventListener('change', () => iMue.min = iNac.value || '');

        document.getElementById('formMascota').addEventListener('submit', function (e) {
            const hoy = new Date().setHours(0, 0, 0, 0);
            if (iNac.value && new Date(iNac.value) > hoy) {
                e.preventDefault();
                Swal.fire('Error', 'La fecha de nacimiento no puede ser futura.', 'error');
            } else if (iMue.value && iNac.value && new Date(iMue.value) < new Date(iNac.value)) {
                e.preventDefault();
                Swal.fire('Error', 'La fecha de muerte no puede ser anterior al nacimiento.', 'error');
            }
        });

        <?php if ($registroExitoso): ?>
            Swal.fire({
                title: '¡Registrado!',
                text: 'Mascota añadida correctamente.',
                icon: 'success',
                confirmButtonColor: '#00897b',
                allowOutsideClick: false
            }).then((r) => { if (r.isConfirmed) window.location.href = 'gestionar-mascotas.php'; });
        <?php endif; ?>
    </script>
    <?php require_once '../shared/footer.php'; ?>
</body>

</html>