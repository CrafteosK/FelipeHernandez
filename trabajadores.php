<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    //logica para agregar trabajador

    if(isset($_POST['addTrabajadorBtn'])) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $cedula = $_POST['cedula'];
        $telefono = $_POST['telefono'];
        $cargo = $_POST['cargo'];

        $insertarTrabajador = "INSERT INTO trabajadores (nombre, apellido, cedula, telefono, cargo) VALUES ('$nombre', '$apellido', '$cedula', '$telefono', '$cargo')";

        //VERIFICAR SI EL TRABAJADOR YA EXISTE
        $verificarTrabajador = "SELECT * FROM trabajadores WHERE cedula = '$cedula'";
        $resultado = mysqli_query($enlace, $verificarTrabajador);

        if(mysqli_num_rows($resultado) == 0) {
            mysqli_query($enlace, $insertarTrabajador);
        }
        else {
            echo '
                <script>
                    alert("El trabajador ya existe, intenta con otro diferente");
                    window.location = "trabajadores.php";
                </script>
            ';
        }
    }

    // Lógica para editar trabajador
    if(isset($_POST['editTrabajadorBtn'])) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $cedula = $_POST['cedula'];
        $telefono = $_POST['telefono'];
        $cargo = $_POST['cargo'];

        $actualizarTrabajador = "UPDATE trabajadores SET nombre='$nombre', apellido='$apellido', cedula='$cedula', telefono='$telefono', cargo='$cargo' WHERE id='$id'";
        if(mysqli_query($enlace, $actualizarTrabajador)) {
            echo '<script>alert("Trabajador actualizado correctamente"); window.location = "trabajadores.php";</script>';
        } else {
            echo '<script>alert("Error al actualizar trabajador");</script>';
        }
    }

    // Lógica para eliminar trabajador
        if(isset($_POST['deleteTrabajadorBtn'])) {
            $id = $_POST['id'];
            $eliminarTrabajador = "DELETE FROM trabajadores WHERE id='$id'";
            if(mysqli_query($enlace, $eliminarTrabajador)) {
                echo '<script>alert("Trabajador eliminado correctamente"); window.location = "trabajadores.php";</script>';
            } else {
                echo '<script>alert("Error al eliminar trabajador");</script>';
            }
        }

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajadores - Felipe Hernández</title>
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
                <div class="center"><h1>Trabajadores</h1></div>

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
                    <a href="includes/descargar.php?cargo=<?php echo isset($_GET['cargo']) ? $_GET['cargo'] : 'todos'; ?>" class="btn btn-primary btn-pad">
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
                                    <h5 class="modal-title">Agregar Trabajador</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="#" method="post">
                                    <div class="modal-body">
                                          <div class="mb-3">
                                              <label for="nombre" class="form-label">Nombre</label>
                                              <input type="text" class="form-control" id="nombre" name="nombre" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="apellido" class="form-label">Apellido</label>
                                              <input type="text" class="form-control" id="apellido" name="apellido" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="cedula" class="form-label">Cédula</label>
                                              <input type="text" class="form-control" id="cedula" name="cedula" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="telefono" class="form-label">Teléfono</label>
                                              <input type="text" class="form-control" id="telefono" name="telefono" required>
                                          </div>
                                          <div class="mb-3">
                                              <label for="cargo" class="form-label">Cargo</label>
                                              <div class="input-icon">
                                                  <select name="cargo" id="cargo" class="form-control" required>
                                                      <option value="">Seleccionar cargo</option>
                                                      <?php
                                                      $consultaCargos = "SELECT * FROM cargos";
                                                      $resultadoCargos = mysqli_query($enlace, $consultaCargos);
                                                      while ($cargoRow = mysqli_fetch_array($resultadoCargos)) {
                                                          $selected = isset($row) && $row['cargo'] == $cargoRow['id'] ? ' selected' : '';
                                                          echo '<option value="'.$cargoRow['id'].'"'.$selected.'>'.$cargoRow['nombre'].'</option>';
                                                      }
                                                      ?>
                                                  </select>
                                                  <i class="fa-solid fa-angle-down"></i>
                                              </div>
                                          </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button class="btn btn-primary" name="addTrabajadorBtn">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            
            </div>
            <div class="container-table">
                    <table id="tablaTrabajadores" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cedula</th>
                                <th>Telefono</th>
                                <th>Cargo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $consultaTrabajadores = "
                                    SELECT t.*, c.nombre AS cargo_nombre
                                    FROM trabajadores t
                                    JOIN cargos c ON t.cargo = c.id
                                ";
                                $resultado = mysqli_query($enlace, $consultaTrabajadores);
                                $contador = 1;
                                while($row = mysqli_fetch_array($resultado)) {
                                    echo '
                                            <tr>
                                                    <td>'.$contador.'</td>
                                                    <td>'.$row['nombre'].'</td>
                                                    <td>'.$row['apellido'].'</td>
                                                    <td>'.$row['cedula'].'</td>
                                                    <td>'.$row['telefono'].'</td>
                                                    <td>'.$row['cargo_nombre'].'</td>
                                                    <td>
                                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal'.$row['id'].'"><i class="fa-solid fa-pen-to-square"></i></button>
                                                            <form action="#" method="post" style="display:inline;" onsubmit="return false;" id="deleteForm'.$row['id'].'">
                                                                <input type="hidden" name="id" value="'.$row['id'].'">
                                                                <input type="hidden" name="deleteTrabajadorBtn" value="1">
                                                                <button type="button" class="btn btn-danger" onclick="if(confirm(\'¿Seguro que deseas eliminar este trabajador?\')) { document.getElementById(\'deleteForm'.$row['id'].'\').submit(); }"><i class="fa-solid fa-trash"></i></button>
                                                            </form>
                                                    </td>
                                            </tr>
                                            <!-- Modal de edición para este trabajador -->
                                            <div class="modal fade" id="editModal'.$row['id'].'" tabindex="-1" aria-labelledby="editModalLabel'.$row['id'].'" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel'.$row['id'].'">Editar Trabajador</h5>
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
                                                                    <label for="cedula'.$row['id'].'" class="form-label">Cédula</label>
                                                                    <input type="text" class="form-control" id="cedula'.$row['id'].'" name="cedula" value="'.$row['cedula'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="telefono'.$row['id'].'" class="form-label">Teléfono</label>
                                                                    <input type="text" class="form-control" id="telefono'.$row['id'].'" name="telefono" value="'.$row['telefono'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="cargo'.$row['id'].'" class="form-label">Cargo</label>
                                                                    <div class="input-icon">
                                                                        <select name="cargo" id="cargo'.$row['id'].'" class="form-control" required>
                                                                            <option value="">Seleccionar cargo</option>';
                                                                            $consultaCargos = "SELECT * FROM cargos";
                                                                            $resultadoCargos = mysqli_query($enlace, $consultaCargos);
                                                                            while ($cargoRow = mysqli_fetch_array($resultadoCargos)) {
                                                                                $selected = ($row['cargo'] == $cargoRow['id']) ? ' selected' : '';
                                                                                echo '<option value="'.$cargoRow['id'].'"'.$selected.'>'.$cargoRow['nombre'].'</option>';
                                                                            }
                                                                        ?>
                                                                        </select>
                                                                        <i class="fa-solid fa-angle-down"></i>
                                                                    </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button class="btn btn-primary" name="editTrabajadorBtn">Guardar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
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
        var table = $('#tablaTrabajadores').DataTable({
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