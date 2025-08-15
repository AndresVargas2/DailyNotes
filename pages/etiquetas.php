<?php
require '../system/session.php';
require '../layout/header.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$mensaje = [];

// Manejar acciones POST
if (isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case 'agregarEtiqueta':
            $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
            $sql = "INSERT INTO etiquetas (nombre) VALUES ('$nombre')";
            $mensaje = mysqli_query($conn, $sql)
                ? ['mensaje' => 'Etiqueta creada correctamente', 'tipo' => 'success']
                : ['mensaje' => 'Error: '.mysqli_error($conn), 'tipo' => 'danger'];
            break;

        case 'editarEtiqueta':
            $id = (int)$_POST['id'];
            $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
            $sql = "UPDATE etiquetas SET nombre='$nombre' WHERE id='$id'";
            $mensaje = mysqli_query($conn, $sql)
                ? ['mensaje' => 'Etiqueta actualizada', 'tipo' => 'success']
                : ['mensaje' => 'Error: '.mysqli_error($conn), 'tipo' => 'danger'];
            break;

        case 'eliminarEtiqueta':
            $id = (int)$_POST['id'];
            $sql = "DELETE FROM etiquetas WHERE id='$id'";
            $mensaje = mysqli_query($conn, $sql)
                ? ['mensaje' => 'Etiqueta eliminada', 'tipo' => 'success']
                : ['mensaje' => 'Error: '.mysqli_error($conn), 'tipo' => 'danger'];
            break;

        case 'asignarUsuario':
            $usuario_id = (int)$_POST['usuario_id'];
            $etiqueta_id = (int)$_POST['etiqueta_id'];
            $sql = "INSERT IGNORE INTO usuario_etiqueta (usuario_id, etiqueta_id) VALUES ($usuario_id, $etiqueta_id)";
            $mensaje = mysqli_query($conn, $sql)
                ? ['mensaje' => 'Usuario asignado a etiqueta', 'tipo' => 'success']
                : ['mensaje' => 'Error: '.mysqli_error($conn), 'tipo' => 'danger'];
            break;
    }
}

// Cargar etiquetas y usuarios
$etiquetas = mysqli_query($conn, "SELECT * FROM etiquetas ORDER BY nombre ASC");
$usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario ORDER BY nombre_completo ASC");
?>

<div class="container mt-4">
    <h2>Gestión de Proyectos</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $mensaje['tipo'] ?>"><?= $mensaje['mensaje'] ?></div>
    <?php endif; ?>

    <!-- Botón crear etiqueta -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEtiquetaModal">Agregar Proyecto</button>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuarios Asignados</th>
                <th>Tareas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($et = mysqli_fetch_assoc($etiquetas)):
            // Usuarios asignados
            $res = mysqli_query($conn, "SELECT u.nombre_completo FROM usuario u
                                        JOIN usuario_etiqueta ue ON u.id = ue.usuario_id
                                        WHERE ue.etiqueta_id = {$et['id']}");
            $asignados = [];
            while ($u = mysqli_fetch_assoc($res)) $asignados[] = $u['nombre_completo'];

            // Contar tareas
            $resT = mysqli_query($conn, "SELECT COUNT(*) as total FROM tarea_etiqueta WHERE etiqueta_id={$et['id']}");
            $totalTareas = mysqli_fetch_assoc($resT)['total'];
        ?>
            <tr>
                <td><?= htmlspecialchars($et['nombre']) ?></td>
                <td><?= implode(', ', $asignados) ?></td>
                <td><?= $totalTareas ?></td>
                <td>
                    <button class="btn btn-primary" onclick="editarEtiqueta(<?= $et['id'] ?>,'<?= htmlspecialchars($et['nombre'], ENT_QUOTES) ?>')">Editar</button>
                    <form style="display:inline" method="post">
                        <input type="hidden" name="accion" value="eliminarEtiqueta">
                        <input type="hidden" name="id" value="<?= $et['id'] ?>">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                    <button class="btn btn-info" onclick="mostrarAsignarUsuario(<?= $et['id'] ?>)">Asignar Usuario</button>
                    <a href="tareas.php?etiqueta_id=<?= $et['id'] ?>" class="btn btn-success">Ver Tareas</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Agregar Etiqueta -->
<div class="modal fade" id="addEtiquetaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="accion" value="agregarEtiqueta">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre del Proyecto</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Etiqueta -->
<div class="modal fade" id="editEtiquetaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="accion" value="editarEtiqueta">
                <input type="hidden" name="id" id="editEtiquetaId">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Etiqueta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="editEtiquetaNombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Asignar Usuario -->
<div class="modal fade" id="asignarUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="accion" value="asignarUsuario">
                <input type="hidden" name="etiqueta_id" id="asignarEtiquetaId">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Usuario</label>
                        <select name="usuario_id" class="form-select" required>
                            <?php mysqli_data_seek($usuarios,0); // reset ?>
                            <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre_completo']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarEtiqueta(id,nombre){
    document.getElementById('editEtiquetaId').value = id;
    document.getElementById('editEtiquetaNombre').value = nombre;
    new bootstrap.Modal(document.getElementById('editEtiquetaModal')).show();
}

function mostrarAsignarUsuario(id){
    document.getElementById('asignarEtiquetaId').value = id;
    new bootstrap.Modal(document.getElementById('asignarUsuarioModal')).show();
}
</script>

<?php require '../layout/footer.php'; ?>
