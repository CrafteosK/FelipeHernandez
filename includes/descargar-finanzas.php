<?php
require('../fpdf/fpdf.php');
include '../bd/conexion.php';

class PDF_Finanzas extends FPDF {
    protected $total_ingresos;
    protected $total_gastos;

    function __construct($orientation='P', $unit='mm', $size='Letter') {
        parent::__construct($orientation, $unit, $size);
        $this->total_ingresos = 0;
        $this->total_gastos = 0;
    }

    function setTotals($ingresos, $gastos) {
        $this->total_ingresos = $ingresos;
        $this->total_gastos = $gastos;
    }

    function Header() {
        $this->Image('../imagenes/felipeHernandez.jpg', 188, 8, 15);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Reporte General de Finanzas'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'U.E.E. Br "Felipe Hernandez"', 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Fecha de Emisión: ') . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Ln(10);
        
        $this->SetFillColor(50, 50, 50);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(25, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Tipo', 1, 0, 'C', true);
        $this->Cell(50, 10, utf8_decode('Categoría'), 1, 0, 'C', true);
        $this->Cell(70, 10, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(31, 10, 'Monto', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}

// Lógica de filtrado por fecha (similar a gastos.php)
$filtroFecha = $_GET['filtroFecha'] ?? 'todos';
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';

$whereClause = "";
if ($filtroFecha == 'hoy') {
    $whereClause = " WHERE fecha = CURDATE() ";
} elseif ($filtroFecha == 'ayer') {
    $whereClause = " WHERE fecha = DATE_SUB(CURDATE(), INTERVAL 1 DAY) ";
} elseif ($filtroFecha == '7dias') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
} elseif ($filtroFecha == '15dias') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 15 DAY) ";
} elseif ($filtroFecha == 'mes') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
} elseif ($filtroFecha == '3meses') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ";
} elseif ($filtroFecha == 'ano') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ";
} elseif ($filtroFecha == 'personalizado' && !empty($fechaInicio) && !empty($fechaFin)) {
    $whereClause = " WHERE fecha BETWEEN '$fechaInicio' AND '$fechaFin' ";
}

$pdf = new PDF_Finanzas('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$query = "SELECT * FROM finanzas $whereClause ORDER BY fecha ASC";
$res = mysqli_query($enlace, $query);
$total_i = 0; $total_g = 0;

while($row = mysqli_fetch_array($res)) {
    $pdf->Cell(25, 8, $row['fecha'], 1, 0, 'C');
    $pdf->Cell(20, 8, strtoupper($row['tipo']), 1, 0, 'C');
    $pdf->Cell(50, 8, utf8_decode($row['categoria']), 1, 0, 'L');
    $pdf->Cell(70, 8, utf8_decode(substr($row['descripcion'], 0, 40)), 1, 0, 'L');
    
    if($row['tipo'] == 'ingreso') {
        $pdf->SetTextColor(0, 128, 0);
        $total_i += $row['monto'];
    } else {
        $pdf->SetTextColor(200, 0, 0);
        $total_g += $row['monto'];
    }
    $pdf->Cell(31, 8, '$ ' . number_format($row['monto'], 2), 1, 1, 'R');
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->setTotals($total_i, $total_g); // Actualizar los totales en el objeto PDF

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(245, 245, 245);
$pdf->Cell(165, 10, 'TOTAL INGRESOS (+)', 1, 0, 'R', true);
$pdf->SetTextColor(0, 128, 0);
$pdf->Cell(31, 10, '$ ' . number_format($total_i, 2), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(165, 10, 'TOTAL GASTOS (-)', 1, 0, 'R', true);
$pdf->SetTextColor(200, 0, 0);
$pdf->Cell(31, 10, '$ ' . number_format($total_g, 2), 1, 1, 'R', true);

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(165, 12, 'SALDO DISPONIBLE', 1, 0, 'R', true);
$pdf->Cell(31, 12, '$ ' . number_format($total_i - $total_g, 2), 1, 1, 'R', true);

$pdf->Output('I', 'Reporte_Finanzas.pdf');
?>