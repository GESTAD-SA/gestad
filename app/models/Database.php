<?php
class Database {
    private static $instance = null;
    public static function connect() {
        if (self::$instance == null) {
            // Cargar variables desde .env si existe
            $envPath = __DIR__ . '/../../.env';
            $env = [];
            if (is_file($envPath) && is_readable($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(ltrim($line), '#') === 0) continue;
                    if (!str_contains($line, '=')) continue;
                    [$k, $v] = array_map('trim', explode('=', $line, 2));
                    // eliminar posibles comillas
                    $v = trim($v, "\"' ");
                    $env[$k] = $v;
                }
            }

            $host = $env['DB_HOST'] ?? 'localhost';
            $name = $env['DB_NAME'] ?? 'rfid_system';
            $user = $env['DB_USER'] ?? 'root';
            $pass = $env['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            // alinear zona horaria de la sesion MySQL con la de PHP (para que NOW() coincida)
            $offset = (new DateTime('now'))->format('P'); // Ej: -05:00
            try {
                self::$instance->exec("SET time_zone = '" . $offset . "'");
                // Tambien establecer la zona horaria por nombre para mayor compatibilidad
                $timezone = date_default_timezone_get();
                self::$instance->exec("SET time_zone = '" . $timezone . "'");
            } catch (Throwable $e) {
                // Si falla, intentar el offset numerico
                try {
                    self::$instance->exec("SET time_zone = '" . $offset . "'");
                } catch (Throwable $e2) {
                    // silencioso si falla; MySQL usara su zona por defecto
                }
            }
        }
        return self::$instance;
    }
}