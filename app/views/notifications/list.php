<?php include dirname(__DIR__) . "/layout/header.php"; ?>
<div class="card">
  <h2>Notificaciones</h2>
  <div class="table-wrapper">
    <table>
      <tr><th>Docente</th><th>Mensaje</th><th>Fecha</th></tr>
      <?php foreach($notificaciones as $n): ?>
      <tr>
        <td><?php echo $n['nombre']; ?></td>
        <td><?php echo $n['mensaje']; ?></td>
        <td><?php echo $n['fecha']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php include dirname(__DIR__) . "/layout/footer.php"; ?>
