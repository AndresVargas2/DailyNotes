<?php
if (!isset($_SESSION)) {
    session_start();
}
$rol = $_SESSION['rol'] ?? null;
?>

<?php if ($rol === 'admin'): ?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                    <span>Tareas</span>
                </h6>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="index.php"?'active':''?>" href="./">
                    <span data-feather="bar-chart" class="align-text-bottom"></span>
                    Hoy
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="tareas.php"?'active':''?>" href="tareas.php">
                    <span data-feather="check-square" class="align-text-bottom"></span>
                    Tareas
                </a>       
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="etiquetas.php"?'active':''?>" href="etiquetas.php">
                    <span data-feather="folder" class="align-text-bottom"></span>
                    Proyectos
                </a>       
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Configuraciones</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="usuarios.php"?'active':''?>" href="usuarios.php">
                    <span data-feather="user-check" class="align-text-bottom"></span>
                    Usuarios del Sistema
                </a>
                <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <span data-feather="log-out" class="align-text-bottom"></span>
                    Cerrar Sesión
                </a>
            </li>   
            </li>
        </ul>
    </div>
</nav>

<?php elseif ($rol === 'empleado'): ?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                    <span>Tareas</span>
                </h6>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="index.php"?'active':''?>" href="./">
                    <span data-feather="bar-chart" class="align-text-bottom"></span>
                    Hoy
                </a>
                            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=="tareasEmpleado.php"?'active':''?>" href="tareasEmpleado.php">
                    <span data-feather="check-square" class="align-text-bottom"></span>
                    Tareas Personales
                </a>       
            </li>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Salir del Sistema</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <span data-feather="log-out" class="align-text-bottom"></span>
                    Cerrar Sesión
                </a>
            </li>   
        </ul>
    </div>
</nav>
<?php endif; ?>


