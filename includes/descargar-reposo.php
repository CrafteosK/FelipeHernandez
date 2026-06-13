<?php
error_reporting(E_ERROR | E_PARSE); // Solo mostrar errores críticos
require('../fpdf/fpdf.php');
include '../bd/conexion.php'; // Conexión a la base de datos

class PDF extends FPDF
{
    public $titulo;
    public $file;

    function Header()
    {
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 20, utf8_decode('U.E.Br "Felipe Hernández"'), 0, 1, 'C');
        $this->Ln(5);

        // Imagen del cintillo (ajusta la ruta si es necesario)
        $this->Image('../imagenes/cintillo.jpg', 0, -3, 190, 27);

        // Imagen del logo (ajusta la ruta si es necesario)
        $this->Image('../imagenes/felipeHernandez.jpg', 188, 2, 17);

        $this->SetFont('Arial', 'B', 14);
        // Subtítulo según el archivo
        if ($this->titulo) {
            $this->Cell(0, 10, $this->titulo, 0, 1, 'C');
        }
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 8, date('d/m/Y H:i'), 0, 1, 'C');
        $this->Ln(3);

        // Encabezados de la tabla según el archivo
        $this->SetFont('Arial', 'B', 12);

        $this->Cell(10, 10, utf8_decode('N°'), 1, 0, 'C');
        $this->Cell(35, 10, utf8_decode('Nombre'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Apellido'), 1, 0, 'C');
        $this->Cell(25, 10, utf8_decode('Cédula'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Cargo'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Expedición'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Vencimiento'), 1, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}


// Lógica de filtrado para reposo_medico
$filtroFecha = isset($_GET['filtroFecha']) ? $_GET['filtroFecha'] : 'todos';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';

$whereClause = "";
if ($filtroFecha == 'hoy') {
    $whereClause = " WHERE fecha = CURDATE() ";
} elseif ($filtroFecha == 'ayer') {
    $whereClause = " WHERE fecha = DATE_SUB(CURDATE(), INTERVAL 1 DAY) ";
} elseif ($filtroFecha == '7dias') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
} elseif ($filtroFecha == 'mes') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
} elseif ($filtroFecha == '3meses') {
    $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ";
} elseif ($filtroFecha == 'personalizado' && !empty($fechaInicio) && !empty($fechaFin)) {
    $whereClause = " WHERE fecha BETWEEN '$fechaInicio' AND '$fechaFin' ";
}

$consulta = "SELECT * FROM reposo_medico $whereClause ORDER BY id DESC";
$resultado = $enlace->query($consulta);

if (!$resultado) {
    die("Error en la consulta: " . $enlace->error);
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->titulo = utf8_decode('Reporte de Reposos Médicos');
$pdf->AddPage();

$pdf->SetFont('Arial', '', 10);
$gris1 = [230, 230, 230];
$gris2 = [245, 245, 245];
$contador = 1;


while ($fila = $resultado->fetch_assoc()) {
    if ($contador % 2 == 0) {
        $pdf->SetFillColor($gris1[0], $gris1[1], $gris1[2]);
    } else {
        $pdf->SetFillColor($gris2[0], $gris2[1], $gris2[2]);
    }
    $pdf->Cell(10, 10, $contador, 1, 0, 'C', true);
    $pdf->Cell(35, 10, utf8_decode($fila['nombre']), 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode($fila['apellido']), 1, 0, 'C', true);
    $pdf->Cell(25, 10, utf8_decode($fila['cedula']), 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode($fila['cargo']), 1, 0, 'C', true);
    $pdf->Cell(30, 10, $fila['expedicion'], 1, 0, 'C', true);
    $pdf->Cell(30, 10, $fila['vencimiento'], 1, 1, 'C', true);
    $contador++;
}

$pdf->Output('D', 'reporte_reposos_medicos.pdf');
exit();