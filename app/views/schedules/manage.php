<?php
require_once dirname(__DIR__, 2) . '/models/Database.php';
// $schedules y $docentes son provistos por ScheduleController::manage()
include dirname(__DIR__) . '/layout/header.php';
?>

<div class="card">
  <h2>Gestión de Horarios (Lunes a Sábado)</h2>
  <p class="mb-1">Crea bloques de clase por docente. Usa el formato 24h (por ejemplo, 06:00 a 08:00).</p>
  <form method="post" action="index.php?action=schedules_create">
    <div class="form-row">
      <select name="docente_id" required>
        <option value="">-- Selecciona Docente --</option>
        <?php foreach($docentes as $d): ?>
          <option value="<?php echo (int)$d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="dia_semana" required>
        <option value="">-- Día --</option>
        <option value="1">Lunes</option>
        <option value="2">Martes</option>
        <option value="3">Miércoles</option>
        <option value="4">Jueves</option>
        <option value="5">Viernes</option>
        <option value="6">Sábado</option>
      </select>
    </div>
    <div class="form-row">
      <input type="time" name="hora_inicio" required>
      <input type="time" name="hora_fin" required>
      <input type="text" name="salon" placeholder="Salón">
    </div>
    <button type="submit" class="btn">Agregar Bloque</button>
  </form>
</div>

<div class="card">
  <h3 class="mb-1">Horarios registrados</h3>
  <div class="table-wrapper">
    <table>
      <tr>
        <th>ID</th>
        <th>Docente</th>
        <th>Día</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Salón</th>
        <th>Acciones</th>
      </tr>
      <?php
      $dias = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado'];
      foreach($schedules as $s):
      ?>
      <tr>
        <td><?php echo (int)$s['id']; ?></td>
        <td><?php echo htmlspecialchars($s['docente_nombre']); ?></td>
        <td><?php echo $dias[(int)$s['dia_semana']]; ?></td>
        <td><?php echo htmlspecialchars($s['hora_inicio']); ?></td>
        <td><?php echo htmlspecialchars($s['hora_fin']); ?></td>
        <td><?php echo htmlspecialchars($s['salon'] ?? ''); ?></td>
        <td>
          <form method="post" action="index.php?action=schedules_delete" onsubmit="event.preventDefault(); confirmAction(event, '¿Eliminar horario?', '¿Estás seguro de que deseas eliminar este horario? Esta acción no se puede deshacer.');">
            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
            <button type="submit" class="btn danger">Eliminar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
