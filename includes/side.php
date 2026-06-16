<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <nav>
        <ul>
            <li>
                <a href="inicio.php" class="<?= $currentPage == 'inicio.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-house icons-header"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="trabajadores.php" class="<?= $currentPage == 'trabajadores.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-briefcase icons-header"></i>
                    <span>Trabajadores</span>
                </a>
            </li>
            <li>
                <a href="asistencias.php" class="<?= $currentPage == 'asistencias.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-user-clock icons-header"></i>
                    <span>Asistencias</span>
                </a>
            </li>
            <li>
                <a href="reposo.php" class="<?= $currentPage == 'reposo.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-kit-medical icons-header"></i>
                    <span>Reposo Medico</span>
                </a>
            </li>
            <li>
                <a href="vacaciones.php" class="<?= $currentPage == 'vacaciones.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-umbrella-beach"></i>
                    <span>Vacaciones de Vigilantes</span>
                </a>
            </li>
            <li>
                <a href="salon.php" class="<?= $currentPage == 'salon.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-chalkboard-user icons-header"></i>
                    <span>Salón de Clase</span>
                </a>
            </li>
            <li>
                <a href="Inscripciones.php" class="<?= $currentPage == 'inscripciones.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-address-card icons-header"></i>
                    <span>Inscripciones</span>
                </a>
            </li>
            <li>
                <a href="notas.php" class="<?= $currentPage == 'notas.php' ? 'selected' : '' ?>">
                    <i class="fa-solid fa-rectangle-list icons-header"></i>
                    <span>Notas</span>
                </a>
            </li>
            </li>
        </ul>
    </nav>
</div>