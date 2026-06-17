<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    //logica para agregar trabajador

    if(isset($_POST['addAsistenciaBtn'])) { 
        $id_trabajador = $_POST['trabajador'];

        // 1. Obtener la información del trabajador seleccionado
        $consultaInfo = "SELECT t.*, c.nombre AS cargo_nombre 
                         FROM trabajadores t 
                         JOIN cargos c ON t.cargo = c.id 
                         WHERE t.id = '$id_trabajador'";
        $resultadoInfo = mysqli_query($enlace, $consultaInfo);

        if ($rowInfo = mysqli_fetch_array($resultadoInfo)) {
            $nombre = $rowInfo['nombre'];
            $apellido = $rowInfo['apellido'];
            $cedula = $rowInfo['cedula'];
            $telefono = $rowInfo['telefono'];
            // Asegúrate de que la zona horaria esté configurada antes de obtener la fecha
            date_default_timezone_set('America/Caracas'); // Ajusta a tu zona horaria
            $fecha = date('Y-m-d');

            // Validar si el trabajador tiene un reposo médico vigente
            $consultaReposo = "SELECT vencimiento FROM reposo_medico WHERE cedula = '$cedula' AND '$fecha' <= vencimiento ORDER BY vencimiento DESC LIMIT 1";
            $resultadoReposo = mysqli_query($enlace, $consultaReposo);

            if (mysqli_num_rows($resultadoReposo) > 0) {
                $reposo = mysqli_fetch_assoc($resultadoReposo);
                echo '<script>alert("No se puede registrar la asistencia. El trabajador '.$nombre.' '.$apellido.' tiene un reposo médico vigente hasta el '.$reposo['vencimiento'].'."); window.location = "asistencias.php";</script>';
                exit;
            }

            // Verificar si el trabajador ya tiene una asistencia registrada para hoy
            $verificarAsistenciaHoy = "SELECT COUNT(*) FROM asistencias WHERE cedula = '$cedula' AND fecha = '$fecha'";
            $resultadoVerificacion = mysqli_query($enlace, $verificarAsistenciaHoy);
            $asistenciasHoy = mysqli_fetch_row($resultadoVerificacion)[0];
            $cargo = $rowInfo['cargo_nombre'];
            
            // 2. Obtener fecha y hora del servidor
            date_default_timezone_set('America/Caracas'); // Ajusta a tu zona horaria
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            if ($asistenciasHoy > 0) {
                echo '<script>alert("El trabajador '.$nombre.' '.$apellido.' (C.I.: '.$cedula.') ya tiene una asistencia registrada para hoy."); window.location = "asistencias.php";</script>';
            } else {
                // 3. Insertar en la tabla asistencias (copia de datos)
                $insertarAsistencia = "INSERT INTO asistencias (nombre, apellido, cedula, telefono, cargo, fecha, hora) 
                                       VALUES ('$nombre', '$apellido', '$cedula', '$telefono', '$cargo', '$fecha', '$hora')";
                
                if(mysqli_query($enlace, $insertarAsistencia)) {
                    echo '<script>alert("Asistencia registrada correctamente"); window.location = "asistencias.php";</script>';
                } else {
                    echo '<script>alert("Error al registrar la asistencia");</script>';
                }
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
    
    $consultaAsistencias = "SELECT * FROM asistencias $whereClause ORDER BY id DESC";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias - Felipe Hernández</title>
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
            
                        <div class="input-icon
            <!-- Centro -->
                <div class="center"><h1>Asistencias</h1></div>

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
                    <a href="includes/descargar-asistencias.php?filtroFecha=<?= $filtroFecha ?>&fechaInicio=<?= $fechaInicio ?>&fechaFin=<?= $fechaFin ?>" class="btn btn-primary btn-pad">
                        <i class="fa-solid fa-file-pdf"></i> Exportar
                    </a>

                    <!-- FPDF -->



                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fa-solid fa-plus"></i> Agregar
                    </button>


                    <!-- Modal para agregar trabajador -->
                    <div class="modal fade" tabindex="-1" id="exampleModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Agregar Asistencia</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="#" method="post">
                                    <div class="modal-body">
                                          <div class="mb-3">
                                              <label for="trabajador" class="form-label">Seleccione un Trabajador</label>
                                              <div class="input-icon">
                                                <input type="text" id="buscarAsistenciaTrabajador" list="listaTrabajadores" class="form-control" placeholder="Escriba nombre o cédula..." required autocomplete="off">
                                                <datalist id="listaTrabajadores">
                                                    <?php 
                                                    $consultaTrabajadores = "SELECT * FROM trabajadores";
                                                    $resultadoTrabajadores = mysqli_query($enlace, $consultaTrabajadores);
                                                    while ($trabajadorRow = mysqli_fetch_array($resultadoTrabajadores)) {
                                                        echo '<option value="'.$trabajadorRow['nombre'].' '.$trabajadorRow['apellido'].' (C.I: '.$trabajadorRow['cedula'].')" data-id="'.$trabajadorRow['id'].'">'; 
                                                    }
                                                    ?>
                                                </datalist>
                                                <input type="hidden" name="trabajador" id="id_trabajador_hidden_asistencia">
                                                <i class="fa-solid fa-angle-down"></i>
                                            </div>

                                          </div>

                                          <div class="mb-3">
                                              <p>O</p>
                                          </div>

                                            <div class="mb-3">
                                                <p>Escanee el código QR</p>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button class="btn btn-primary" name="addAsistenciaBtn">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            
            </div>
            <div class="container-table">
                    <table id="tablaAsistencias" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cedula</th>
                                <th>Telefono</th>
                                <th>Cargo</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $resultado = mysqli_query($enlace, $consultaAsistencias);
                                $contador = 1;
                                while($row = mysqli_fetch_array($resultado)) {
                                    echo '
                                            <tr>
                                                    <td>'.$contador.'</td>
                                                    <td>'.$row['nombre'].'</td>
                                                    <td>'.$row['apellido'].'</td>
                                                    <td>'.$row['cedula'].'</td>
                                                    <td>'.$row['telefono'].'</td>
                                                    <td>'.$row['cargo'].'</td>
                                                    <td>'.$row['fecha'].'</td>
                                                    <td>'.$row['hora'].'</td>
                                            </tr>';
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
        var table = $('#tablaAsistencias').DataTable({
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

        // Lógica para vincular el datalist con el input hidden de asistencia
        document.getElementById('buscarAsistenciaTrabajador').addEventListener('input', function() {
            const val = this.value;
            const options = document.getElementById('listaTrabajadores').options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    document.getElementById('id_trabajador_hidden_asistencia').value = options[i].getAttribute('data-id');
                    break;
                }
            }
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
</body>
</html>