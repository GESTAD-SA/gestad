<?php
require_once __DIR__ . "/../models/NotificationModel.php";

class NotificationController {
    public static function listar() {
        return NotificationModel::listAll();
    }
}
