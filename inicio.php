<?php
include 'bd/conexion.php';
include 'bd/sesion-start.php';

// 1. Contar estudiantes inscritos
$query_estudiantes = mysqli_query($enlace, "SELECT COUNT(*) as total FROM inscripciones");
$data_estudiantes = mysqli_fetch_assoc($query_estudiantes);
$total_estudiantes = $data_estudiantes['total'] ?? 0;

// 2. Finanzas: Ingresos y Egresos
$query_ingresos = mysqli_query($enlace, "SELECT SUM(monto) as total FROM finanzas WHERE tipo = 'ingreso'");
$data_ingresos = mysqli_fetch_assoc($query_ingresos);
$total_ingresos = $data_ingresos['total'] ?? 0.00;

$query_egresos = mysqli_query($enlace, "SELECT SUM(monto) as total FROM finanzas WHERE tipo = 'gasto'");
$data_egresos = mysqli_fetch_assoc($query_egresos);
$total_egresos = $data_egresos['total'] ?? 0.00;


// === NUEVA LÓGICA PARA LOS 3 COLORES DEL PERSONAL ===

// A. ASISTENCIAS: Conteo total de asistencias en la tabla
$query_asist = mysqli_query($enlace, "SELECT COUNT(*) as total FROM asistencias");
$data_asist = mysqli_fetch_assoc($query_asist);
$total_asistencias = $data_asist['total'] ?? 0;

// B. REPOSOS: Conteo total de reposos médicos registrados
$query_reposos = mysqli_query($enlace, "SELECT COUNT(*) as total FROM reposo_medico");
$data_reposos = mysqli_fetch_assoc($query_reposos);
$total_reposos = $data_reposos['total'] ?? 0;

// C. INASISTENCIAS (Estimación): Total de registros esperados menos los que asistieron o tienen reposo
$query_trabajadores = mysqli_query($enlace, "SELECT COUNT(*) as total FROM trabajadores");
$data_trabajadores = mysqli_fetch_assoc($query_trabajadores);
$total_trabajadores = $data_trabajadores['total'] ?? 0;

// Multiplicamos por un factor o usamos el histórico. Si no hay suficientes datos para restar, 
// podemos poner un valor base o usar una consulta de faltas. Aquí un cálculo estimado:
$total_inasistencias = max(0, ($total_trabajadores * 2) - ($total_asistencias + $total_reposos)); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Felipe Hernández</title>
    <link rel="stylesheet" href="css/nav-side.css">
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Estilos en armonía con interfaces administrativas estándar. 
           Usa las variables de color heredadas de tu archivo nav-side.css */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 5px solid var(--primary-color, #3498db); /* Usa tu color principal si está definido */
        }

        .card.finance-in { border-left-color: #2ecc71; }
        .card.finance-out { border-left-color: #e74c3c; }
        .card.students { border-left-color: #9b59b6; }

        .card-info h3 {
            margin: 0;
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .card-info p {
            margin: 5px 0 0 0;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .card-icon {
            font-size: 32px;
            color: #bdc3c7;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .chart-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            /* Eliminamos el min-height de aquí para que lo controle el wrapper */
            display: flex;
            flex-direction: column;
        }

        /* Este wrapper es el secreto para congelar el tamaño del Canvas */
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 280px; /* Define aquí la altura fija exacta que deseas para tus gráficas */
            margin: auto;
        }

        .chart-box h2 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #34495e;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <header><?php include 'includes/nav.php'; ?></header> <?php include 'includes/side.php'; ?>
    <main id="main">
        <h1>Panel de Control</h1>
        <p>Resumen analítico del estado actual de la institución educativa.</p>

        <div class="dashboard-grid">
            <div class="card students">
                <div class="card-info">
                    <h3>Estudiantes Inscritos</h3>
                    <p><?php echo $total_estudiantes; ?></p>
                </div>
                <div class="card-icon"><i class="fas fa-graduation-cap"></i></div>
            </div>

            <div class="card finance-in">
                <div class="card-info">
                    <h3>Total Ingresos</h3>
                    <p><?php echo number_format($total_ingresos, 2, ',', '.'); ?> Bs.</p>
                </div>
                <div class="card-icon"><i class="fas fa-wallet"></i></div>
            </div>

            <div class="card finance-out">
                <div class="card-info">
                    <h3>Total Egresos</h3>
                    <p><?php echo number_format($total_egresos, 2, ',', '.'); ?> Bs.</p>
                </div>
                <div class="card-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-box">
                <h2>Control de Asistencia e Incidencias del Personal</h2>
                <div class="chart-wrapper">
                    <canvas id="chartPersonal"></canvas>
                </div>
            </div>

            <div class="chart-box">
                <h2>Balance General de Fondos</h2>
                <div class="chart-wrapper">
                    <canvas id="chartFinanzas"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script src="js/nav-side.js"></script>

    <script>
        // GRÁFICO DEL PERSONAL (3 COLORES)
        const ctxPersonal = document.getElementById('chartPersonal').getContext('2d');
        new Chart(ctxPersonal, {
            type: 'bar', // Puedes cambiarlo a 'doughnut' si prefieres una gráfica de dona
            data: {
                labels: ['Asistencias', 'Reposos Médicos', 'Inasistencias'],
                datasets: [{
                    label: 'Registros Totales',
                    data: [
                        <?php echo $total_asistencias; ?>, 
                        <?php echo $total_reposos; ?>, 
                        <?php echo $total_inasistencias; ?>
                    ],
                    backgroundColor: [
                        '#2ecc71', // Verde para Asistencias
                        '#f1c40f', // Amarillo para Reposos
                        '#e74c3c'  // Rojo para Inasistencias
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false // Oculta la leyenda superior porque cada barra tiene su nombre abajo
                    }
                }
            }
        });

        // GRÁFICO DE FINANZAS (Se mantiene igual)
        const ctxFinanzas = document.getElementById('chartFinanzas').getContext('2d');
        new Chart(ctxFinanzas, {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    label: 'Monto en Bs.',
                    data: [<?php echo $total_ingresos; ?>, <?php echo $total_egresos; ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>