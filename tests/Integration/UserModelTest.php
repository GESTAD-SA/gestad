<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/UserModel.php';

class UserModelTest extends TestCase
{
    private $db;
    private $testUserId;

    protected function setUp(): void
    {
        // Reset Database singleton to use test database
        $reflection = new ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);

        // Connect to test database
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testUserId) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }

        // Reset Database singleton
        $reflection = new ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testCreateUser()
    {
        $nombre = 'Test User';
        $usuario = 'testuser_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'test@example.com';
        $cedula = '1234567890';

        $result = UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->assertTrue($result);

        // Get the inserted ID
        $this->testUserId = $this->db->lastInsertId();
        $this->assertIsNumeric($this->testUserId);
    }

    public function testFindByUsername()
    {
        // Create a test user
        $nombre = 'Test User Find';
        $usuario = 'testfind_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testfind@example.com';
        $cedula = '0987654321';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();

        // Find the user by username
        $user = UserModel::findByUsername($usuario);

        $this->assertIsArray($user);
        $this->assertEquals($nombre, $user['nombre']);
        $this->assertEquals($usuario, $user['usuario']);
        $this->assertEquals($rol, $user['rol']);
        $this->assertEquals($email, $user['email']);
        $this->assertEquals($cedula, $user['cedula']);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testFindByUsernameNonExistent()
    {
        $user = UserModel::findByUsername('nonexistent_user_' . time());
        $this->assertFalse($user);
    }

    public function testAssignCard()
    {
        // Create a test user
        $nombre = 'Test User Card';
        $usuario = 'testcard_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testcard@example.com';
        $cedula = '1122334455';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();

        // Assign a card
        $uid = 'ABC123DEF456';
        $result = UserModel::assignCard($this->testUserId, $uid);
        $this->assertTrue($result);

        // Verify the card was assigned
        $user = UserModel::findById($this->testUserId);
        $this->assertEquals($uid, $user['uid_tarjeta']);
    }

    public function testFindByUID()
    {
        // Create a test user with a card
        $nombre = 'Test User UID';
        $usuario = 'testuid_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testuid@example.com';
        $cedula = '5544332211';
        $uid = 'XYZ789ABC012';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();
        UserModel::assignCard($this->testUserId, $uid);

        // Find by UID
        $user = UserModel::findByUID($uid);

        $this->assertIsArray($user);
        $this->assertEquals($nombre, $user['nombre']);
        $this->assertEquals($uid, $user['uid_tarjeta']);
    }

    public function testFindByCedula()
    {
        // Create a test user
        $nombre = 'Test User Cedula';
        $usuario = 'testcedula_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testcedula@example.com';
        $cedula = '9988776655';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();

        // Find by cedula
        $user = UserModel::findByCedula($cedula);

        $this->assertIsArray($user);
        $this->assertEquals($nombre, $user['nombre']);
        $this->assertEquals($cedula, $user['cedula']);
    }
}
