<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // Lógica para actualizar nota
    if(isset($_POST['saveNotaBtn'])) {
        $id = $_POST['id'];
        $nota = $_POST['nota'];
        $actualizarNota = "UPDATE inscripciones SET nota='$nota' WHERE id='$id'";
        if(mysqli_query($enlace, $actualizarNota)) {
            echo '<script>alert("Nota actualizada"); window.location.href=window.location.href;</script>';
        } else {
            echo '<script>alert("Error al actualizar nota");</script>';
        }
    }

    // Filtros
    $anno_filtro = isset($_GET['anno_escolar']) ? $_GET['anno_escolar'] : '';
    $grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas - Felipe Hernández</title>
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
    <link href="datatables/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-5.3.8-examples/assets/dist/css/bootstrap.min.css">  <!--Opcional: agrega estilos a las tablas -->
    <link rel="stylesheet" href="css/nav-side.css">
    <link rel="stylesheet" href="css/trabajadores.css">
</head>
<body>
    <header><?php include 'includes/nav.php'; ?></header> <?php include 'includes/side.php'; ?>
    <main id="main">

        <div class="container my-4">

            <!-- Centro -->
                <div class="center"><h1>Control de Notas</h1></div>

            <!-- Pestañas -->
            <ul class="nav nav-tabs mb-4" id="notasTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="estudiantes-tab" data-bs-toggle="tab" data-bs-target="#estudiantes" type="button" role="tab">Notas Estudiantes</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="calculo-tab" data-bs-toggle="tab" data-bs-target="#calculo-rapido" type="button" role="tab">Cálculo Rápido</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="estudiantes" role="tabpanel">
                    <div class="container-top">
                        <form action="" method="GET" class="d-flex align-items-center gap-3">
                            <div class="input-icon">
                                <select name="anno_escolar" class="form-control" required>
                                    <option value="">Año Escolar</option>
                                    <?php
                                    $qAnnos = "SELECT DISTINCT anno_escolar FROM inscripciones ORDER BY anno_escolar DESC";
                                    $rAnnos = mysqli_query($enlace, $qAnnos);
                                    while($a = mysqli_fetch_array($rAnnos)) {
                                        $sel = ($anno_filtro == $a['anno_escolar']) ? 'selected' : '';
                                        echo '<option value="'.$a['anno_escolar'].'" '.$sel.'>'.$a['anno_escolar'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-icon">
                                <select name="grado" class="form-control">
                                    <option value="">Todos los grados</option>
                                    <?php
                                    $grados = ["inicial A", "inicial B", "inicial C", "1er grado", "2do grado", "3ro grado", "4to grado", "5to grado", "6to grado", "Promovido"];
                                    foreach($grados as $g) {
                                        $sel = ($grado_filtro == $g) ? 'selected' : '';
                                        echo '<option value="'.$g.'" '.$sel.'>'.$g.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </form>
                        
                        <div class="input-icon" style="width: 250px;">
                            <input class="form-control me-2" type="search" id="busqueda" placeholder="Buscar en resultados">
                            <i class="fa-brands fa-sistrix"></i>
                        </div>
                    </div>

                    <div class="container-table">
                        <?php if($anno_filtro): ?>
                            <table id="tablaNotas" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nº</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Grado</th>
                                        <th>Sección</th>
                                        <th>Nota</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $query = "SELECT * FROM inscripciones WHERE anno_escolar = '$anno_filtro'";
                                        if($grado_filtro) $query .= " AND grado = '$grado_filtro'";
                                        $resultado = mysqli_query($enlace, $query);
                                        $contador = 1;
                                        while($row = mysqli_fetch_array($resultado)) {
                                            echo '<tr>
                                                <td>'.$contador.'</td>
                                                <td>'.$row['nombre'].'</td>
                                                <td>'.$row['apellido'].'</td>
                                                <td>'.$row['grado'].'</td>
                                                <td>'.$row['seccion'].'</td>
                                                <td>
                                                    <span class="nota-indicador nota-'.(isset($row['nota']) && $row['nota'] != 'S/N' ? $row['nota'] : 'SN').'">
                                                        '.(isset($row['nota']) && $row['nota'] ? $row['nota'] : 'S/N').'
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#notaModal'.$row['id'].'"><i class="fa-solid fa-marker"></i></button>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="notaModal'.$row['id'].'" tabindex="-1">
                                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5>Asignar Nota</h5></div>
                                                        <form method="post">
                                                            <input type="hidden" name="id" value="'.$row['id'].'">
                                                            <div class="modal-body text-center">
                                                                <select name="nota" class="form-control form-control-lg" required>
                                                                    <option value="A" '.(isset($row['nota']) && $row['nota']=='A'?'selected':'').'>A</option>
                                                                    <option value="B" '.(isset($row['nota']) && $row['nota']=='B'?'selected':'').'>B</option>
                                                                    <option value="C" '.(isset($row['nota']) && $row['nota']=='C'?'selected':'').'>C</option>
                                                                    <option value="D" '.(isset($row['nota']) && $row['nota']=='D'?'selected':'').'>D</option>
                                                                    <option value="E" '.(isset($row['nota']) && $row['nota']=='E'?'selected':'').'>E</option>
                                                                </select>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button name="saveNotaBtn" class="btn btn-primary w-100">Guardar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>';
                                            $contador++;
                                        }
                                    ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-info text-center mt-5">
                                <i class="fa-solid fa-circle-info"></i> Por favor, seleccione un <b>Año Escolar</b> para ver las notas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="calculo-rapido" role="tabpanel">
                    <div class="row mt-4">
                        <!-- Calculadora de Promedio -->
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fa-solid fa-calculator me-2"></i>Calcular Promedio</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Ingrese las notas numéricas separadas por comas o espacios:</p>
                                    <div class="mb-3">
                                        <input type="text" id="inputPromedio" class="form-control" placeholder="Ej: 15, 18, 20">
                                    </div>
                                    <button type="button" onclick="calcularPromedioLetra()" class="btn btn-primary w-100 mb-3">Calcular Promedio</button>
                                    <div id="resultadoPromedio" class="alert alert-secondary d-none text-center">
                                        <h5 class="mb-1">Promedio Numérico: <span id="valPromedio"></span></h5>
                                        <h3 class="mb-0">Nota Cualitativa: <span id="letraPromedio" class="badge bg-success"></span></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conversor Directo -->
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fa-solid fa-right-left me-2"></i>Conversor Directo</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Ingrese una nota específica (01 al 20):</p>
                                    <div class="mb-3">
                                        <input type="number" id="inputDirecto" class="form-control" min="1" max="20" placeholder="Ej: 19">
                                    </div>
                                    <button type="button" onclick="convertirDirecto()" class="btn btn-success w-100 mb-3">Ver Equivalencia</button>
                                    <div id="resultadoDirecto" class="alert alert-secondary d-none text-center">
                                        <h3 class="mb-0">Equivale a: <span id="letraDirecto" class="badge bg-primary"></span></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de Referencia Visual -->
                    <div class="row mt-4 justify-content-center">
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-bordered mb-0 text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Rango Cuantitativo</th>
                                                <th>Nota Cualitativa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>20 - 19</td><td><b class="text-success">A</b></td></tr>
                                            <tr><td>18 - 16</td><td><b class="text-primary">B</b></td></tr>
                                            <tr><td>15 - 12</td><td><b class="text-info">C</b></td></tr>
                                            <tr><td>11 - 10</td><td><b class="text-warning text-dark">D</b></td></tr>
                                            <tr><td>09 - 01</td><td><b class="text-warning">E</b></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
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