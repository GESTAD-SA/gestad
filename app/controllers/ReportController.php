<?php
// Asegurarse de que no haya salida antes de los headers
if (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . "/../models/AttendanceModel.php";
require_once __DIR__ . "/../../vendor/autoload.php";

class ReportController {
    public static function exportCSV($desde, $hasta) {
        $data = AttendanceModel::getByRange($desde, $hasta);
        
        // Crear una nueva instancia de TCPDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configuración del documento
        $pdf->SetCreator('Sistema de Asistencia');
        $pdf->SetAuthor('Sistema de Asistencia');
        $pdf->SetTitle('Reporte de Asistencias');
        $pdf->SetSubject('Reporte de Asistencias');
        
        // Agregar una página
        $pdf->AddPage();
        
        // Agregar título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Reporte de Asistencias', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Desde: ' . $desde . '  Hasta: ' . $hasta, 0, 1, 'C');
        $pdf->Ln(10);
        
        // Encabezados de la tabla
        $header = array('Docente', 'Fecha', 'Hora', 'Estado', 'Observación');
        
        // Configurar fuente para la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        
        // Ancho de las columnas
        $w = array(50, 30, 25, 30, 55);
        
        // Imprimir encabezados
        for($i = 0; $i < count($header); $i++) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        }
        $pdf->Ln();
        
        // Imprimir datos
        $pdf->SetFont('helvetica', '', 9);
        foreach($data as $row) {
            $pdf->Cell($w[0], 6, $row['nombre'], 'LR', 0, 'L');
            $pdf->Cell($w[1], 6, $row['fecha'], 'LR', 0, 'C');
            $pdf->Cell($w[2], 6, $row['hora'], 'LR', 0, 'C');
            $pdf->Cell($w[3], 6, $row['estado'], 'LR', 0, 'C');
            $pdf->Cell($w[4], 6, $row['observacion'], 'LR', 0, 'L');
            $pdf->Ln();
        }
        
        // Cerrar la tabla
        $pdf->Cell(array_sum($w), 0, '', 'T');
        
        // Generar el PDF
        $pdf->Output('reporte_asistencias.pdf', 'D');
        exit;
    }
}
