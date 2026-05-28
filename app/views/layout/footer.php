</main>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal-overlay">
  <div class="modal-content">
    <h3 id="modalTitle">¿Estás seguro?</h3>
    <p id="modalMessage">Esta acción no se puede deshacer.</p>
    <div class="modal-actions">
      <button id="confirmButton" class="btn btn-confirm">Confirmar</button>
      <button id="cancelButton" class="btn btn-cancel">Cancelar</button>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <p>Proyecto RFID - <?php echo date("Y"); ?></p>
  </div>
</footer>

<!-- JavaScript Files -->
<script src="/gestad/public/assets/js/confirmation-modal.js"></script>
</body>
</html>
