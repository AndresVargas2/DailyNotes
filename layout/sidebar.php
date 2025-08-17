<?php
if (!isset($_SESSION)) {
    session_start();
}
$usuario_etiqueta = 
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
                    <?php
                    // Mostrar etiquetas asignadas al empleado
                    if (isset($_SESSION['id'])) {
                        $usuario_id = $_SESSION['id'];

                        $sql = "SELECT e.id, e.nombre 
                                FROM etiquetas e
                                INNER JOIN usuario_etiqueta ue ON ue.etiqueta_id = e.id
                                WHERE ue.usuario_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $usuario_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            echo '<div class="px-3"><strong>Etiquetas asignadas:</strong></div>';
                            echo '<ul class="nav flex-column mb-2">';
                            while ($row = $result->fetch_assoc()) {
                                echo '<li class="nav-item">';
                                echo '<a class="nav-link" href="etiqueta.php?id=' . $row['id'] . '">';
                                echo '<span data-feather="tag" class="align-text-bottom"></span> ' . htmlspecialchars($row['nombre']);
                                echo '</a></li>';
                            }
                            echo '</ul>';
                        }
                        $stmt->close();
                        $conn->close();
                    }

                    ?>
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
<?php endif; ?>
<?php if ($rol === 'empleado'): ?>
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
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                    <span>Proyectos</span>
                </h6>
                
                       
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


