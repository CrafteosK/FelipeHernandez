<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    //logica para agregar reposo medico

    if(isset($_POST['addReposoBtn'])) { 
        $id_trabajador = $_POST['trabajador'];
        $expedicion = $_POST['expedicion'];
        $vencimiento = $_POST['vencimiento'];
        $patologia = mysqli_real_escape_string($enlace, trim($_POST['patologia']));

        // 1. Obtener la información del trabajador seleccionado
        $consultaInfo = "SELECT t.*, c.nombre AS cargo_nombre 
                         FROM trabajadores t 
                         JOIN cargos c ON t.cargo = c.id 
                         WHERE t.id = '$id_trabajador'";
        $resultadoInfo = mysqli_query($enlace, $consultaInfo);

        if ($rowInfo = mysqli_fetch_array($resultadoInfo)) {
            // Validación del lado del servidor para la patología
            if (empty($patologia)) {
                echo '<script>alert("Error: La patología es obligatoria para registrar el reposo."); window.history.back();</script>';
                exit;
            }

            $nombre = $rowInfo['nombre'];
            $apellido = $rowInfo['apellido'];
            $cedula = $rowInfo['cedula'];
            $telefono = $rowInfo['telefono'];
            $cargo = $rowInfo['cargo_nombre'];

            // Insertar en la tabla reposo_medico
            $insertarReposo = "INSERT INTO reposo_medico (nombre, apellido, cedula, telefono, cargo, patologia, expedicion, vencimiento) 
                                VALUES ('$nombre', '$apellido', '$cedula', '$telefono', '$cargo', '$patologia', '$expedicion', '$vencimiento')";
            if(mysqli_query($enlace, $insertarReposo)) {
                echo '<script>alert("Reposo médico registrado correctamente"); window.location = "reposo.php";</script>';
            } else {
                echo '<script>alert("Error al registrar el reposo médico");</script>';
            }
        } else {
            echo '<script>alert("No se encontró el trabajador seleccionado");</script>';
        }
    }

    // Lógica de filtrado por fecha
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
    $consultaReposo = "SELECT * FROM reposo_medico $whereClause ORDER BY id DESC";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reposo Médico- Felipe Hernández</title>
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
                <div class="center"><h1>Reposo Médico</h1></div>

            <div class="container-top">
                <!-- Filtros -->
                <div class="d-flex align-items-center gap-3">
                    <form action="" method="GET" class="d-flex align-items-center gap-2" id="formFiltro">
                        <div class="input-icon">
                            <select name="filtroFecha" id="filtroFecha" class="form-control" style="width: auto;">
                                <option value="todos" <?= $filtroFecha == 'todos' ? 'selected' : '' ?>>Todos los registros </option>
                                <option value="hoy" <?= $filtroFecha == 'hoy' ? 'selected' : '' ?>>Hoy</option>
                                <option value="ayer" <?= $filtroFecha == 'ayer' ? 'selected' : '' ?>>Ayer</option>
                                <option value="7dias" <?= $filtroFecha == '7dias' ? 'selected' : '' ?>>Últimos 7 días</option>
                                <option value="mes" <?= $filtroFecha == 'mes' ? 'selected' : '' ?>>Último mes</option>
                                <option value="3meses" <?= $filtroFecha == '3meses' ? 'selected' : '' ?>>Últimos 3 meses</option>
                                <option value="personalizado" <?= $filtroFecha == 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
                            </select>
                            <i class="fa-solid fa-angle-down"></i>
                        </div>
                        
                        <div id="customDateInputs" style="display: <?= $filtroFecha == 'personalizado' ? 'flex' : 'none' ?>;" class="align-items-center gap-2">
                            <input type="date" name="fechaInicio" class="form-control" value="<?= $fechaInicio ?>" style="width: auto;">
                            <input type="date" name="fechaFin" class="form-control" value="<?= $fechaFin ?>" style="width: auto;">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i></button>
                        </div>
                    </form>
                    <div class="input-icon" style="width: 250px;">
                        <input class="form-control me-2" type="search" name="busqueda" id="busqueda" placeholder="Buscar" aria-label="Buscar" autocomplete="off">
                        <i class="fa-brands fa-sistrix"></i>
                    </div>
                </div>

                <!-- Left -->
                <div class="left">
                    <a href="includes/descargar-reposo.php?filtroFecha=<?= $filtroFecha ?>&fechaInicio=<?= $fechaInicio ?>&fechaFin=<?= $fechaFin ?>" class="btn btn-primary btn-pad">
                        <i class="fa-solid fa-file-pdf"></i> Exportar
                    </a>

                    <!-- FPDF -->



                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fa-solid fa-plus"></i> Agregar
                    </button>


                    <!-- Modal para agregar reposo medico -->
                    <div class="modal fade" tabindex="-1" id="exampleModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Agregar Reposo Médico</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="#" method="post">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="trabajador" class="form-label">Seleccione un Trabajador</label>
                                            <div class="input-icon">
                                                <select name="trabajador" id="trabajador" class="form-control" required>
                                                    <option value="">Seleccione un trabajador</option>
                                                    <?php 
                                                    $consultaTrabajadores = "SELECT * FROM trabajadores";
                                                    $resultadoTrabajadores = mysqli_query($enlace, $consultaTrabajadores);
                                                    while ($trabajadorRow = mysqli_fetch_array($resultadoTrabajadores)) {
                                                        echo '<option value="'.$trabajadorRow['id'].'">'.$trabajadorRow['nombre'].' '.$trabajadorRow['apellido'].'</option>'; 
                                                    }
                                                    ?>
                                                </select>
                                                <i class="fa-solid fa-angle-down"></i>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="expedicion" class="form-label">Fecha de Expedición</label>
                                            <input type="date" name="expedicion" id="expedicion" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="patologia" class="form-label">Patología / Motivo</label>
                                            <textarea name="patologia" id="patologia" class="form-control" placeholder="Describa la patología que presenta el trabajador" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="vencimiento" class="form-label">Fecha de Vencimiento</label>
                                            <input type="date" name="vencimiento" id="vencimiento" class="form-control" required>
                                        </div>
                                        <div class="alert alert-info py-2">
                                            Total de días: <b id="preview-dias">0</b>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button class="btn btn-primary" name="addReposoBtn">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            
            </div>
            <div class="container-table">
                <table id="tablaReposo" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Cargo</th>
                            <th>Patología</th>
                            <th>Fecha de Expedición</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Días</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $resultado = mysqli_query($enlace, $consultaReposo);
                        $contador = 1;
                        $hoy = date('Y-m-d');
                        while($row = mysqli_fetch_array($resultado)) {
                            $esVigente = strtotime($row['vencimiento']) >= strtotime($hoy);
                            $estado = $esVigente ? 'Vigente' : 'Vencido';
                            $claseEstado = $esVigente ? 'estado-vigente' : 'estado-vencido';

                            $f_inicio = new DateTime($row['expedicion']);
                            $f_fin = new DateTime($row['vencimiento']);
                            $intervalo = $f_inicio->diff($f_fin);
                            $dias_totales = $intervalo->days + 1;

                            echo '<tr>';
                            echo '<td>'.$contador.'</td>';
                            echo '<td>'.$row['nombre'].'</td>';
                            echo '<td>'.$row['apellido'].'</td>';
                            echo '<td>'.$row['cedula'].'</td>';
                            echo '<td>'.$row['telefono'].'</td>';
                            echo '<td>'.$row['cargo'].'</td>';
                            echo '<td>'.$row['patologia'].'</td>';
                            echo '<td>'.$row['expedicion'].'</td>';
                            echo '<td>'.$row['vencimiento'].'</td>';
                            echo '<td>'.$dias_totales.'</td>';
                            echo '<td><span class="badge-estado ' . $claseEstado . '">' . $estado . '</span></td>';
                            echo '<td>
                                    <a href="includes/descargar-reposo-individual.php?id='.$row['id'].'" class="btn btn-info btn-sm" title="Descargar Comprobante" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>
                                  </td>';
                            echo '</tr>';
                            $contador++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </main>
    <!-- jQuery debe ir primero -->
    <script src="js/jquery.min.js"></script>
    <!-- Bootstrap Bundle JS (incluye Popper) -->
    <script src="bootstrap-5.3.8-examples/assets/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="datatables/datatables.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="js/nav-side.js"></script>
    <script src="js/trabajadores.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#tablaReposo').DataTable({
            "dom": 'rtip', // Oculta el buscador por defecto de DataTable para usar el tuyo
            "language": {
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });

        // Vincular tu input de búsqueda personalizado con DataTable
        $('#busqueda').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
    </script>
    <script>
    // Mostrar/ocultar inputs de fecha personalizados
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const customInputs = document.getElementById('customDateInputs');
        if (this.value === 'personalizado') {
            customInputs.style.display = 'flex';
        } else {
            customInputs.style.display = 'none';
            this.form.submit();
        }
    });
    </script>
    <script>
    function calcularDiasReposo() {
        const exp = $('#expedicion').val();
        const ven = $('#vencimiento').val();
        if (exp && ven) {
            const start = new Date(exp);
            const end = new Date(ven);
            const diffTime = end - start;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            $('#preview-dias').text(diffDays > 0 ? diffDays : 0);
        }
    }
    $('#expedicion, #vencimiento').on('change', calcularDiasReposo);
    </script>
</body>
</html>