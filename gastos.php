<?php 
    include 'bd/sesion-start.php'; 
    include 'bd/conexion.php';

    // Lógica para agregar movimiento
    if(isset($_POST['addMovimientoBtn'])) {
        $tipo = $_POST['tipo'];
        $fecha = $_POST['fecha']; // Mover fecha al inicio para usarla en el nombre del archivo
        $categoria = mysqli_real_escape_string($enlace, $_POST['categoria']);
        $descripcion = mysqli_real_escape_string($enlace, $_POST['descripcion']);
        $monto = $_POST['monto'];
        
        $recibo_imagen = null;
        if (isset($_FILES['recibo_imagen']) && $_FILES['recibo_imagen']['error'] == 0) {
            $target_dir = "uploads/recibos/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Crea el directorio si no existe
            }
            $file_extension = pathinfo($_FILES['recibo_imagen']['name'], PATHINFO_EXTENSION);
            $recibo_imagen = "recibo_" . $fecha . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $recibo_imagen;
            move_uploaded_file($_FILES['recibo_imagen']['tmp_name'], $target_file);
        }

        $query = "INSERT INTO finanzas (tipo, categoria, descripcion, monto, fecha, recibo_imagen) 
                  VALUES ('$tipo', '$categoria', '$descripcion', '$monto', '$fecha', " . ($recibo_imagen ? "'$recibo_imagen'" : "NULL") . ")";
        
        if(mysqli_query($enlace, $query)) {
            echo '<script>alert("Movimiento registrado correctamente"); window.location = "gastos.php";</script>';
        }
    }

    // Lógica para eliminar
    if(isset($_GET['delete'])) {
        $id = $_GET['delete'];
        mysqli_query($enlace, "DELETE FROM finanzas WHERE id = '$id'");
        header("Location: gastos.php");
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
    } elseif ($filtroFecha == '15dias') {
        $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 15 DAY) ";
    } elseif ($filtroFecha == 'mes') {
        $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
    } elseif ($filtroFecha == '3meses') {
        $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ";
    } elseif ($filtroFecha == 'ano') {
        $whereClause = " WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ";
    } elseif ($filtroFecha == 'personalizado' && !empty($fechaInicio) && !empty($fechaFin)) {
        $whereClause = " WHERE fecha BETWEEN '$fechaInicio' AND '$fechaFin' ";
    }

    // Totales para las tarjetas de balance
    $res_ingresos = mysqli_query($enlace, "SELECT SUM(monto) as total FROM finanzas WHERE tipo = 'ingreso'");
    $total_ingresos = mysqli_fetch_assoc($res_ingresos)['total'] ?? 0;

    $res_gastos = mysqli_query($enlace, "SELECT SUM(monto) as total FROM finanzas WHERE tipo = 'gasto'");
    $total_gastos = mysqli_fetch_assoc($res_gastos)['total'] ?? 0;

    $saldo_total = $total_ingresos - $total_gastos;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finanzas - Felipe Hernández</title>
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
    <link href="datatables/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-5.3.8-examples/assets/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/nav-side.css">
    <link rel="stylesheet" href="css/trabajadores.css">
</head>
<body>
    <header><?php include 'includes/nav.php'; ?></header>
    <?php include 'includes/side.php'; ?>
    <main id="main">
        <div class="container my-4">
            <div class="center"><h1>Control de Finanzas</h1></div>

            <!-- Tarjetas de Balance -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6>Total Ingresos</h6>
                            <h3>$ <?= number_format($total_ingresos, 2) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6>Total Gastos</h6>
                            <h3>$ <?= number_format($total_gastos, 2) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h6>Saldo Total</h6>
                            <h3>$ <?= number_format($saldo_total, 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <!-- Botones de acción -->
                <div class="mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#movimientoModal">
                        <i class="fa-solid fa-plus"></i> Nuevo Movimiento
                    </button>
                    <a href="includes/descargar-finanzas.php?filtroFecha=<?= $filtroFecha ?>&fechaInicio=<?= $fechaInicio ?>&fechaFin=<?= $fechaFin ?>" target="_blank" class="btn btn-outline-danger ms-2">
                        <i class="fa-solid fa-file-pdf"></i> Reporte General
                    </a>
                </div>

                <!-- Filtros de fecha y búsqueda -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <form action="" method="GET" class="d-flex align-items-center gap-2" id="formFiltro">
                        <div class="input-icon">
                            <select name="filtroFecha" id="filtroFecha" class="form-control" style="width: auto;">
                                <option value="todos" <?= $filtroFecha == 'todos' ? 'selected' : '' ?>>Todos los registros</option>
                                <option value="hoy" <?= $filtroFecha == 'hoy' ? 'selected' : '' ?>>Hoy</option>
                                <option value="ayer" <?= $filtroFecha == 'ayer' ? 'selected' : '' ?>>Ayer</option>
                                <option value="7dias" <?= $filtroFecha == '7dias' ? 'selected' : '' ?>>Últimos 7 días</option>
                                <option value="15dias" <?= $filtroFecha == '15dias' ? 'selected' : '' ?>>Últimos 15 días</option>
                                <option value="mes" <?= $filtroFecha == 'mes' ? 'selected' : '' ?>>Último mes</option>
                                <option value="3meses" <?= $filtroFecha == '3meses' ? 'selected' : '' ?>>Últimos 3 meses</option>
                                <option value="ano" <?= $filtroFecha == 'ano' ? 'selected' : '' ?>>Último año</option>
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
                </div>
                <div class="mb-2 mb-md-0" style="width: 250px;">
                    <input class="form-control" type="search" id="busqueda" placeholder="Buscar movimiento...">
                </div>
            </div>

            <div class="container-table mt-3">
                <table id="tablaFinanzas" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Recibo</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $queryFinanzas = "SELECT * FROM finanzas $whereClause ORDER BY fecha DESC";
                        $resultado = mysqli_query($enlace, $queryFinanzas);
                        while($row = mysqli_fetch_array($resultado)): 
                            $color = ($row['tipo'] == 'ingreso') ? 'text-success' : 'text-danger';
                        ?>
                        <tr>
                            <td><?= $row['fecha'] ?></td>
                            <td><span class="badge <?= $row['tipo'] == 'ingreso' ? 'bg-success' : 'bg-danger' ?>"><?= strtoupper($row['tipo']) ?></span></td>
                            <td><?= $row['categoria'] ?></td>
                            <td><?= $row['descripcion'] ?></td>
                            <td class="text-center">
                                <?php if (!empty($row['recibo_imagen'])): ?>
                                    <a href="uploads/recibos/<?= $row['recibo_imagen'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="Ver Recibo"><i class="fa-solid fa-image"></i></a>
                                <?php else: ?> --- <?php endif; ?>
                            </td>
                            <td class="fw-bold <?= $color ?>">$ <?= number_format($row['monto'], 2) ?></td>
                            <td>
                                <a href="includes/descargar-recibo.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-info text-white" title="Descargar Recibo"><i class="fa-solid fa-receipt"></i></a>
                                <a href="gastos.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Movimiento -->
    <div class="modal fade" id="movimientoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"> 
                <form method="POST">
                    <div class="modal-header"><h5>Registrar Movimiento</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Movimiento</label>
                            <select name="tipo" class="form-control" required>
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="gasto">Gasto (-)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <input type="text" name="categoria" class="form-control" placeholder="Ej: Artículos de limpieza, Donación, etc." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Monto ($)</label>
                                <input type="number" step="0.01" name="monto" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="recibo_imagen" class="form-label">Imagen del Recibo (Opcional)</label>
                            <input type="file" name="recibo_imagen" id="recibo_imagen" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button name="addMovimientoBtn" class="btn btn-primary">Guardar</button>
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
        var table = $('#tablaFinanzas').DataTable({
            "dom": 'rtip',
            "language": { "url": "datatables/Spanish.json" }
        });
        $('#busqueda').on('keyup', function() { table.search(this.value).draw(); });
    });

    // Lógica para mostrar/ocultar inputs de fecha personalizados
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const customInputs = document.getElementById('customDateInputs');
        if (this.value === 'personalizado') {
            customInputs.style.display = 'flex';
        } else {
            customInputs.style.display = 'none';
            this.form.submit(); // Envía el formulario automáticamente al cambiar el filtro
        }
    });
    </script>
</body>
</html>