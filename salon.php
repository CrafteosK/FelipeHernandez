<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // Función auxiliar para mostrar la letra de la evaluación en la tabla
    function numeroALetraPHP($num) {
        $num = round($num);
        if ($num >= 19) return 'A';
        if ($num >= 16) return 'B';
        if ($num >= 12) return 'C';
        if ($num >= 10) return 'D';
        return 'E';
    }

    // 1. Lógica para asignar salón
    if(isset($_POST['crearSalonBtn'])) {
        $id_docente = $_POST['docente'];
        $grado = $_POST['grado_asignar'];
        $seccion = $_POST['seccion_asignar'];
        $anno = $_POST['anno_asignar'];

        mysqli_query($enlace, "INSERT INTO salones (id_docente, grado, seccion, anno_escolar) VALUES ('$id_docente', '$grado', '$seccion', '$anno')");
        $id_salon = mysqli_insert_id($enlace);

        // Buscar automáticamente los estudiantes que pertenecen a este grado, sección y año
        $q_est = "SELECT id FROM inscripciones WHERE grado = '$grado' AND seccion = '$seccion' AND anno_escolar = '$anno'";
        $res_est = mysqli_query($enlace, $q_est);
        
        while($fila = mysqli_fetch_array($res_est)) {
            $id_est = $fila['id'];
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

    // 3. Lógica para eliminar evaluación
    if(isset($_POST['deleteEvalBtn'])) {
        $id_ev = $_POST['id_evaluacion'];
        mysqli_query($enlace, "DELETE FROM evaluaciones WHERE id = '$id_ev'");
        echo '<script>alert("Evaluación eliminada");</script>';
    }

    // 4. Lógica para guardar nota de evaluación y actualizar promedio
    if(isset($_POST['saveNotaEvalBtn'])) {
        $id_est = $_POST['id_estudiante'];
        $id_ev = $_POST['id_evaluacion'];
        $nota_letra = $_POST['nota_letra'];

        // Mapeo de letra a valor numérico para poder promediar correctamente
        $mapeo = ['A' => 20, 'B' => 17, 'C' => 14, 'D' => 11, 'E' => 8];
        $nota_num = $mapeo[$nota_letra] ?? 0;

        // Guardar o actualizar la nota numérica
        $sql = "INSERT INTO notas_evaluaciones (id_estudiante, id_evaluacion, nota) 
                VALUES ('$id_est', '$id_ev', '$nota_num') 
                ON DUPLICATE KEY UPDATE nota = '$nota_num'";
        mysqli_query($enlace, $sql);

        // Recalcular promedio para el salón actual
        $res_salon = mysqli_query($enlace, "SELECT id_salon FROM evaluaciones WHERE id = '$id_ev'");
        $id_salon = mysqli_fetch_assoc($res_salon)['id_salon'];

        $q_notas = "SELECT nota FROM notas_evaluaciones WHERE id_estudiante = '$id_est' AND id_evaluacion IN (SELECT id FROM evaluaciones WHERE id_salon = '$id_salon')";
        $res_n = mysqli_query($enlace, $q_notas);
        $suma = 0; $cant = 0;
        while($n = mysqli_fetch_array($res_n)) {
            $suma += $n['nota'];
            $cant++;
        }

        if($cant > 0) {
            $prom = round($suma / $cant);
            $letra = 'E';
            if ($prom >= 19) $letra = 'A';
            elseif ($prom >= 16) $letra = 'B';
            elseif ($prom >= 12) $letra = 'C';
            elseif ($prom >= 10) $letra = 'D';
            
            mysqli_query($enlace, "UPDATE inscripciones SET nota = '$letra' WHERE id = '$id_est'");
        }
        echo '<script>alert("Calificación guardada y promedio actualizado"); window.location.href=window.location.href;</script>';
    }

    $anno_f = $_GET['anno_escolar'] ?? '';
    $grado_f = $_GET['grado'] ?? '';
    $seccion_f = $_GET['seccion'] ?? '';

    $info_salon = null;
    $evaluaciones_list = [];
    if($anno_f && $grado_f && $seccion_f) {
        $q = "SELECT s.*, t.nombre as d_nom, t.apellido as d_ape, t.cedula as d_ced 
              FROM salones s JOIN trabajadores t ON s.id_docente = t.id 
              WHERE s.grado='$grado_f' AND s.seccion='$seccion_f' AND s.anno_escolar='$anno_f'";
        $res = mysqli_query($enlace, $q);
        $info_salon = mysqli_fetch_array($res);

        if($info_salon) {
            $q_evs = "SELECT * FROM evaluaciones WHERE id_salon = '{$info_salon['id']}' ORDER BY id ASC";
            $res_evs = mysqli_query($enlace, $q_evs);
            while($ev = mysqli_fetch_array($res_evs)) $evaluaciones_list[] = $ev;
        }
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
                        <select name="grado" id="grado" data-target="#seccion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $grados = ["Simoncito Libertador", "Mi esperanza", "inicial", "1er grado", "2do grado", "3ro grado", "4to grado", "5to grado", "6to grado"];
                            foreach($grados as $g) echo "<option ".($grado_f==$g?'selected':'')." value='$g'>$g</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Sección</label>
                        <select name="seccion" id="seccion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="A" <?= ($seccion_f == 'A' ? 'selected' : '') ?>>A</option>
                            <option value="B" <?= ($seccion_f == 'B' ? 'selected' : '') ?>>B</option>
                            <option value="C" <?= ($seccion_f == 'C' ? 'selected' : '') ?>>C</option>
                            <option value="D" <?= ($seccion_f == 'D' ? 'selected' : '') ?>>D</option>
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
                                        <?php foreach($evaluaciones_list as $ev): ?>
                                            <th class="text-center" title="<?php echo $ev['descripcion']; ?>"><?php echo $ev['titulo']; ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-center">Nota Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q = "SELECT i.* FROM inscripciones i JOIN estudiantes_salon es ON i.id = es.id_estudiante WHERE es.id_salon = '{$info_salon['id']}'";
                                    $r = mysqli_query($enlace, $q);
                                    $c = 1;
                                    while($row = mysqli_fetch_array($r)) {
                                        echo "<tr>
                                            <td>$c</td>
                                            <td>{$row['nombre']}</td>
                                            <td>{$row['apellido']}</td>
                                            <td>{$row['sexo']}</td>
                                            <td>{$row['fecha_nacimiento']}</td>";
                                        
                                        foreach($evaluaciones_list as $ev) {
                                            $q_n = "SELECT nota FROM notas_evaluaciones WHERE id_estudiante = '{$row['id']}' AND id_evaluacion = '{$ev['id']}'";
                                            $res_n = mysqli_query($enlace, $q_n);
                                            $nota_val = mysqli_fetch_assoc($res_n)['nota'] ?? '-';
                                            echo "<td class='text-center'>
                                                <button class='btn btn-link btn-sm text-decoration-none btn-nota-ev' data-est='{$row['id']}' data-ev='{$ev['id']}' data-titulo='{$ev['titulo']}'>
                                                    ".($nota_val != '-' ? numeroALetraPHP($nota_val) : '<i class="fa-solid fa-plus-circle text-muted"></i>')."
                                                </button>
                                            </td>";
                                        }
                                        $badgeColor = ($row['nota'] == 'A') ? 'success' : (($row['nota'] == 'B') ? 'primary' : (($row['nota'] == 'C') ? 'info' : (($row['nota'] == 'D') ? 'warning' : 'danger')));
                                        echo "<td class='text-center fw-bold'><span class='badge bg-$badgeColor'>".($row['nota'] ?: 'S/N')."</span></td>";
                                        echo "</tr>";
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
                    <div class="container-table">
                        <table class="table table-striped" id="tablaEvaluaciones">
                            <thead>
                                <tr>
                                    <th>Nº</th>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q = "SELECT * FROM evaluaciones WHERE id_salon = '{$info_salon['id']}' ORDER BY fecha_actividad DESC";
                                $r = mysqli_query($enlace, $q);
                                $ce = 1;
                                while($ev = mysqli_fetch_array($r)): ?>
                                    <tr>
                                        <td><?php echo $ce++; ?></td>
                                        <td><?php echo $ev['titulo']; ?></td>
                                        <td><?php echo $ev['descripcion']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $ev['fecha_actividad']; ?></span></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta evaluación?');">
                                                <input type="hidden" name="id_evaluacion" value="<?php echo $ev['id']; ?>">
                                                <button name="deleteEvalBtn" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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

            <!-- Modal Nota Evaluación -->
            <div class="modal fade" id="notaEvalModal" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <input type="hidden" name="id_estudiante" id="ne_id_est">
                            <input type="hidden" name="id_evaluacion" id="ne_id_ev">
                            <div class="modal-header"><h5>Calificar</h5></div>
                            <div class="modal-body text-center">
                                <p id="ne_titulo_ev" class="fw-bold"></p>
                                <select name="nota_letra" class="form-control form-control-lg text-center" required>
                                    <option value="" disabled selected>Seleccione nota</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button name="saveNotaEvalBtn" class="btn btn-primary w-100">Guardar</button>
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
                            <label class="form-label fw-bold">2. Estudiantes a ser asignados automáticamente:</label>
                            <div class="border p-2 bg-light rounded" style="max-height: 200px; overflow-y: auto;">
                                <ul class="list-unstyled mb-0">
                                <?php
                                $q = "SELECT i.* FROM inscripciones i 
                                      WHERE i.grado = '$grado_f' AND i.seccion = '$seccion_f' AND i.anno_escolar = '$anno_f'
                                      AND i.id NOT IN (SELECT es.id_estudiante FROM estudiantes_salon es JOIN salones s ON es.id_salon = s.id WHERE s.anno_escolar='$anno_f')";
                                $r = mysqli_query($enlace, $q);
                                $total_est = mysqli_num_rows($r);
                                if($total_est > 0) {
                                    while($est = mysqli_fetch_array($r)) {
                                        echo "<li><i class='fa-solid fa-user-check text-success me-2'></i>{$est['nombre']} {$est['apellido']}</li>";
                                    }
                                } else {
                                    echo "<li class='text-danger text-center'>No hay estudiantes pendientes para este grado y sección.</li>";
                                }
                                ?>
                                </ul>
                            </div>
                            <small class="text-muted">Se asignarán <b><?= $total_est ?></b> estudiantes encontrados.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button name="crearSalonBtn" class="btn btn-primary" <?= ($total_est == 0 ? 'disabled' : '') ?>>Confirmar Asignación</button>
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
        var table = $('#tablaSalon').DataTable({
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

        var tableEv = $('#tablaEvaluaciones').DataTable({
            "dom": 'rtip',
            "language": {
                "emptyTable": "No hay evaluaciones registradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ evaluaciones",
                "infoEmpty": "Mostrando 0 a 0 de 0 evaluaciones",
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

        // Función para validar si es inicial y bloquear sección
        function validarSeccion(gradoSelect) {
            const target = $(gradoSelect.data('target'));
            if (!target.length) return;

            const valor = gradoSelect.val() || '';
            const valorMinus = valor.toLowerCase();
            
            // Resetear visibilidad y estado por defecto
            target.find('option').show();
            target.prop('disabled', false);
            target.prop('required', true);

            // 1. Inhabilitar para Simoncito Libertador o Mi esperanza
            if (valorMinus.includes('libertador') || valorMinus.includes('esperanza')) {
                target.prop('disabled', true).val('').prop('required', false);
            }
            // 2. Habilitar Secciones A y B para "grados"
            else if (valorMinus.includes('grado')) {
                target.find('option[value="C"], option[value="D"]').hide();
                // Si estaba seleccionado C o D, limpiar
                if (target.val() === 'C' || target.val() === 'D') target.val('');
            }
        }

        // Evento para cambios en Grado
        $('#grado').on('change', function() {
            validarSeccion($(this));
        });

        // Evento para abrir modal de nota de evaluación
        $(document).on('click', '.btn-nota-ev', function() {
            $('#ne_id_est').val($(this).data('est'));
            $('#ne_id_ev').val($(this).data('ev'));
            $('#ne_titulo_ev').text($(this).data('titulo'));
            $('#notaEvalModal').modal('show');
        });

        // Ejecutar al cargar para reflejar el filtro actual
        validarSeccion($('#grado'));
    });

    // Funciones de Cálculo Rápido
    function obtenerLetra(nota) {
        nota = Math.round(nota);
        if (nota >= 19 && nota <= 20) return 'A';
        if (nota >= 16 && nota <= 18) return 'B';
        if (nota >= 12 && nota <= 15) return 'C';
        if (nota >= 10 && nota <= 11) return 'D';
        if (nota >= 1 && nota <= 9) return 'E';
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