<?php
require_once __DIR__ . "/../models/UserModel.php";
require_once __DIR__ . "/../models/ScheduleModel.php";
require_once __DIR__ . "/../models/Database.php";
require_once __DIR__ . "/../utils/ColombiaValidator.php";

class UserController {
    public static function createAdmin($nombre, $usuario, $password, $email, $cedula = null) {
        if (($_SESSION['user']['rol'] ?? '') !== 'superadmin') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        
        
        $nombre = trim($nombre);
        $usuario = trim($usuario);
        $email = trim($email);
        $cedula = trim((string)$cedula);
        
        // Validar cédula
        if ($cedula === '') { 
            $_SESSION['flash_error'] = 'La cédula es un campo requerido'; 
            return false; 
        }
        
        // Validar formato de cédula colombiana
        $validacionCedula = ColombiaValidator::validarCedula($cedula);
        if (!$validacionCedula['isValid']) {
            $_SESSION['flash_error'] = $validacionCedula['message'];
            return false;
        }
        

        // Verificar si la cédula ya existe
        if (ColombiaValidator::cedulaExiste($cedula)) { 
            $_SESSION['flash_error'] = 'La cédula ingresada ya está registrada en el sistema'; 
            return false; 
        }
        // Verificar si el usuario ya existe, si existe mostrar un mensaje de error
        if (UserModel::findByUsername($usuario)) { 
            $_SESSION['flash_error'] = 'El nombre de usuario no es valido. Por favor, elija otro.'; 
            return false; 
        }
        $ok = UserModel::create($nombre, $usuario, $password, "admin", $email, $cedula);
        if ($ok) { $_SESSION['flash_success'] = 'Administrador creado correctamente'; }
        return $ok;
    }

    public static function createDocente($nombre, $usuario, $password, $email, $cedula = null) {
        if (($_SESSION['user']['rol'] ?? '') !== 'admin') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        
        $nombre = trim($nombre);
        $usuario = trim($usuario);
        $email = trim($email);
        $cedula = trim((string)$cedula);
        
        // Validar cédula
        if ($cedula === '') { 
            $_SESSION['flash_error'] = 'La cédula es un campo requerido'; 
            return false; 
        }
        
        // Validar formato de cédula colombiana
        $validacionCedula = ColombiaValidator::validarCedula($cedula);
        if (!$validacionCedula['isValid']) {
            $_SESSION['flash_error'] = $validacionCedula['message'];
            return false;
        }
        
        // Verificar si la cédula ya existe
        if (ColombiaValidator::cedulaExiste($cedula)) { 
            $_SESSION['flash_error'] = 'La cédula ingresada ya está registrada en el sistema'; 
            return false; 
        }
        // Verificar si el usuario ya existe
        if (UserModel::findByUsername($usuario)) { 
            $_SESSION['flash_error'] = 'El nombre de usuario no es valido. Por favor, elija otro.'; 
            return false; 
        }
        $ok = UserModel::create($nombre, $usuario, $password, "docente", $email, $cedula);
        if ($ok) { $_SESSION['flash_success'] = 'Docente creado correctamente'; }
        return $ok;
    }

    public static function assignCard($id, $uid) {
        if (($_SESSION['user']['rol'] ?? '') !== 'admin') { $_SESSION['flash_error'] = 'No autorizado'; return false; }
        $uid = strtoupper(trim($uid));
        if ($uid === '') { $_SESSION['flash_error'] = 'Debe seleccionar un UID'; return false; }
        // Validar que no esté asignado a otro usuario
        $owner = UserModel::findByUID($uid);
        if ($owner && (int)$owner['id'] !== (int)$id) {
            $_SESSION['flash_error'] = 'El UID seleccionado ya está asignado a: ' . htmlspecialchars($owner['nombre']);
            return false;
        }
        $ok = UserModel::assignCard($id, $uid);
        if ($ok) { $_SESSION['flash_success'] = 'UID asignado correctamente'; }
        return $ok;
    }


    public static function assignSchedule($id, $hora) {
        if ($_SESSION['user']['rol'] !== 'admin') die("No autorizado");
        return ScheduleModel::AssignSchedule($id, $hora);
    }

    public static function updateUser($id, $nombre, $usuario, $email, $password, $cedula = null) {
        $actorRol = $_SESSION['user']['rol'] ?? '';
        $target = UserModel::findById($id);
        if (!$target) { 
            $_SESSION['flash_error'] = 'Usuario no encontrado'; 
            return false; 
        }
        
        $cedula = trim((string)$cedula);
        
        // Validar cédula
        if ($cedula === '') { 
            $_SESSION['flash_error'] = 'La cédula es un campo requerido'; 
            return false; 
        }
        
        // Validar formato de cédula colombiana
        $validacionCedula = ColombiaValidator::validarCedula($cedula);
        if (!$validacionCedula['isValid']) {
            $_SESSION['flash_error'] = $validacionCedula['message'];
            return false;
        }
        
        // Verificar si la cédula ya existe (excluyendo al usuario actual)
        if (ColombiaValidator::cedulaExiste($cedula, $id)) { 
            $_SESSION['flash_error'] = 'La cédula ingresada ya está registrada en el sistema'; 
            return false; 
        }

        // superadmin solo puede gestionar admins; admin solo docentes
        if ($actorRol === 'superadmin' && $target['rol'] !== 'admin') { $_SESSION['flash_error'] = 'No autorizado'; return false; }
        if ($actorRol === 'admin' && $target['rol'] !== 'docente') { $_SESSION['flash_error'] = 'No autorizado'; return false; }
        if (!in_array($actorRol, ['admin','superadmin'])) { $_SESSION['flash_error'] = 'No autorizado'; return false; }

        $nombre = trim($nombre);
        $usuario = trim($usuario);
        $email = trim($email);
        $password = trim($password);
        $cedula = trim((string)$cedula);
        if ($cedula === '') { $_SESSION['flash_error'] = 'Cédula requerida'; return false; }
        if (UserModel::findByCedulaExcludingId($cedula, $id)) { $_SESSION['flash_error'] = 'Cédula ya registrada'; return false; }

        $ok = UserModel::update($id, $nombre, $usuario, $email, $password, $cedula);
        if ($ok) { $_SESSION['flash_success'] = 'Usuario actualizado'; }
        return $ok;
    }

    /**
     * Elimina un usuario del sistema (solo si no tiene registros de asistencia)
     */
    public static function deleteUser($id) {
        $actorRol = $_SESSION['user']['rol'] ?? '';
        $target = UserModel::findById($id);
        if (!$target) { 
            $_SESSION['flash_error'] = 'Usuario no encontrado'; 
            return false; 
        }

        // No permitir eliminarse a sí mismo
        if ((int)$id === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash_error'] = 'No puedes eliminarte a ti mismo';
            return false;
        }

        // Verificar permisos: superadmin solo puede gestionar admins; admin solo docentes
        if ($actorRol === 'superadmin' && $target['rol'] !== 'admin') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        if ($actorRol === 'admin' && $target['rol'] !== 'docente') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        if (!in_array($actorRol, ['admin','superadmin'])) { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }

        // Verificar si el usuario tiene registros de asistencia
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE docente_id = ?");
        $stmt->execute([$id]);
        $hasAttendance = $stmt->fetchColumn() > 0;

        if ($hasAttendance) {
            $_SESSION['flash_error'] = 'No se puede eliminar el usuario porque tiene registros de asistencia. ';
            $_SESSION['flash_error'] .= '<a href="?action=deactivate_user&id=' . $id . '" class="alert-link">¿Desea desactivar el usuario en su lugar?</a>';
            return false;
        }

        // Iniciar transacción para asegurar la integridad de los datos
        $db->beginTransaction();

        try {
            // 1. Eliminar registros relacionados en la tabla notifications
            $stmt = $db->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // 2. Eliminar horarios del docente (si existe la tabla schedules)
            try {
                $stmt = $db->prepare("DELETE FROM schedules WHERE docente_id = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) {
                // Ignorar si la tabla no existe o hay un error
                error_log("Warning: Could not delete from schedules: " . $e->getMessage());
            }
            
            // 3. Finalmente, eliminar el usuario
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            // Confirmar la transacción
            $db->commit();
            
            $_SESSION['flash_success'] = 'Usuario eliminado correctamente';
            return true;
            
        } catch (Exception $e) {
            // En caso de error, revertir la transacción
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Error al eliminar usuario: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error al eliminar el usuario: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Desactiva un usuario en lugar de eliminarlo
     */
    public static function deactivateUser($id) {
        $actorRol = $_SESSION['user']['rol'] ?? '';
        $target = UserModel::findById($id);
        
        if (!$target) { 
            $_SESSION['flash_error'] = 'Usuario no encontrado'; 
            return false; 
        }

        // No permitir desactivarse a sí mismo
        if ((int)$id === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash_error'] = 'No puedes desactivar tu propia cuenta!!!';
            return false;
        }

        // Verificar permisos
        if ($actorRol === 'superadmin' && $target['rol'] !== 'admin') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        if ($actorRol === 'admin' && $target['rol'] !== 'docente') { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }
        if (!in_array($actorRol, ['admin','superadmin'])) { 
            $_SESSION['flash_error'] = 'No autorizado'; 
            return false; 
        }

        // Desactivar el usuario
        $ok = UserModel::deactivate($id);
        
        if ($ok) {
            $_SESSION['flash_success'] = 'Usuario desactivado correctamente. El usuario ya no podrá iniciar sesión pero se conservarán sus registros.';
        } else {
            $_SESSION['flash_error'] = 'Error al desactivar el usuario';
        }
        
        return $ok;
    }
}