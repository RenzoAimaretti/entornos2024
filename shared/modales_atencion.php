<?php
?>

<?php if ($esAdmin): ?>
  <div class="modal fade" id="editarAtencionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-teal text-white">
          <h5 class="modal-title font-weight-bold">Editar Informe Médico</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <form action="../shared/editar-atencion.php" method="POST">
          <div class="modal-body p-4">
            <input type="hidden" name="id" value="<?= $atencion['id'] ?>">
            <div class="form-group">
              <label class="font-weight-bold text-muted small">Fecha y Hora</label>
              <input type="datetime-local" class="form-control" name="fecha"
                value="<?= date('Y-m-d\TH:i', strtotime($atencion['fecha'])) ?>" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold text-muted small">Detalles del Procedimiento / Observaciones</label>
              <textarea class="form-control" name="detalle" rows="6"
                required><?= htmlspecialchars($atencion['detalle']) ?></textarea>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success font-weight-bold">Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title font-weight-bold">Confirmar Eliminación</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body text-center p-5">
          <i class="fas fa-exclamation-triangle text-warning fa-4x mb-3"></i>
          <h4>¿Estás seguro?</h4>
          <p class="text-muted">Se eliminará permanentemente este registro de atención.</p>
          <form action="../shared/eliminar-atencion.php" method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= $atencion['id'] ?>">
            <button type="button" class="btn btn-secondary px-4 mr-2" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger px-4 font-weight-bold">Sí, Eliminar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>