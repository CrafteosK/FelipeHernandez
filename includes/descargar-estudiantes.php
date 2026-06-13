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
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 10, utf8_decode('N°'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Nombre'), 1, 0, 'C');
        $this->Cell(30, 10, utf8_decode('Apellido'), 1, 0, 'C');
        $this->Cell(25, 10, utf8_decode('F. Nacimiento'), 1, 0, 'C');
        $this->Cell(35, 10, utf8_decode('Grado'), 1, 0, 'C');
        $this->Cell(15, 10, utf8_decode('Secc.'), 1, 0, 'C');
        $this->Cell(25, 10, utf8_decode('Año Escolar'), 1, 0, 'C');
        $this->Cell(45, 10, utf8_decode('Tel. Representante'), 1, 0, 'C');

        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}

// Verifica si se ha enviado el parámetro "file"
$consulta = "SELECT * FROM inscripciones ORDER BY grado, seccion, apellido ASC";
$resultado = $enlace->query($consulta);

if (!$resultado) {
    die("Error en la consulta: " . $enlace->error);
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->titulo = utf8_decode('Reporte de Estudiantes Inscritos');
$pdf->AddPage();

$pdf->SetFont('Arial', '', 9);
$gris1 = [230, 230, 230];
$gris2 = [245, 245, 245];
$contador = 1;

while ($fila = $resultado->fetch_assoc()) {
    if ($contador % 2 == 0) {
        $pdf->SetFillColor($gris1[0], $gris1[1], $gris1[2]);
    } else {
        $pdf->SetFillColor($gris2[0], $gris2[1], $gris2[2]);
    }
    $pdf->Cell(10, 8, $contador, 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode($fila['nombre']), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode($fila['apellido']), 1, 0, 'C', true);
    $pdf->Cell(25, 8, $fila['fecha_nacimiento'], 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode($fila['grado']), 1, 0, 'C', true);
    $pdf->Cell(15, 8, utf8_decode($fila['seccion']), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode($fila['anno_escolar']), 1, 0, 'C', true);
    $pdf->Cell(45, 8, utf8_decode($fila['telefono_representante']), 1, 1, 'C', true);
    $contador++;
}

$pdf->Output('D', 'reporte_estudiantes.pdf');
exit();
?>