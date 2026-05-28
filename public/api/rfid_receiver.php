<?php
// Configurar zona horaria
date_default_timezone_set('America/Bogota');

require_once "../../app/models/AttendanceModel.php";
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $uid = $_POST['uid'] ?? null;
    if ($uid) {
        AttendanceModel::marcarAsistencia($uid);
        echo "OK";
    } else {
        echo "UID requerido";
    }
}
