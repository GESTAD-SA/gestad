<?php
// Script para limpiar completamente la base de datos, dejando solo al superadministrador actual

// Iniciar sesión para acceder a las variables de sesión
session_start();

// Verificar si el usuario es superadmin
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'superadmin') {
    die('Acceso denegado. Solo el superadministrador puede ejecutar este script.');
}

// Incluir el modelo de base de datos
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/UserModel.php';

// Obtener el ID y email del superusuario actual
$superadmin_id = $_SESSION['user']['id'];
$superadmin_email = $_SESSION['user']['email'];

try {
    // Iniciar conexión
    $pdo = Database::connect();
    
    // Verificar conexión
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Obtener el email del superadmin actual
    $superadmin_data = UserModel::findById($superadmin_id);
    if (!$superadmin_data) {
        throw new Exception("No se encontró el superusuario actual");
    }
    
    // Lista de tablas a limpiar (en el orden correcto para evitar errores de clave foránea)
    $tables = [
        'asistencias',
        'horarios',
        'asignaturas',
        'notificaciones',
        'registros_tarjeta',
        'rfid_pool',
        'solicitudes_estudiantes',
        'tokens_reset',
        'usuarios_tokens',
        'users'
    ];
    
    // Desactivar restricciones de clave foránea temporalmente
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // 1. Vaciar las tablas
    foreach ($tables as $table) {
        if ($table === 'users') continue; // La manejaremos aparte
        try {
            $pdo->exec("TRUNCATE TABLE `$table`");
        } catch (Exception $e) {
            // Si falla el TRUNCATE, intentar DELETE
            try {
                $pdo->exec("DELETE FROM `$table`");
            } catch (Exception $e) {
                // Si la tabla no existe, continuar
                if (strpos($e->getMessage(), "doesn't exist") === false) {
                    throw $e;
                }
            }
        }
    }
    
    // 2. Limpiar la tabla de usuarios, dejando solo al superadmin actual
    $pdo->exec("DELETE FROM `users` WHERE id != $superadmin_id");
    
    // 3. Restaurar el superadmin con valores por defecto
    $columns = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
    
    // Construir la consulta dinámicamente basada en las columnas existentes
    $updateFields = [
        "nombre = 'Super Administrador'",
        "usuario = 'superadminn'",
        "password = ?",
        "email = ?",
        "cedula = '00000000'",
        "active = 1",
        "rol = 'superadmin'"
    ];
    
    // Solo incluir uid_tarjeta si la columna existe
    if (in_array('uid_tarjeta', $columns)) {
        $updateFields[] = "uid_tarjeta = NULL";
    }
    
    // Construir y preparar la consulta
    $sql = "UPDATE `users` SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    // Hashear la contraseña por defecto
    $hashed_password = password_hash('super123', PASSWORD_BCRYPT);
    
    // Ejecutar la consulta
    $stmt->execute([$hashed_password, $superadmin_data['email'], $superadmin_id]);
    
    // 4. Reiniciar los contadores de auto-incremento
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        try {
            $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
        } catch (Exception $e) {
            // Continuar con la siguiente tabla si hay error
            continue;
        }
    }
    
    // Reactivar restricciones de clave foránea
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    // Cerrar sesión para forzar nuevo inicio
    session_destroy();
    
    // Mostrar mensaje de éxito
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Limpieza completada</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 800px; 
                margin: 0 auto; 
                padding: 20px;
                line-height: 1.6;
                background-color: #f5f5f5;
            }
            .card {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin-top: 50px;
            }
            h1 { 
                color: #2c3e50; 
                margin-top: 0;
                border-bottom: 2px solid #eee;
                padding-bottom: 10px;
            }
            .success { 
                color: #27ae60;
                font-weight: bold;
            }
            .credentials {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                border-left: 4px solid #3498db;
                margin: 20px 0;
            }
            .btn {
                display: inline-block;
                background: #3498db;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .btn:hover {
                background: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>✅ Limpieza completada con éxito</h1>
            
            <p class="success">Se ha reiniciado completamente la base de datos del sistema.</p>
            
            <div class="credentials">
                <p>Se ha mantenido el superadministrador con las siguientes credenciales:</p>
                <p><strong>Usuario:</strong> superadminn</p>
                <p><strong>Contraseña:</strong> super123</p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($superadmin_data['email']); ?></p>
            </div>
            
            <p>Por seguridad, se ha cerrado la sesión actual. Por favor, inicia sesión nuevamente.</p>
            
            <a href="../../index.php" class="btn">Ir a la página de inicio de sesión</a>
            
            <p style="margin-top: 30px; font-size: 0.9em; color: #7f8c8d;">
                <strong>Nota:</strong> Se recomienda cambiar la contraseña después del primer inicio de sesión.
            </p>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // En caso de error, mostrar mensaje
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error en la limpieza</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 800px; 
                margin: 0 auto; 
                padding: 20px;
                line-height: 1.6;
                background-color: #f5f5f5;
            }
            .card {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin-top: 50px;
            }
            h1 { 
                color: #e74c3c; 
                margin-top: 0;
                border-bottom: 2px solid #f5c6cb;
                padding-bottom: 10px;
            }
            .error { 
                color: #e74c3c;
                font-weight: bold;
            }
            .btn {
                display: inline-block;
                background: #3498db;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            pre {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>❌ Error durante la limpieza</h1>
            
            <p class="error"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <p>No se realizaron cambios en la base de datos.</p>
            
            <?php if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false): ?>
                <hr>
                <h3>Detalles del error (solo visible en localhost):</h3>
                <pre><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
            <?php endif; ?>
            
            <p><a href="../../index.php" class="btn">Volver al inicio</a></p>
        </div>
    </body>
    </html>
    <?php
}
