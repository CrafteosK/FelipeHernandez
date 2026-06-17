<?php
require('../fpdf/fpdf.php');
include '../bd/sesion-start.php';
include '../bd/conexion.php';

class PDF_Boleta extends FPDF {
    function Header() {
        // Logo
        if (file_exists('../imagenes/felipeHernandez.jpg')) {
            $this->Image('../imagenes/felipeHernandez.jpg', 12, 10, 20);
        }
        
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, utf8_decode("REPÚBLICA BOLIVARIANA DE VENEZUELA"), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode("MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN"), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode("U.E.E. BR. FELIPE HERNÁNDEZ"), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode("CÓDIGO DE DEPENDENCIA: 1001040533"), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode("CIUDAD BOLÍVAR - ANGOSTURA DEL ORINOCO"), 0, 1, 'C');
        $this->Ln(10);
        
        $this->SetFont('Arial', 'B', 14);
        // Detección automática para el encabezado del PDF
        $mes_actual = (int)date('n');
        $lapso_auto = ($mes_actual >= 9) ? '1' : (($mes_actual <= 3) ? '2' : '3');
        $lapso_num = $_GET['lapso'] ?? $lapso_auto;
        $lapso_txt = $lapso_num . ($lapso_num == '1' ? 'er' : ($lapso_num == '2' ? 'do' : 'er')) . " Lapso";
        $this->Cell(0, 7, utf8_decode("BOLETÍN INFORMATIVO"), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 11);
        $this->Cell(0, 6, utf8_decode($lapso_txt), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-40);
        $this->SetFont('Arial', '', 8);
        $w = 196 / 3;
        $this->Cell($w, 4, "__________________________", 0, 0, 'C');
        $this->Cell($w, 4, "__________________________", 0, 0, 'C');
        $this->Cell($w, 4, "__________________________", 0, 1, 'C');
        $this->Cell($w, 4, "Director(a) del Plantel", 0, 0, 'C');
        $this->Cell($w, 4, "Docente de Grado", 0, 0, 'C');
        $this->Cell($w, 4, "Representante", 0, 1, 'C');
        
        $this->Ln(5);
        $this->Cell(0, 10, utf8_decode("Ciudad Bolívar, ") . date('d/m/Y'), 0, 0, 'R');
    }
}

// Obtener datos según la solicitud
$alumnos = [];
if (isset($_GET['id_estudiante'])) {
    $id = mysqli_real_escape_string($enlace, $_GET['id_estudiante']);
    $query = "SELECT i.*, t.nombre as d_nom, t.apellido as d_ape 
              FROM inscripciones i 
              LEFT JOIN estudiantes_salon es ON i.id = es.id_estudiante 
              LEFT JOIN salones s ON es.id_salon = s.id 
              LEFT JOIN trabajadores t ON s.id_docente = t.id 
              WHERE i.id = '$id'";
    $res = mysqli_query($enlace, $query);
    if ($r = mysqli_fetch_assoc($res)) $alumnos[] = $r;
} else {
    $anno = mysqli_real_escape_string($enlace, $_GET['anno_escolar']);
    $grado = mysqli_real_escape_string($enlace, $_GET['grado']);
    $secc = mysqli_real_escape_string($enlace, $_GET['seccion']);
    
    $query = "SELECT i.*, t.nombre as d_nom, t.apellido as d_ape 
              FROM inscripciones i 
              JOIN estudiantes_salon es ON i.id = es.id_estudiante 
              JOIN salones s ON es.id_salon = s.id 
              JOIN trabajadores t ON s.id_docente = t.id 
              WHERE s.anno_escolar = '$anno' AND s.grado = '$grado' AND s.seccion = '$secc'";
    $res = mysqli_query($enlace, $query);
    while ($r = mysqli_fetch_assoc($res)) $alumnos[] = $r;
}

$pdf = new PDF_Boleta('P', 'mm', 'Letter');
$pdf->SetAutoPageBreak(true, 45);

foreach ($alumnos as $alumno) {
    $pdf->AddPage();
    
    // Bloque de datos del alumno
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(240, 240, 240); // Fondo gris claro para algunas celdas

    // APELLIDOS Y NOMBRES DEL ALUMNO
    $pdf->Cell(130, 7, utf8_decode(" APELLIDOS Y NOMBRES DEL ALUMNO: " . $alumno['apellido'] . ", " . $alumno['nombre']), 1, 0, 'L', true);
    // CÉDULA DE IDENTIDAD O ESCOLAR
    $pdf->Cell(66, 7, utf8_decode(" C.I. O ESCOLAR: " . ($alumno['cedula'] ?? 'S/C')), 1, 1, 'L', true);
    
    // NOMBRE Y APELLIDO DEL REPRESENTANTE (ancho completo, eliminando el teléfono)
    $pdf->Cell(0, 7, utf8_decode(" NOMBRE Y APELLIDO DEL REPRESENTANTE: " . ($alumno['representante_nombre'] ?? 'No registrado')), 1, 1, 'L');
    
    $docente = utf8_decode($alumno['d_nom'] . " " . $alumno['d_ape']);
    $pdf->Cell(100, 7, utf8_decode(" DOCENTE: " . $docente), 1, 0, 'L');
    $pdf->Cell(48, 7, utf8_decode(" GRADO: " . $alumno['grado']), 1, 0, 'L');
    $pdf->Cell(48, 7, utf8_decode(" SECCIÓN: '" . $alumno['seccion'] . "'"), 1, 1, 'L');
    
    // Obtener proyectos para el salón actual del estudiante
    $mes_actual = (int)date('n');
    $lapso_auto = ($mes_actual >= 9) ? '1' : (($mes_actual <= 3) ? '2' : '3');
    $lapso_f = $_GET['lapso'] ?? $lapso_auto;

    $q_proyectos_alumno = "SELECT DISTINCT e.proyecto FROM evaluaciones e JOIN salones s ON e.id_salon = s.id JOIN estudiantes_salon es ON s.id = es.id_salon 
                           WHERE es.id_estudiante = '{$alumno['id']}' AND e.lapso = '$lapso_f' AND e.proyecto IS NOT NULL AND e.proyecto != ''";
    $res_proyectos_alumno = mysqli_query($enlace, $q_proyectos_alumno);
    $proyectos_alumno_arr = [];
    while ($p = mysqli_fetch_assoc($res_proyectos_alumno)) {
        $proyectos_alumno_arr[] = $p['proyecto'];
    }
    $proyectos_alumno_str = !empty($proyectos_alumno_arr) ? implode(", ", $proyectos_alumno_arr) : 'No hay proyectos asignados';
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(0, 5, utf8_decode("PROYECTOS: " . $proyectos_alumno_str), 1, 'L');
    $pdf->Cell(98, 6, utf8_decode("DÍAS HÁBILES: 63"), 1, 0, 'L');
    $pdf->Cell(98, 6, utf8_decode("INASISTENCIAS: ____________________"), 1, 1, 'L'); // Línea para entrada manual
    $pdf->Ln(5);

    // Cuerpo Descriptivo
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, utf8_decode("ASPECTOS DESCRIPTIVOS DE MI ACTUACIÓN ACADÉMICA ESCOLAR"), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    
    $texto_descriptivo = "El estudiante ha demostrado un rendimiento satisfactorio durante este periodo. Participa activamente en los proyectos de aula, mostrando especial interes en los experimentos cientificos y el trabajo colaborativo. Cumple con las asignaciones puntualmente y mantiene una excelente conducta.";
    $pdf->MultiCell(0, 6, utf8_decode($texto_descriptivo), 0, 'J');
    $pdf->Ln(5);

    // Observaciones (Líneas para escribir a mano)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, utf8_decode("Observaciones:"), 0, 1, 'L');
    $pdf->Ln(2);
    for($i=0; $i<3; $i++) {
        $pdf->Cell(0, 8, "__________________________________________________________________________________________", 0, 1, 'L');
    }
    $pdf->Ln(5);

    // Literal de Promoción
    $pdf->SetFillColor(250, 250, 250);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(0, 3, utf8_decode("Escala Cualitativa: (A) El alumno alcanzó todas las competencias. (B) El alumno alcanzó la mayoría de las competencias. (C) El alumno alcanzó algunas de las competencias. (D) El alumno alcanzó pocas competencias. (E) El alumno no logró las competencias."), 1, 'C', true);
    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 11);
    $literal = $alumno['nota'] ?: 'S/N';
    $siguiente_grado = "5to"; // Lógica simple para el ejemplo
    
    $promo_txt = "En función con lo descrito en este boletín, ha sido PROMOVIDO al '" . $siguiente_grado . "' Grado de Educación Primaria por haber logrado las competencias y obtenido el literal: " . $literal;
    
    $pdf->SetTextColor(0, 50, 150);
    $pdf->MultiCell(0, 7, utf8_decode($promo_txt), 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
}

if (empty($alumnos)) {
    die("No se encontraron alumnos para generar boletas.");
}

$pdf->Output('I', 'Boletas_Informativas.pdf');
?>