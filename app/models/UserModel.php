<?php
require_once __DIR__ . "/Database.php";

class UserModel {
    public static function create($nombre, $usuario, $password, $rol, $email=null, $cedula=null) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO users(nombre,usuario,password,rol,email,cedula) VALUES(?,?,?,?,?,?)");
        return $stmt->execute([$nombre, $usuario, password_hash($password, PASSWORD_BCRYPT), $rol, $email, $cedula]);
    }

    public static function assignCard($id, $uid) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET uid_tarjeta=? WHERE id=?");
        return $stmt->execute([$uid, $id]);
    }

    public static function findByUID($uid) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE uid_tarjeta=?");
        $stmt->execute([$uid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByUsername($usuario) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE usuario=?");
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function listByRole($rol) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE rol=? AND active=1");
        $stmt->execute([$rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAdmins() {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM users WHERE rol='admin'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByCedula($cedula) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE cedula=?");
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByCedulaExcludingId($cedula, $excludeId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE cedula=? AND id<>?");
        $stmt->execute([$cedula, $excludeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updatePasswordHash($id, $hash) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
        return $stmt->execute([$hash, $id]);
    }

    public static function update($id, $nombre, $usuario, $email, $password = '', $cedula = null) {
        $db = Database::connect();
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET nombre=?, usuario=?, email=?, password=?, cedula=? WHERE id=?");
            return $stmt->execute([$nombre, $usuario, $email, $hash, $cedula, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET nombre=?, usuario=?, email=?, cedula=? WHERE id=?");
            return $stmt->execute([$nombre, $usuario, $email, $cedula, $id]);
        }
    }

    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    public static function deactivate($id) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET active=0 WHERE id=?");
        return $stmt->execute([$id]);
    }
}
