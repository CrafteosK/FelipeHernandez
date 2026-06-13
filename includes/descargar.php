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

        if ($this->file === 'registros_trabajadores.pdf') {
            $this->Cell(10, 10, utf8_decode('N°'), 1, 0, 'C');
            $this->Cell(40, 10, utf8_decode('Nombre'), 1, 0, 'C');
            $this->Cell(40, 10, utf8_decode('Apellido'), 1, 0, 'C');
            $this->Cell(30, 10, utf8_decode('Cédula'), 1, 0, 'C');
            $this->Cell(30, 10, utf8_decode('Teléfono'), 1, 0, 'C');
            $this->Cell(30, 10, utf8_decode('Cargo'), 1, 0, 'C');
        } else {
            $this->Cell(10, 10, utf8_decode('N°'), 1, 0, 'C');
            $this->Cell(40, 10, utf8_decode('Nombre'), 1, 0, 'C');
            $this->Cell(40, 10, utf8_decode('Apellido'), 1, 0, 'C');
            $this->Cell(33.3, 10, utf8_decode('Cédula'), 1, 0, 'C');
            $this->Cell(33.3, 10, utf8_decode('Telefono'), 1, 0, 'C');
            $this->Cell(33.3, 10, utf8_decode('Cargo'), 1, 0, 'C');
        }
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
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Configura el nombre del archivo PDF, el título y la consulta SQL según el archivo solicitado
    if ($file === 'registros_maestros.pdf') {
        $titulo = utf8_decode('Registros de Maestros');
        $consulta = "
            SELECT m.id, t.nombre, t.apellido, t.cedula, m.tipo, m.hora
            FROM maestros m
            INNER JOIN trabajadores t ON m.id_trabajador = t.id_trabajador
        ";
    } elseif ($file === 'registros_cocineros.pdf') {
        $titulo = utf8_decode('Registros de Cocineros');
        $consulta = "
            SELECT c.id, t.nombre, t.apellido, t.cedula, c.tipo, c.hora
            FROM cocineros c
            INNER JOIN trabajadores t ON c.id_trabajador = t.id_trabajador
        ";
    } elseif ($file === 'registros_vigilantes.pdf') {
        $titulo = utf8_decode('Registros de Vigilantes');
        $consulta = "
            SELECT v.id, t.nombre, t.apellido, t.cedula, v.tipo, v.hora
            FROM vigilantes v
            INNER JOIN trabajadores t ON v.id_trabajador = t.id_trabajador
        ";
    } elseif ($file === 'registros_obreros.pdf') {
        $titulo = utf8_decode('Registros de Obreros');
        $consulta = "
            SELECT o.id, t.nombre, t.apellido, t.cedula, o.tipo, o.hora
            FROM obreros o
            INNER JOIN trabajadores t ON o.id_trabajador = t.id_trabajador
        ";
    } elseif ($file === 'registros_trabajadores.pdf') {
        $titulo = utf8_decode('Registros de Trabajadores');
        $consulta = "
            SELECT id_trabajador, nombre, apellido, cedula, telefono, cargos
            FROM trabajadores 
        ";
    } else {
        die('Archivo no válido.');
    }

    // Ejecuta la consulta
    $resultado = $enlace->query($consulta);

    if (!$resultado) {
        die("Error en la consulta: " . $enlace->error);
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->titulo = $titulo;
    $pdf->file = $file;
    $pdf->AddPage();

    // Colores para filas intercaladas
    $gris1 = [230, 230, 230];
    $gris2 = [245, 245, 245];
    $contador = 1;

    // Agrega encabezados y datos específicos para registros_trabajadores.pdf
    if ($file === 'registros_trabajadores.pdf') {
        $pdf->SetFont('Arial', '', 10);
        while ($fila = $resultado->fetch_assoc()) {
            if ($contador % 2 == 0) {
                $pdf->SetFillColor($gris1[0], $gris1[1], $gris1[2]);
            } else {
                $pdf->SetFillColor($gris2[0], $gris2[1], $gris2[2]);
            }
            $pdf->Cell(10, 10, $fila['id_trabajador'], 1, 0, 'C', true);
            $pdf->Cell(40, 10, utf8_decode($fila['nombre']), 1, 0, 'C', true);
            $pdf->Cell(40, 10, utf8_decode($fila['apellido']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, utf8_decode($fila['cedula']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, utf8_decode($fila['telefono']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, utf8_decode($fila['cargos']), 1, 1, 'C', true);
            $contador++;
        }
    } else {
        $pdf->SetFont('Arial', '', 10);
        while ($fila = $resultado->fetch_assoc()) {
            if ($contador % 2 == 0) {
                $pdf->SetFillColor($gris1[0], $gris1[1], $gris1[2]);
            } else {
                $pdf->SetFillColor($gris2[0], $gris2[1], $gris2[2]);
            }
            // Verifica si las claves existen antes de usarlas
            $id = isset($fila['id']) ? $fila['id'] : 'N/A';
            $tipo = isset($fila['tipo']) ? utf8_decode($fila['tipo']) : 'N/A';
            $hora_sin_segundos = isset($fila['hora']) ? substr($fila['hora'], 0, 16) : 'N/A';

            $pdf->Cell(10, 10, $id, 1, 0, 'C', true);
            $pdf->Cell(40, 10, utf8_decode($fila['nombre']), 1, 0, 'C', true);
            $pdf->Cell(40, 10, utf8_decode($fila['apellido']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, utf8_decode($fila['cedula']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, $tipo, 1, 0, 'C', true);
            $pdf->Cell(30, 10, $hora_sin_segundos, 1, 1, 'C', true);
            $contador++;
        }
    }

    // Salida del PDF
    $pdf->Output('D', $file); // Descarga directa del archivo
    exit();
} elseif (isset($_GET['cargo'])) {
    // Verificar si se ha enviado el filtro de cargo
    $cargo_filtro = isset($_GET['cargo']) ? $_GET['cargo'] : 'todos';

    // Construir la consulta SQL según el filtro
    if ($cargo_filtro === 'todos') {
        $consulta = "
            SELECT trabajadores.nombre, trabajadores.apellido, trabajadores.cedula, trabajadores.telefono, cargos.nombre AS cargo
            FROM trabajadores
            INNER JOIN cargos ON trabajadores.cargo = cargos.id
        ";
        $resultado = $enlace->query($consulta);
    } else {
        $consulta = "
            SELECT trabajadores.nombre, trabajadores.apellido, trabajadores.cedula, trabajadores.telefono, cargos.nombre AS cargo
            FROM trabajadores
            INNER JOIN cargos ON trabajadores.cargo = cargos.id
            WHERE trabajadores.cargo = ?
        ";
        $stmt = $enlace->prepare($consulta);
        $stmt->bind_param("i", $cargo_filtro);
        $stmt->execute();
        $resultado = $stmt->get_result();
    }

    // Verificar si la consulta fue exitosa
    if (!$resultado) {
        die("Error en la consulta: " . $enlace->error);
    }

    // Crear el PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->titulo = utf8_decode('Registro de Trabajadores');
    $pdf->file = '';
    $pdf->AddPage();

    $pdf->SetFont('Arial', '', 10);
    $gris1 = [230, 230, 230];
    $gris2 = [245, 245, 245];
    $contador = 1;

    // Agregar los datos al PDF
    while ($fila = $resultado->fetch_assoc()) {
        if ($contador % 2 == 0) {
            $pdf->SetFillColor($gris1[0], $gris1[1], $gris1[2]);
        } else {
            $pdf->SetFillColor($gris2[0], $gris2[1], $gris2[2]);
        }
        $pdf->Cell(10, 10, $contador, 1, 0, 'C', true);
        $pdf->Cell(40, 10, utf8_decode($fila['nombre']), 1, 0, 'C', true);
        $pdf->Cell(40, 10, utf8_decode($fila['apellido']), 1, 0, 'C', true);
        $pdf->Cell(33.3, 10, utf8_decode($fila['cedula']), 1, 0, 'C', true);
        $pdf->Cell(33.3, 10, utf8_decode($fila['telefono']), 1, 0, 'C', true);
        $pdf->Cell(33.3, 10, utf8_decode($fila['cargo']), 1, 1, 'C', true);
        $contador++;
    }

    // Salida del PDF
    $pdf->Output('D', 'trabajadores_filtrados.pdf'); // Descarga directa del archivo
    exit();
} else {
    die('No se especificó ningún archivo.');
}
?>