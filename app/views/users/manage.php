<?php
// Cargar clases necesarias porque esta vista invoca UserModel y Database directamente
require_once dirname(__DIR__, 2) . "/models/UserModel.php";
require_once dirname(__DIR__, 2) . "/models/Database.php";
?>
<?php include dirname(__DIR__) . "/layout/header.php"; ?>
<div class="card">
  <h2>Gestionar <?php echo ($_SESSION['user']['rol']=='superadmin') ? 'Admins' : 'Docentes'; ?></h2>
  <form method="post" action="index.php?action=create_user">
    <input type="hidden" name="rol" value="<?php echo ($_SESSION['user']['rol']=='superadmin') ? 'admin' : 'docente'; ?>">
    <div class="form-row">
      <input type="text" name="nombre" placeholder="Nombre completo" required>
      <input type="text" name="usuario" placeholder="Usuario" required>
    </div>
    <div class="form-row">
      <input type="text" name="cedula" placeholder="Cédula" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <input type="email" name="email" placeholder="Correo electrónico">
    </div>
    <div>
      <button type="submit" class="btn">Crear</button>
    </div>
  </form>
  </div>

<div class="card">
  <h3 class="mb-1">Usuarios existentes</h3>
  <div class="table-wrapper">
    <table>
      <tr>
        <th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th>
        <?php if($_SESSION['user']['rol']=='admin'): ?>
          <th>UID Tarjeta</th>
        <?php endif; ?>
        <th>Acciones</th>
      </tr>
      <?php 
      $usuarios = UserModel::listByRole(($_SESSION['user']['rol']=='superadmin')?'admin':'docente');
      foreach($usuarios as $u): ?>
      <tr>
        <td><?php echo $u['id']; ?></td>
        <td><?php echo $u['nombre']; ?></td>
        <td><?php echo $u['usuario']; ?></td>
        <td><?php echo $u['email']; ?></td>
        <?php if($_SESSION['user']['rol']=='admin'): ?>
          <td><?php echo $u['uid_tarjeta'] ?? 'No asignada'; ?></td>
        <?php endif; ?>
        <td>
          <?php if($_SESSION['user']['rol']=='admin'): ?>
            <!-- Asignar UID (solo admin) -->
            <form method="post" action="index.php?action=assign_card" class="mb-1 assign-card-form">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <div class="form-row">
                <select name="uid" class="uid-select" <?php echo ($_SESSION['user']['rol'] === 'admin') ? 'data-auto-refresh="5000"' : ''; ?>>
                  <option value="">-- Selecciona UID leído --</option>
                </select>
                <button type="button" class="btn secondary refresh-uids">Actualizar UIDs</button>
                <button type="submit" class="btn">Asignar</button>
              </div>
            </form>

            <!-- Actualizar datos del docente (admin) -->
            <form method="post" action="index.php?action=update_user" class="mb-1">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <div class="form-row">
                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                <input type="text" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($u['usuario']); ?>" required>
              </div>
              <div class="form-row">
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($u['email']); ?>">
                <input type="text" name="cedula" placeholder="Cédula" value="<?php echo htmlspecialchars($u['cedula'] ?? ''); ?>" required>
                <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
              </div>
              <button type="submit" class="btn">Actualizar</button>
            </form>

            <!-- Desactivar docente (admin) -->
            <form method="post" action="index.php?action=deactivate_user" 
                  data-confirm="¿Desactivar este usuario? Ya no podrá iniciar sesión pero se conservarán sus registros."
                  data-confirm-ok="Sí, desactivar"
                  data-confirm-cancel="No, mantener activo">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <button type="submit" class="btn" style="background-color: #f39c12; color: white;">Desactivar</button>
            </form>

          <?php elseif($_SESSION['user']['rol']=='superadmin'): ?>
            <!-- Actualizar datos del admin (superadmin) -->
            <form method="post" action="index.php?action=update_user" class="mb-1">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <div class="form-row">
                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                <input type="text" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($u['usuario']); ?>" required>
              </div>
              <div class="form-row">
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($u['email']); ?>">
                <input type="text" name="cedula" placeholder="Cédula" value="<?php echo htmlspecialchars($u['cedula'] ?? ''); ?>" required>
                <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
              </div>
              <button type="submit" class="btn">Actualizar</button>
            </form>

            <!-- Desactivar admin (superadmin) -->
            <form method="post" action="index.php?action=deactivate_user" 
                  data-confirm="¿Desactivar este administrador? Ya no podrá iniciar sesión pero se conservarán sus registros."
                  data-confirm-ok="Sí, desactivar"
                  data-confirm-cancel="No, mantener activo">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <button type="submit" class="btn" style="background-color: #f39c12; color: white;">Desactivar</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php 
// Add UID management script if user is admin
if(($_SESSION['user']['rol'] ?? '') === 'admin'): 
?>
<script src="/gestad/public/assets/js/uid-management.js"></script>
<?php endif; ?>

<?php include dirname(__DIR__) . "/layout/footer.php"; ?>
