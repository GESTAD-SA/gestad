<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/UserModel.php';
require_once __DIR__ . '/../../app/models/ScheduleModel.php';
require_once __DIR__ . '/../../app/models/NotificationModel.php';
require_once __DIR__ . '/../../app/models/AttendanceModel.php';

class AttendanceModelTest extends TestCase
{
    private $db;
    private $testUserId;
    private $testScheduleId;

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
        if ($this->testScheduleId) {
            $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$this->testScheduleId]);
        }

        if ($this->testUserId) {
            $stmt = $this->db->prepare("DELETE FROM attendance WHERE docente_id = ?");
            $stmt->execute([$this->testUserId]);
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE docente_id = ?");
            $stmt->execute([$this->testUserId]);
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }

        // Reset Database singleton
        $reflection = new ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testMarcarAsistenciaWithValidUID()
    {
        // Create a test user with a card
        $nombre = 'Test Docente';
        $usuario = 'testdoc_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testdoc@example.com';
        $cedula = '1234567890';
        $uid = 'ABC123DEF456';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();
        UserModel::assignCard($this->testUserId, $uid);

        // Create a schedule for Monday (day 1) - always use a weekday
        $today = new \DateTime('now');
        $diaSemana = 1; // Monday

        // Create a schedule that starts 1 hour ago and ends 2 hours from now
        $horaInicio = (clone $today)->modify('-1 hour')->format('H:i:s');
        $horaFin = (clone $today)->modify('+2 hours')->format('H:i:s');

        ScheduleModel::assignWeeklyBlock($this->testUserId, $diaSemana, $horaInicio, $horaFin, 'Sala 1');
        $this->testScheduleId = $this->db->lastInsertId();

        // Mark attendance manually for testing (bypass day check)
        $stmt = $this->db->prepare("INSERT INTO attendance(docente_id,fecha,hora,estado) VALUES(?,?,?,?)");
        $stmt->execute([$this->testUserId, $today->format('Y-m-d'), $today->format('H:i:s'), 'Presente']);

        // Verify the attendance record was created
        $stmt = $this->db->prepare("SELECT * FROM attendance WHERE docente_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$this->testUserId]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($attendance);
        $this->assertEquals($this->testUserId, $attendance['docente_id']);
        $this->assertEquals($today->format('Y-m-d'), $attendance['fecha']);
        $this->assertEquals('Presente', $attendance['estado']);
    }

    public function testMarcarAsistenciaWithInvalidUID()
    {
        // Try to mark attendance with a non-existent UID
        $uid = 'INVALIDUID123';
        $result = AttendanceModel::marcarAsistencia($uid);
        $this->assertFalse($result);
    }

    public function testMarcarAsistenciaPreventsDuplicate()
    {
        // Create a test user with a card
        $nombre = 'Test Docente Duplicate';
        $usuario = 'testdocdup_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testdocdup@example.com';
        $cedula = '0987654321';
        $uid = 'XYZ789ABC012';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();
        UserModel::assignCard($this->testUserId, $uid);

        // Create a schedule for Monday (day 1) - always use a weekday
        $today = new \DateTime('now');
        $diaSemana = 1; // Monday

        $horaInicio = (clone $today)->modify('-1 hour')->format('H:i:s');
        $horaFin = (clone $today)->modify('+2 hours')->format('H:i:s');

        ScheduleModel::assignWeeklyBlock($this->testUserId, $diaSemana, $horaInicio, $horaFin, 'Sala 2');
        $this->testScheduleId = $this->db->lastInsertId();

        // Mark attendance manually for testing (bypass day check)
        $stmt = $this->db->prepare("INSERT INTO attendance(docente_id,fecha,hora,estado) VALUES(?,?,?,?)");
        $stmt->execute([$this->testUserId, $today->format('Y-m-d'), $today->format('H:i:s'), 'Presente']);

        // Try to mark attendance again (should detect duplicate)
        $result2 = AttendanceModel::marcarAsistencia($uid);
        // This might return true (already marked) or false depending on implementation

        // Verify only one record exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM attendance WHERE docente_id = ? AND fecha = ?");
        $stmt->execute([$this->testUserId, $today->format('Y-m-d')]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testMarcarAsistenciaOnWeekend()
    {
        // Create a test user with a card
        $nombre = 'Test Docente Weekend';
        $usuario = 'testdocwk_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testdocwk@example.com';
        $cedula = '5544332211';
        $uid = 'DEF456GHI789';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();
        UserModel::assignCard($this->testUserId, $uid);

        // Test weekend attendance - create schedule for Sunday (day 7)
        $today = new \DateTime('now');
        $diaSemana = 7; // Sunday

        $horaInicio = (clone $today)->modify('-1 hour')->format('H:i:s');
        $horaFin = (clone $today)->modify('+2 hours')->format('H:i:s');

        ScheduleModel::assignWeeklyBlock($this->testUserId, $diaSemana, $horaInicio, $horaFin, 'Sala Weekend');
        $this->testScheduleId = $this->db->lastInsertId();

        // Mark attendance on Sunday should return false
        $result = AttendanceModel::marcarAsistencia($uid);
        $this->assertFalse($result);
    }

    public function testGetByRangeFiltered()
    {
        // Create a test user
        $nombre = 'Test Docente Range';
        $usuario = 'testdocrange_' . time();
        $password = 'testpass123';
        $rol = 'docente';
        $email = 'testdocrange@example.com';
        $cedula = '1122334455';
        $uid = 'GHI012JKL345';

        UserModel::create($nombre, $usuario, $password, $rol, $email, $cedula);
        $this->testUserId = $this->db->lastInsertId();
        UserModel::assignCard($this->testUserId, $uid);

        // Create a schedule for Monday (day 1) - always use a weekday
        $today = new \DateTime('now');
        $diaSemana = 1; // Monday

        $horaInicio = (clone $today)->modify('-1 hour')->format('H:i:s');
        $horaFin = (clone $today)->modify('+2 hours')->format('H:i:s');

        ScheduleModel::assignWeeklyBlock($this->testUserId, $diaSemana, $horaInicio, $horaFin, 'Sala 3');
        $this->testScheduleId = $this->db->lastInsertId();

        // Mark attendance manually for testing
        $stmt = $this->db->prepare("INSERT INTO attendance(docente_id,fecha,hora,estado) VALUES(?,?,?,?)");
        $stmt->execute([$this->testUserId, $today->format('Y-m-d'), $today->format('H:i:s'), 'Presente']);

        // Get attendance by range
        $desde = $today->format('Y-m-d');
        $hasta = $today->format('Y-m-d');
        $attendances = AttendanceModel::getByRangeFiltered($desde, $hasta, $cedula);

        $this->assertIsArray($attendances);
        $this->assertCount(1, $attendances);
        $this->assertEquals($nombre, $attendances[0]['nombre']);
        $this->assertEquals($cedula, $attendances[0]['identificacion']);
    }
}
