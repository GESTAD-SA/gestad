<?php
require_once __DIR__ . "/../models/UserModel.php";

class AuthController {
    public static function login($usuario, $password) {
        // Limpiar cualquier mensaje de error previo
        unset($_SESSION['flash_error']);
        
        // Validar campos vacíos
        if (empty(trim($usuario)) || empty(trim($password))) {
            $_SESSION['flash_error'] = 'Por favor ingrese su usuario y contraseña';
            return false;
        }
        
        $user = UserModel::findByUsername(trim($usuario));
        
        // Usuario no encontrado
        if (!$user) {
            // Usamos un mensaje generico por seguridad
            $_SESSION['flash_error'] = 'Usuario o contraseña incorrectos';
            return false;
        }
        
        // Verificar si la cuenta está activa
        if (isset($user['active']) && $user['active'] == 0) {
            $_SESSION['flash_error'] = 'Su cuenta está inactiva. Por favor contacte al administrador.';
            return false;
        }
        
        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            // Actualizar hash si es necesario
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                UserModel::updatePasswordHash($user['id'], $newHash);
                $user['password'] = $newHash;
            }
            
            // Iniciar sesión
            $_SESSION['user'] = $user;
            
            // Limpiar el contador de intentos fallidos si existe
            if (isset($_SESSION['login_attempts'])) {
                unset($_SESSION['login_attempts']);
            }
            return true;
        }
        
        // Contraseña incorrecta
        $_SESSION['flash_error'] = 'Usuario o contraseña incorrectos.';
        
        return false;
    }

    public static function logout() {
        session_start();
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }
}