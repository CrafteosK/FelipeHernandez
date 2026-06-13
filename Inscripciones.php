<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // Función para obtener el año escolar actual (Septiembre a Julio)
    function obtenerAnnoEscolarActual() {
        $mes = (int)date('n');
        $anio = (int)date('Y');
        if ($mes >= 9) { // Septiembre en adelante
            return $anio . "-" . ($anio + 1);
        } else { // Enero a Julio (Agosto es vacación)
            return ($anio - 1) . "-" . $anio;
        }
    }

    //logica para agregar inscripcion
    if(isset($_POST['addInscripcionBtn'])) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $sexo = $_POST['sexo'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $grado = $_POST['grado'];
        $anno_escolar = $_POST['anno_escolar'];
        $telefono_representante = $_POST['telefono_representante'];

        $insertarInscripcion = "INSERT INTO inscripciones (nombre, apellido, sexo, fecha_nacimiento, grado, anno_escolar, telefono_representante) 
                                VALUES ('$nombre', '$apellido', '$sexo', '$fecha_nacimiento', '$grado', '$anno_escolar', '$telefono_representante')";

        if(mysqli_query($enlace, $insertarInscripcion)) {
            echo '<script>alert("Inscripción realizada correctamente"); window.location = "Inscripciones.php";</script>';
        } else {
            echo '<script>alert("Error al realizar la inscripción");</script>';
        }
    }

    // Lógica para editar inscripcion
    if(isset($_POST['editInscripcionBtn'])) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $sexo = $_POST['sexo'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $grado = $_POST['grado'];
        $anno_escolar = $_POST['anno_escolar'];
        $telefono_representante = $_POST['telefono_representante'];

        $actualizarInscripcion = "UPDATE inscripciones SET nombre='$nombre', apellido='$apellido', sexo='$sexo', fecha_nacimiento='$fecha_nacimiento', grado='$grado', anno_escolar='$anno_escolar', telefono_representante='$telefono_representante' WHERE id='$id'";
        if(mysqli_query($enlace, $actualizarInscripcion)) {
            echo '<script>alert("Inscripción actualizada correctamente"); window.location = "Inscripciones.php";</script>';
        } else {
            echo '<script>alert("Error al actualizar la inscripción");</script>';
        }
    }

    // Lógica para eliminar inscripcion
    if(isset($_POST['deleteInscripcionBtn'])) {
        $id = $_POST['id'];
        $eliminarInscripcion = "DELETE FROM inscripciones WHERE id='$id'";
        if(mysqli_query($enlace, $eliminarInscripcion)) {
            echo '<script>alert("Inscripción eliminada correctamente"); window.location = "Inscripciones.php";</script>';
        } else {
            echo '<script>alert("Error al eliminar la inscripción");</script>';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - Felipe Hernández</title>
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
                <div class="center"><h1>Inscripciones</h1></div>

            <div class="container-top">

                <!-- Buscador -->
                <div class="d-flex align-items-center gap-3">
                    <div class="input-icon" style="width: 250px;">
                        <input class="form-control me-2" type="search" name="busqueda" id="busqueda" placeholder="Buscar" aria-label="Buscar" autocomplete="off">
                        <i class="fa-brands fa-sistrix"></i>
                    </div>
                </div>

                <!-- Left -->
                <div class="left">
                    <a href="includes/descargar-estudiantes.php" class="btn btn-primary btn-pad">
                        <i class="fa-solid fa-file-pdf"></i> Exportar
                    </a>

                    <!-- FPDF -->



                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fa-solid fa-plus"></i> Inscribir Estudiante
                    </button>

                    <!-- Modal para agregar inscripcion -->
                    <div class="modal fade" tabindex="-1" id="exampleModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Nueva Inscripción</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="#" method="post">
                                    <?php $anno_sugerido = obtenerAnnoEscolarActual(); ?>
                                    <div class="modal-body">
                                          <div class="mb-3">
                                              <label for="nombre" class="form-label">Nombre del Estudiante</label>
                                              <input type="text" class="form-control" id="nombre" name="nombre" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="apellido" class="form-label">Apellido del Estudiante</label>
                                              <input type="text" class="form-control" id="apellido" name="apellido" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                              <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="grado" class="form-label">Grado</label>
                                              <select class="form-control" id="grado" name="grado" required>
                                                  <option value="" disabled selected>Seleccionar grado</option>
                                                  <option value="inicial A">Inicial A</option>
                                                  <option value="inicial B">Inicial B</option>
                                                  <option value="inicial C">Inicial C</option>
                                                  <option value="1er grado">1er grado</option>
                                                  <option value="2do grado">2do grado</option>
                                                  <option value="3ro grado">3ro grado</option>
                                                  <option value="4to grado">4to grado</option>
                                                  <option value="5to grado">5to grado</option>
                                                  <option value="6to grado">6to grado</option>
                                                  <option value="Promovido">Promovido</option>
                                              </select>
                                          </div>
                                          <div class="mb-3">
                                              <label for="sexo" class="form-label">Sexo</label>
                                              <select class="form-control" id="sexo" name="sexo" required>
                                                  <option value="" disabled selected>Seleccionar sexo</option>
                                                  <option value="M">Masculino (M)</option>
                                                  <option value="F">Femenino (F)</option>
                                              </select>
                                          </div>
                                          <div class="mb-3">
                                              <label for="anno_escolar" class="form-label">Año Escolar</label>
                                              <input type="text" class="form-control" id="anno_escolar" name="anno_escolar" value="<?php echo $anno_sugerido; ?>" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="telefono_representante" class="form-label">Teléfono Representante</label>
                                              <input type="text" class="form-control" id="telefono_representante" name="telefono_representante" required>
                                          </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button class="btn btn-primary" name="addInscripcionBtn">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-table">
                    <table id="tablaInscripciones" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Fecha Nacimiento</th>
                                <th>Grado</th>
                                <th>Sexo</th>
                                <th>Año Escolar</th>
                                <th>Teléfono Representante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $consultaInscripciones = "SELECT * FROM inscripciones";
                                $resultado = mysqli_query($enlace, $consultaInscripciones);
                                $contador = 1;
                                while($row = mysqli_fetch_array($resultado)) {
                                    $id_est = $row['id'];
                                    $gradoActual = $row['grado'];
                                    
                                    $gradosList = ["inicial A", "inicial B", "inicial C", "1er grado", "2do grado", "3ro grado", "4to grado", "5to grado", "6to grado", "Promovido"];
                                    $optionsGrado = '<option value="" disabled>Seleccionar grado</option>';
                                    foreach($gradosList as $g) {
                                        $sel = ($gradoActual == $g) ? "selected" : "";
                                        $optionsGrado .= '<option value="'.$g.'" '.$sel.'>'.$g.'</option>';
                                    }
                                    echo '
                                            <tr>
                                                    <td>'.$contador.'</td>
                                                    <td>'.$row['nombre'].'</td>
                                                    <td>'.$row['apellido'].'</td>
                                                    <td>'.$row['fecha_nacimiento'].'</td>
                                                    <td>'.$row['grado'].'</td>
                                                    <td>'.($row['sexo'] == 'M' ? 'M' : 'F').'</td>
                                                    <td>'.$row['anno_escolar'].'</td>
                                                    <td>'.$row['telefono_representante'].'</td>
                                                    <td>
                                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal'.$row['id'].'"><i class="fa-solid fa-pen-to-square"></i></button>
                                                            <form action="#" method="post" style="display:inline;" onsubmit="return false;" id="deleteForm'.$row['id'].'">
                                                                <input type="hidden" name="id" value="'.$row['id'].'">
                                                                <input type="hidden" name="deleteInscripcionBtn" value="1">
                                                                <button type="button" class="btn btn-danger" onclick="if(confirm(\'¿Seguro que deseas eliminar a este estudiante?\')) { document.getElementById(\'deleteForm'.$row['id'].'\').submit(); }"><i class="fa-solid fa-trash"></i></button>
                                                            </form>
                                                    </td>
                                            </tr>
                                            <!-- Modal de edición -->
                                            <div class="modal fade" id="editModal'.$row['id'].'" tabindex="-1" aria-labelledby="editModalLabel'.$row['id'].'" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel'.$row['id'].'">Editar Inscripción</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="#" method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="'.$row['id'].'">
                                                                <div class="mb-3">
                                                                    <label for="nombre'.$row['id'].'" class="form-label">Nombre</label>
                                                                    <input type="text" class="form-control" id="nombre'.$row['id'].'" name="nombre" value="'.$row['nombre'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="apellido'.$row['id'].'" class="form-label">Apellido</label>
                                                                    <input type="text" class="form-control" id="apellido'.$row['id'].'" name="apellido" value="'.$row['apellido'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="fecha_nacimiento'.$row['id'].'" class="form-label">Fecha de Nacimiento</label>
                                                                    <input type="date" class="form-control" id="fecha_nacimiento'.$row['id'].'" name="fecha_nacimiento" value="'.$row['fecha_nacimiento'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="grado'.$row['id'].'" class="form-label">Grado</label>
                                                                    <select class="form-control grado-select-edit" id="grado'.$row['id'].'" name="grado" data-target="#seccion'.$row['id'].'" required>
                                                                        '.$optionsGrado.'
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="sexo'.$row['id'].'" class="form-label">Sexo</label>
                                                                    <select class="form-control" id="sexo'.$row['id'].'" name="sexo" required>
                                                                        <option value="M" '.($row['sexo'] == 'M' ? 'selected' : '').'>Masculino (M)</option>
                                                                        <option value="F" '.($row['sexo'] == 'F' ? 'selected' : '').'>Femenino (F)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="anno_escolar'.$row['id'].'" class="form-label">Año Escolar</label>
                                                                    <input type="text" class="form-control" id="anno_escolar'.$row['id'].'" name="anno_escolar" value="'.$row['anno_escolar'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="telefono_representante'.$row['id'].'" class="form-label">Teléfono Representante</label>
                                                                    <input type="text" class="form-control" id="telefono_representante'.$row['id'].'" name="telefono_representante" value="'.$row['telefono_representante'].'" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button class="btn btn-primary" name="editInscripcionBtn">Guardar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                    ';
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
        var table = $('#tablaInscripciones').DataTable({
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
</body>
</html>