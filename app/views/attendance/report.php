<?php include dirname(__DIR__) . "/layout/header.php"; ?>
<div class="card">
  <h2>Reportes</h2>
  <form method="get" action="index.php" class="mt-1">
    <input type="hidden" name="action" value="export_report">
    <div class="form-row">
      <input type="date" name="desde" required>
      <input type="date" name="hasta" required>
      <button type="submit" class="btn">Exportar PDF</button>
    </div>
  </form>
</div>
<?php include dirname(__DIR__) . "/layout/footer.php"; ?>
