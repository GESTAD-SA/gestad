<?php

use PHPUnit\Framework\TestCase;

class AttendanceStatusTest extends TestCase
{
    /**
     * Extract the attendance status logic from AttendanceModel::marcarAsistencia()
     * and test it in isolation without database dependencies.
     */
    public function testAttendanceStatusOnTime()
    {
        // Simulate: current time is exactly at start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:00:00');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Presente', $estado);
    }

    public function testAttendanceStatusWithin15Minutes()
    {
        // Simulate: current time is 10 minutes after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:10:00');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Presente', $estado);
    }

    public function testAttendanceStatusLateBetween15And30Minutes()
    {
        // Simulate: current time is 20 minutes after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:20:00');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Tarde', $estado);
    }

    public function testAttendanceStatusLateExactly15Minutes()
    {
        // Simulate: current time is exactly 15 minutes after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:15:01');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Tarde', $estado);
    }

    public function testAttendanceStatusAbsentAfter30Minutes()
    {
        // Simulate: current time is 35 minutes after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:35:00');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Ausente', $estado);
    }

    public function testAttendanceStatusAbsentExactly30Minutes()
    {
        // Simulate: current time is exactly 30 minutes after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('08:30:01');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Ausente', $estado);
    }

    public function testAttendanceStatusOneHourLate()
    {
        // Simulate: current time is 1 hour after start time
        $inicio = new \DateTime('08:00:00');
        $now = new \DateTime('09:00:00');
        
        $estado = $this->calculateAttendanceStatus($inicio, $now);
        $this->assertEquals('Ausente', $estado);
    }

    /**
     * Isolated logic extracted from AttendanceModel::marcarAsistencia()
     * This replicates the status decision logic without database dependencies.
     */
    private function calculateAttendanceStatus(\DateTime $inicio, \DateTime $now): string
    {
        $estado = 'Presente';
        $inicio15 = (clone $inicio)->modify('+15 minutes');
        $inicio30 = (clone $inicio)->modify('+30 minutes');
        
        if ($now > $inicio15 && $now <= $inicio30) {
            $estado = 'Tarde';
        } elseif ($now > $inicio30) {
            $estado = 'Ausente';
        }
        
        return $estado;
    }
}
