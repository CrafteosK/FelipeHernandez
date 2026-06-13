<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // 1. Obtener total de vigilantes para el control del 50%
    $qTotalVigilantes = "SELECT COUNT(*) as total FROM trabajadores t JOIN cargos c ON t.cargo = c.id WHERE c.nombre LIKE '%Vigilante%'";
    $resTotal = mysqli_query($enlace, $qTotalVigilantes);
    $totalVigilantes = mysqli_fetch_assoc($resTotal)['total'] ?? 0;
    $limiteVacaciones = floor($totalVigilantes / 2);

    // 2. Contar cuántos están actualmente en vacaciones hoy
    $hoy = date('Y-m-d');
    $qActivos = "SELECT COUNT(*) as total FROM vacaciones_vigilantes WHERE '$hoy' BETWEEN fecha_inicio AND fecha_fin";
    $resActivos = mysqli_query($enlace, $qActivos);
    $vacacionesActivas = mysqli_fetch_assoc($resActivos)['total'] ?? 0;

    // 3. Lógica para asignar/editar vacaciones con validación de límite
    if(isset($_POST['saveVacacionBtn'])) {
        $id_trabajador = $_POST['id_trabajador'];
        $inicio = $_POST['fecha_inicio'];
        $fin = $_POST['fecha_fin'];

        // Verificar si es un registro nuevo para aplicar el límite
        $checkExist = mysqli_query($enlace, "SELECT id FROM vacaciones_vigilantes WHERE id_trabajador = '$id_trabajador'");
        
        if(mysqli_num_rows($checkExist) == 0 && $vacacionesActivas >= $limiteVacaciones) {
            echo '<script>alert("Error: Se ha alcanzado el límite del 50% de vigilantes en vacaciones (Máximo: '.$limiteVacaciones.').");</script>';
        } else {
            $sql = "INSERT INTO vacaciones_vigilantes (id_trabajador, fecha_inicio, fecha_fin) 
                    VALUES ('$id_trabajador', '$inicio', '$fin')
                    ON DUPLICATE KEY UPDATE fecha_inicio='$inicio', fecha_fin='$fin'";
            
            if(mysqli_query($enlace, $sql)) {
                echo '<script>alert("Datos de vacaciones actualizados"); window.location.href="vacaciones.php";</script>';
            } else {
                echo '<script>alert("Error al actualizar datos");</script>';
            }
        }
    }

    // 4. Lógica para eliminar registro
    if(isset($_GET['delete'])) {
        $id_v = $_GET['delete'];
        mysqli_query($enlace, "DELETE FROM vacaciones_vigilantes WHERE id = '$id_v'");
        header("Location: vacaciones.php");
        exit();
    }

    // 5. Consulta para traer a TODOS los vigilantes (usamos LEFT JOIN para ver quién trabaja)
    $consultaVigilantes = "
        SELECT v.id AS id_vacacion, t.id AS id_trabajador, t.nombre, t.apellido, t.cedula, 
               v.fecha_inicio, v.fecha_fin 
        FROM trabajadores t 
        JOIN cargos c ON t.cargo = c.id 
        LEFT JOIN vacaciones_vigilantes v ON t.id = v.id_trabajador 
        WHERE c.nombre LIKE '%Vigilante%'
        ORDER BY t.apellido ASC";

    // 6. Lista de vigilantes para el buscador (todos los vigilantes registrados)
    $vigilantesParaAsignar = mysqli_query($enlace, "
        SELECT t.id, t.nombre, t.apellido, t.cedula 
        FROM trabajadores t 
        JOIN cargos c ON t.cargo = c.id 
        WHERE c.nombre LIKE '%Vigilante%'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacaciones Vigilantes - Felipe Hernández</title>
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
            <div class="center"><h1>Gestión de Vacaciones: Vigilantes</h1></div>
            
            <div class="container-top">
                <div class="input-icon" style="width: 250px;">
                    <input class="form-control me-2" type="search" id="busqueda" placeholder="Buscar en tabla..." autocomplete="off">
                    <i class="fa-brands fa-sistrix"></i>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="text-end">
                        <span class="badge bg-primary fs-6">Personal en Vacaciones: <?= $vacacionesActivas ?></span>
                        <br><small class="text-muted">Total Vigilantes: <?= $totalVigilantes ?></small>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVacacionModal">
                        <i class="fa-solid fa-plus me-2"></i>Asignar Vacación
                    </button>
                </div>
            </div>

            <div class="container-table">
                <table id="tablaVacaciones" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Cédula</th>
                            <th>Vigilante</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Días Totales</th>
                            <th>Días Restantes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $resultado = mysqli_query($enlace, $consultaVigilantes);
                        $contador = 1;
                        
                        $hoy_ts = strtotime($hoy);
                        while($row = mysqli_fetch_array($resultado)) {
                            $fecha_inicio = $row['fecha_inicio'];
                            $fecha_fin = $row['fecha_fin'];
                            
                            if (!$fecha_inicio) {
                                $estado = "Trabajando";
                                $clase = "dark";
                                $dias_totales = 0;
                                $dias_restantes = 0;
                            } else {
                                $inicio_ts = strtotime($fecha_inicio);
                                $fin_ts = strtotime($fecha_fin);
                                $dias_totales = round(($fin_ts - $inicio_ts) / (60 * 60 * 24));
                                $dias_restantes = round(($fin_ts - $hoy_ts) / (60 * 60 * 24));

                                if ($hoy_ts > $fin_ts) {
                                    $estado = "Trabajando";
                                    $clase = "dark";
                                    $dias_restantes = 0;
                                } elseif ($hoy_ts >= $inicio_ts) {
                                    $estado = "En Vacaciones";
                                    $clase = "success";
                                } else {
                                    $estado = "Trabajando (Próximas)";
                                    $clase = "info";
                                }
                            }

                            echo '<tr>';
                            echo '<td>'.$contador.'</td>';
                            echo '<td>'.$row['cedula'].'</td>';
                            echo '<td>'.$row['nombre'].' '.$row['apellido'].'</td>';
                            echo '<td>'.($fecha_inicio ?: '---').'</td>';
                            echo '<td>'.($fecha_fin ?: '---').'</td>';
                            echo '<td>'.$dias_totales.'</td>';
                            echo '<td><b class="'.($dias_restantes > 0 ? 'text-primary' : 'text-muted').'">'.($dias_restantes > 0 ? $dias_restantes : 0).'</b></td>';
                            echo '<td><span class="badge bg-'.$clase.'">'.$estado.'</span></td>';
                            echo '<td>
                                <button title="Editar/Asignar" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#vacacionModal'.$row['id_trabajador'].'"><i class="fa-solid fa-calendar-check"></i></button>
                                '.($row['id_vacacion'] ? '<a href="vacaciones.php?delete='.$row['id_vacacion'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Resetear vacaciones de este vigilante?\')"><i class="fa-solid fa-rotate-left"></i></a>' : '').'
                            </td>';
                            echo '</tr>';

                            // Modal para cada vigilante
                            echo '
                            <div class="modal fade" id="vacacionModal'.$row['id_trabajador'].'" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header"><h5>Gestionar Vacaciones: '.$row['nombre'].'</h5></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_trabajador" value="'.$row['id_trabajador'].'">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Fecha Inicio</label>
                                                        <input type="date" name="fecha_inicio" id="edit_start_'.$row['id_trabajador'].'" class="form-control edit-vac-start" value="'.$row['fecha_inicio'].'" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Días Establecidos</label>
                                                        <input type="number" id="edit_days_'.$row['id_trabajador'].'" class="form-control edit-vac-days" value="45">
                                                    </div>
                                                </div>
                                                <div class="p-3 bg-light rounded border text-center">
                                                    <p class="mb-1 text-muted small">Vista Previa de Finalización:</p>
                                                    <h5 class="mb-0 text-primary" id="preview_end_'.$row['id_trabajador'].'">'.($row['fecha_fin'] ?: 'Seleccione fecha').'</h5>
                                                    <input type="hidden" name="fecha_fin" id="edit_end_hidden_'.$row['id_trabajador'].'" value="'.$row['fecha_fin'].'">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button name="saveVacacionBtn" class="btn btn-primary">Guardar Cambios</button>
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
            </div>
            </div>
        </div>
    </main>

    <!-- Modal para asignar nueva vacación con buscador de similitudes -->
    <div class="modal fade" id="addVacacionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header"><h5>Asignar Vacación a Vigilante</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cédula del Vigilante</label>
                            <input type="text" id="busquedaCedula" list="listaVigilantes" class="form-control" placeholder="Escriba números de cédula..." required>
                            <datalist id="listaVigilantes">
                                <?php 
                                mysqli_data_seek($vigilantesParaAsignar, 0);
                                while($v = mysqli_fetch_array($vigilantesParaAsignar)) {
                                    echo '<option value="'.$v['cedula'].'" data-id="'.$v['id'].'">'.$v['nombre'].' '.$v['apellido'].' (C.I: '.$v['cedula'].')</option>';
                                }
                                ?>
                            </datalist>
                            <input type="hidden" name="id_trabajador" id="id_trabajador_hidden">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" id="add_fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Días (Editable)</label>
                                <input type="number" id="add_dias_vac" class="form-control" value="45">
                            </div>
                        </div>
                        <div class="alert alert-info text-center">
                            <span>Finaliza el: </span><b id="add_preview_fin">---</b>
                            <input type="hidden" name="fecha_fin" id="add_fecha_fin_hidden">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button name="saveVacacionBtn" class="btn btn-primary">Confirmar Asignación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
        var table = $('#tablaVacaciones').DataTable({
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

    // Lógica para el buscador de similitudes (Datalist)
    document.getElementById('busquedaCedula').addEventListener('input', function() {
        const val = this.value;
        const options = document.getElementById('listaVigilantes').options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                document.getElementById('id_trabajador_hidden').value = options[i].getAttribute('data-id');
                break;
            }
        }
    });

    // Lógica para calcular fecha de fin automáticamente
    function calcularFechaFin(fechaInicio, dias, elementPreview, elementHidden) {
        if(!fechaInicio || !dias) return;
        let start = new Date(fechaInicio);
        start.setDate(start.getDate() + parseInt(dias));
        let result = start.toISOString().split('T')[0];
        $(elementPreview).text(result);
        $(elementHidden).val(result);
    }

    // Para el modal de agregar
    $('#add_fecha_inicio, #add_dias_vac').on('change keyup', function() {
        calcularFechaFin($('#add_fecha_inicio').val(), $('#add_dias_vac').val(), '#add_preview_fin', '#add_fecha_fin_hidden');
    });

    // Para los modales de edición (usando delegación de eventos)
    $(document).on('change keyup', '.edit-vac-start, .edit-vac-days', function() {
        let id = $(this).attr('id').split('_').pop();
        calcularFechaFin($('#edit_start_'+id).val(), $('#edit_days_'+id).val(), '#preview_end_'+id, '#edit_end_hidden_'+id);
    });
    </script>
</body>
</html>