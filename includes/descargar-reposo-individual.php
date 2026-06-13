<?php
error_reporting(E_ERROR | E_PARSE);
require('../fpdf/fpdf.php');
include '../bd/conexion.php';

if (!isset($_GET['id'])) {
    die("Error: ID de reposo no proporcionado.");
}

$id = $_GET['id'];

// Consulta para obtener los datos del reposo desde la tabla reposo_medico
$consulta = "SELECT * FROM reposo_medico WHERE id = '$id'";
$resultado = $enlace->query($consulta);

if (!$resultado || $resultado->num_rows == 0) {
    die("Error: Registro de reposo no encontrado.");
}

$datos = $resultado->fetch_assoc();

// Calcular estado para el documento
$hoy = date('Y-m-d');
$esVigente = strtotime($datos['vencimiento']) >= strtotime($hoy);
$estado = $esVigente ? 'Vigente' : 'Vencido';

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../imagenes/cintillo.jpg', 2, -3, 188, 30);
        $this->Image('../imagenes/felipeHernandez.jpg', 188, 2, 17);
        $this->Ln(25);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('U.E.E.Br "Felipe Hernández"'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('COMPROBANTE DE REPOSO MÉDICO'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Generado el: ') . date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(235, 235, 235);

$pdf->Cell(0, 10, utf8_decode('Directivo:_________________'), 0, 1, 'L');
$pdf->Cell(0, 10, utf8_decode('Fecha:_____________'), 0, 1, 'L');

// Detalles del trabajador y el reposo
$pdf->Cell(60, 12, utf8_decode('Trabajador:'), 1, 0, 'L', true);
$pdf->Cell(0, 12, utf8_decode($datos['nombre'] . ' ' . $datos['apellido']), 1, 1, 'L');

$pdf->Cell(60, 12, utf8_decode('Cédula de Identidad:'), 1, 0, 'L', true);
$pdf->Cell(0, 12, utf8_decode($datos['cedula']), 1, 1, 'L');

$pdf->Cell(60, 12, utf8_decode('Cargo:'), 1, 0, 'L', true);
$pdf->Cell(0, 12, utf8_decode($datos['cargo']), 1, 1, 'L');

$pdf->Cell(60, 12, utf8_decode('Fecha de Expedición:'), 1, 0, 'L', true);
$pdf->Cell(0, 12, date('d/m/Y', strtotime($datos['expedicion'])), 1, 1, 'L');

$pdf->Cell(60, 12, utf8_decode('Fecha de Vencimiento:'), 1, 0, 'L', true);
$pdf->Cell(0, 12, date('d/m/Y', strtotime($datos['vencimiento'])), 1, 1, 'L');

$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 8, utf8_decode('Se hace constar que el trabajador mencionado ha presentado la documentación médica reglamentaria para justificar su ausencia en el periodo indicado.'), 0, 'C');

$pdf->Ln(30);
$pdf->Cell(0, 10, utf8_decode('__________________________'), 0, 1, 'C');
$pdf->Cell(0, 10, utf8_decode('Sello'), 0, 1, 'C');

// Salida del archivo
$pdf->Output('D', 'Reposo_' . $datos['cedula'] . '.pdf');
exit();