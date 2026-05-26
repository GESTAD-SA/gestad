<?php include dirname(__DIR__) . "/layout/header.php"; ?>
<div class="card">
  <h2>Lista de Asistencias</h2>
  <form method="get" action="index.php" class="mb-2">
    <input type="hidden" name="action" value="attendance_list">
    <div class="form-row">
      <input type="date" name="desde" value="<?php echo $_GET['desde'] ?? ''; ?>" placeholder="Desde">
      <input type="date" name="hasta" value="<?php echo $_GET['hasta'] ?? ''; ?>" placeholder="Hasta">
      <input type="text" name="cedula" value="<?php echo $_GET['cedula'] ?? ''; ?>" placeholder="Cédula">
      <button type="submit" class="btn">Filtrar</button>
    </div>
  </form>
  <div class="table-wrapper">
    <table>
      <tr>
        <th>ID</th>
        <th>Identificación</th>
        <th>Docente</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Estado</th>
        <th>Observación</th>
        <?php if(($_SESSION['user']['rol'] ?? '')==='admin'): ?><th>Acciones</th><?php endif; ?>
      </tr>
      <?php foreach($asistencias as $a): ?>
      <tr>
        <td><?php echo $a['id']; ?></td>
        <td><?php echo htmlspecialchars($a['identificacion']); ?></td>
        <td><?php echo htmlspecialchars($a['nombre']); ?></td>
        <td><?php echo $a['fecha']; ?></td>
        <td><?php echo $a['hora']; ?></td>
        <td><?php echo $a['estado']; ?></td>
        <td><?php echo htmlspecialchars($a['observacion'] ?? ''); ?></td>
        <?php if(($_SESSION['user']['rol'] ?? '')==='admin'): ?>
        <td>
          <form method="post" action="index.php?action=save_observation" class="mb-0">
            <input type="hidden" name="attendance_id" value="<?php echo $a['id']; ?>">
            <input type="hidden" name="desde" value="<?php echo $_GET['desde'] ?? ''; ?>">
            <input type="hidden" name="hasta" value="<?php echo $_GET['hasta'] ?? ''; ?>">
            <input type="hidden" name="cedula" value="<?php echo $_GET['cedula'] ?? ''; ?>">
            <div class="form-row">
              <input type="text" name="observacion" placeholder="Observación" value="<?php echo htmlspecialchars($a['observacion'] ?? ''); ?>">
              <button type="submit" class="btn">Guardar</button>
            </div>
          </form>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php include dirname(__DIR__) . "/layout/footer.php"; ?>
