<?php
require('../fpdf/fpdf.php');
include '../bd/conexion.php';

$id = $_GET['id'];
$res = mysqli_query($enlace, "SELECT * FROM finanzas WHERE id = '$id'");
$data = mysqli_fetch_assoc($res);

class PDF_Recibo extends FPDF {
    function Header() {
        $this->Image('../imagenes/felipeHernandez.jpg', 10, 8, 20);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30);
        $this->Cell(0, 10, utf8_decode('U.E.E. BR. "FELIPE HERNÁNDEZ"'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(30);
        $this->Cell(0, 5, utf8_decode('CONTROL DE FINANZAS INTERNAS'), 0, 1, 'L');
        $this->Ln(10);
    }
}

$pdf = new PDF_Recibo('P', 'mm', array(140, 210)); // Tamaño media carta
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('RECIBO DE ' . strtoupper($data['tipo'])), 1, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 10, utf8_decode('Número de Control:'), 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, '#' . str_pad($data['id'], 6, '0', STR_PAD_LEFT), 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 10, utf8_decode('Fecha:'), 0, 0);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($data['fecha'])), 0, 1);

$pdf->Cell(40, 10, utf8_decode('Categoría:'), 0, 0);
$pdf->Cell(0, 10, utf8_decode($data['categoria']), 0, 1);

$pdf->Cell(40, 10, utf8_decode('Descripción:'), 0, 0);
$pdf->MultiCell(0, 10, utf8_decode($data['descripcion']), 0, 'L');

if (!empty($data['recibo_imagen']) && file_exists('../uploads/recibos/' . $data['recibo_imagen'])) {
    $pdf->Ln(5);
    $pdf->Image('../uploads/recibos/' . $data['recibo_imagen'], $pdf->GetX(), $pdf->GetY(), 80); // Ajusta el tamaño según sea necesario
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(0, 15, utf8_decode('MONTO TOTAL: $ ' . number_format($data['monto'], 2)), 1, 1, 'C', true);

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 0, '', 'T', 0, 'C');
$pdf->Cell(10, 0, '', 0, 0, 'C');
$pdf->Cell(50, 0, '', 'T', 1, 'C');

$pdf->Cell(60, 10, 'Firma y Sello del Emisor', 0, 0, 'C');
$pdf->Cell(10, 10, '', 0, 0, 'C');
$pdf->Cell(50, 10, 'Firma del Receptor', 0, 1, 'C');

$pdf->Output('I', 'Recibo_' . $data['id'] . '.pdf');
?>