<?php
// Script temporal para crear un usuario SUPERADMIN.
// USO (solo una vez):
// http://localhost/gestad/public/dev/create_superadmin.php
// Opcional (personalizar):
// http://localhost/gestad/public/dev/create_superadmin.php?usuario=superadminn&password=super123&nombre=Superadminn&email=super@example.com

require_once __DIR__ . '/../../app/models/UserModel.php';

// Valores por defecto solicitados
$usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : 'superadminn';
$password = isset($_GET['password']) ? trim($_GET['password']) : 'super123';
$nombre   = isset($_GET['nombre'])   ? trim($_GET['nombre'])   : 'superadminn';
$email    = isset($_GET['email'])    ? trim($_GET['email'])    : 'super@example.com';

if ($usuario === '' || $password === '' || $nombre === '') {
    http_response_code(400);
    echo "Faltan parámetros obligatorios (usuario, password, nombre)";
    exit;
}

try {
    $existente = UserModel::findByUsername($usuario);
    if ($existente) {
        echo "El usuario '{$usuario}' ya existe. Nada que hacer.";
        exit;
    }
    $ok = UserModel::create($nombre, $usuario, $password, 'superadmin', $email);
    if ($ok) {
        echo "Usuario superadmin creado: usuario={$usuario}.\n";
        echo "IMPORTANTE: Elimina este archivo por seguridad: public/dev/create_superadmin.php";
    } else {
        http_response_code(500);
        echo "No se pudo crear el superadmin";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
