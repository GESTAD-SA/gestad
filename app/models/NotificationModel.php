<?php
require_once "Database.php";
require_once "UserModel.php";

class NotificationModel {
    public static function create($docente_id, $mensaje) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO notifications(docente_id, mensaje) VALUES(?,?)");
        $stmt->execute([$docente_id, $mensaje]);

        $admins = UserModel::getAdmins();
        // Envío nativo usando mail()
        foreach ($admins as $admin) {
            if (!empty($admin['email'])) {
                @mail($admin['email'], "Notificación de asistencia", $mensaje);
            }
        }
        return true;
    }

    public static function listAll() {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT n.*, u.nombre 
            FROM notifications n
            JOIN users u ON u.id = n.docente_id
            ORDER BY n.fecha DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}