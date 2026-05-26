<?php include __DIR__ . "/layout/header.php"; ?>
<div class="card">
  <h2>Bienvenido, <?php echo $_SESSION['user']['nombre']; ?> (<?php echo $_SESSION['user']['rol']; ?>)</h2>
  <p class="mb-0">Seleccione una opción en el menú de arriba.</p>
</div>
<?php include __DIR__ . "/layout/footer.php"; ?>
