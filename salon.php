<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // 1. Lógica para asignar salón
    if(isset($_POST['crearSalonBtn'])) {
        $id_docente = $_POST['docente'];
        $grado = $_POST['grado_asignar'];
        $seccion = $_POST['seccion_asignar'];
        $anno = $_POST['anno_asignar'];
        $estudiantes = $_POST['estudiantes']; // Array

        mysqli_query($enlace, "INSERT INTO salones (id_docente, grado, seccion, anno_escolar) VALUES ('$id_docente', '$grado', '$seccion', '$anno')");
        $id_salon = mysqli_insert_id($enlace);

        foreach($estudiantes as $id_est) {
            mysqli_query($enlace, "INSERT INTO estudiantes_salon (id_salon, id_estudiante) VALUES ('$id_salon', '$id_est')");
        }
        header("Location: salon.php?anno_escolar=$anno&grado=$grado&seccion=$seccion");
    }

    // 2. Lógica para asignar evaluación
    if(isset($_POST['saveEvalBtn'])) {
        $id_salon = $_POST['id_salon'];
        $titulo = $_POST['titulo'];
        $desc = $_POST['descripcion'];
        $fecha = $_POST['fecha'];
        mysqli_query($enlace, "INSERT INTO evaluaciones (id_salon, titulo, descripcion, fecha_actividad) VALUES ('$id_salon', '$titulo', '$desc', '$fecha')");
        echo '<script>alert("Evaluación asignada");</script>';
    }

    $anno_f = $_GET['anno_escolar'] ?? '';
    $grado_f = $_GET['grado'] ?? '';
    $seccion_f = $_GET['seccion'] ?? '';

    $info_salon = null;
    if($anno_f && $grado_f && $seccion_f) {
        $q = "SELECT s.*, t.nombre as d_nom, t.apellido as d_ape, t.cedula as d_ced 
              FROM salones s JOIN trabajadores t ON s.id_docente = t.id 
              WHERE s.grado='$grado_f' AND s.seccion='$seccion_f' AND s.anno_escolar='$anno_f'";
        $res = mysqli_query($enlace, $q);
        $info_salon = mysqli_fetch_array($res);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salón de Clase - Felipe Hernández</title>
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
    <link href="datatables/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-5.3.8-examples/assets/dist/css/bootstrap.min.css">  <!--Opcional: agrega estilos a las tablas -->
    <link rel="stylesheet" href="css/nav-side.css">
    <link rel="stylesheet" href="css/trabajadores.css">
</head>
<body>
    <header><?php include 'includes/nav.php'; ?></header>
    <?php include 'includes/side.php'; ?>
    <main id="main">
        <div class="container my-4">
            <div class="center"><h1>Gestión de Salones</h1></div>

            <!-- Filtros de Selección Obligatoria -->
            <div class="card p-3 mb-4 shadow-sm">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Año Escolar</label>
                        <select name="anno_escolar" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $r = mysqli_query($enlace, "SELECT DISTINCT anno_escolar FROM inscripciones ORDER BY anno_escolar DESC");
                            while($a = mysqli_fetch_array($r)) echo "<option ".($anno_f==$a['anno_escolar']?'selected':'')." value='{$a['anno_escolar']}'>{$a['anno_escolar']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Grado</label>
                        <select name="grado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $grados = ["1er grado", "2do grado", "3ro grado", "4to grado", "5to grado", "6to grado"];
                            foreach($grados as $g) echo "<option ".($grado_f==$g?'selected':'')." value='$g'>$g</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Sección</label>
                        <select name="seccion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach(['A','B','C','D'] as $s) echo "<option ".($seccion_f==$s?'selected':'')." value='$s'>$s</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Cargar Salón</button>
                    </div>
                </form>
            </div>

            <?php if($info_salon): ?>
            <!-- Info del Docente y Salón -->
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                <div>
                    <h6 class="mb-0 text-primary fw-bold">DOCENTE ASIGNADO:</h6>
                    <span><?php echo "{$info_salon['d_nom']} {$info_salon['d_ape']} - C.I: {$info_salon['d_ced']}"; ?></span>
                </div>
                <div class="text-end">
                    <h5 class="mb-0 fw-bold"><?php echo "{$info_salon['grado']} - Sección '{$info_salon['seccion']}'"; ?></h5>
                    <small class="text-muted">Año Escolar: <?php echo $info_salon['anno_escolar']; ?></small>
                </div>
            </div>

            <ul class="nav nav-tabs mb-4" id="notasTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#estudiantes">Listado de Estudiantes</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#evaluaciones">Evaluaciones</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="estudiantes">
                    <div class="container-table">
                            <table class="table table-striped" id="tablaSalon">
                                <thead>
                                    <tr>
                                        <th>Nº</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Sexo</th>
                                        <th>Fecha Nacimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q = "SELECT i.* FROM inscripciones i JOIN estudiantes_salon es ON i.id = es.id_estudiante WHERE es.id_salon = '{$info_salon['id']}'";
                                    $r = mysqli_query($enlace, $q);
                                    $c = 1;
                                    while($row = mysqli_fetch_array($r)) {
                                        echo "<tr><td>$c</td><td>{$row['nombre']}</td><td>{$row['apellido']}</td><td>{$row['sexo']}</td><td>{$row['fecha_nacimiento']}</td></tr>";
                                        $c++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="evaluaciones">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#evalModal"><i class="fa-solid fa-plus"></i> Nueva Evaluación</button>
                    </div>
                    <div class="row">
                        <?php
                        $q = "SELECT * FROM evaluaciones WHERE id_salon = '{$info_salon['id']}' ORDER BY fecha_actividad DESC";
                        $r = mysqli_query($enlace, $q);
                        while($ev = mysqli_fetch_array($r)): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-primary"><?php echo $ev['titulo']; ?></h5>
                                        <p class="card-text small text-muted"><?php echo $ev['descripcion']; ?></p>
                                    </div>
                                    <div class="card-footer bg-white border-0 text-end">
                                        <span class="badge bg-secondary"><?php echo $ev['fecha_actividad']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Modal Evaluación -->
            <div class="modal fade" id="evalModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <input type="hidden" name="id_salon" value="<?php echo $info_salon['id']; ?>">
                            <div class="modal-header"><h5>Asignar Actividad</h5></div>
                            <div class="modal-body">
                                <input type="text" name="titulo" class="form-control mb-3" placeholder="Título de la Actividad" required>
                                <textarea name="descripcion" class="form-control mb-3" placeholder="Descripción breve..." required></textarea>
                                <input type="date" name="fecha" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button name="saveEvalBtn" class="btn btn-primary">Asignar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php else: ?>
                <!-- Si no se encuentra salón, opción de asignar -->
                <?php if($anno_f && $grado_f && $seccion_f): ?>
                    <div class="alert alert-warning text-center mt-5 p-5 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation fa-3x mb-3"></i>
                        <h3>Este salón aún no ha sido conformado</h3>
                        <p>Grado: <b><?php echo $grado_f; ?></b> | Sección: <b><?php echo $seccion_f; ?></b> | Año: <b><?php echo $anno_f; ?></b></p>
                        <button class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#asignarModal">Conformar Salón Ahora</button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mt-5 p-5 shadow-sm">
                        <i class="fa-solid fa-chalkboard-user fa-3x mb-3"></i>
                        <h3>Gestión de Salones de Clase</h3>
                        <p>Por favor seleccione un <b>Grado, Sección y Año</b> para visualizar los datos del salón.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de Asignación Masiva -->
    <div class="modal fade" id="asignarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="anno_asignar" value="<?php echo $anno_f; ?>">
                    <input type="hidden" name="grado_asignar" value="<?php echo $grado_f; ?>">
                    <input type="hidden" name="seccion_asignar" value="<?php echo $seccion_f; ?>">
                    
                    <div class="modal-header"><h5>Conformar Salón de Clase</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Seleccione el Docente</label>
                            <select name="docente" class="form-control" required>
                                <option value="">Docentes disponibles...</option>
                                <?php
                                $q = "SELECT t.* FROM trabajadores t JOIN cargos c ON t.cargo = c.id 
                                      WHERE c.nombre LIKE '%docente%' 
                                      AND t.id NOT IN (SELECT id_docente FROM salones WHERE anno_escolar='$anno_f')";
                                $r = mysqli_query($enlace, $q);
                                while($doc = mysqli_fetch_array($r)) echo "<option value='{$doc['id']}'>{$doc['nombre']} {$doc['apellido']} ({$doc['cedula']})</option>";
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Seleccione los Estudiantes (Multiselección)</label>
                            <select name="estudiantes[]" class="form-control" multiple style="height: 200px;" required>
                                <?php
                                $q = "SELECT i.* FROM inscripciones i 
                                      WHERE i.grado = '$grado_f' AND i.anno_escolar = '$anno_f'
                                      AND i.id NOT IN (SELECT es.id_estudiante FROM estudiantes_salon es JOIN salones s ON es.id_salon = s.id WHERE s.anno_escolar='$anno_f')";
                                $r = mysqli_query($enlace, $q);
                                while($est = mysqli_fetch_array($r)) echo "<option value='{$est['id']}'>{$est['nombre']} {$est['apellido']}</option>";
                                ?>
                            </select>
                            <small class="text-muted">Mantenga presionado Ctrl (o Cmd) para seleccionar varios estudiantes.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button name="crearSalonBtn" class="btn btn-primary">Confirmar Asignación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="bootstrap-5.3.8-examples/assets/dist/js/bootstrap.bundle.min.js"></script>
    <script src="datatables/datatables.min.js"></script>
    <script src="js/nav-side.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#tablaNotas').DataTable({
            "dom": 'rtip',
            "language": {
                "emptyTable": "No hay estudiantes registrados en este año/grado",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ estudiantes",
                "infoEmpty": "Mostrando 0 a 0 de 0 estudiantes",
                "infoFiltered": "(filtrado de _MAX_ totales)",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron coincidencias",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
        $('#busqueda').on('keyup', function() {
            table.search(this.value).draw();
        });
    });

    // Funciones de Cálculo Rápido
    function obtenerLetra(nota) {
        nota = Math.round(nota);
        if (nota >= 19 && nota <= 20) return 'A';
        if (nota >= 16 && nota <= 18) return 'B';
        if (nota >= 12 && nota <= 15) return 'C';
        if (nota >= 8 && nota <= 11) return 'D';
        if (nota >= 4 && nota <= 7) return 'E';
        if (nota >= 1 && nota <= 3) return 'F';
        return 'N/A';
    }

    function calcularPromedioLetra() {
        const val = $('#inputPromedio').val();
        const arr = val.split(/[,\s]+/).map(n => parseFloat(n)).filter(n => !isNaN(n));
        
        if (arr.length === 0) {
            alert("Por favor ingrese notas válidas");
            return;
        }

        const sum = arr.reduce((a, b) => a + b, 0);
        const prom = (sum / arr.length).toFixed(2);
        const letra = obtenerLetra(prom);

        $('#valPromedio').text(prom);
        $('#letraPromedio').text(letra);
        $('#resultadoPromedio').removeClass('d-none');
    }

    function convertirDirecto() {
        const val = $('#inputDirecto').val();
        if (val === "" || val < 1 || val > 20) {
            alert("Por favor ingrese una nota entre 1 y 20");
            return;
        }
        const letra = obtenerLetra(val);
        $('#letraDirecto').text(letra);
        $('#resultadoDirecto').removeClass('d-none');
    }
    </script>
</body>
</html>