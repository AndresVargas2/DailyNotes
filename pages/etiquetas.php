<?php
require '../system/session.php';
require '../layout/header.php';

// Crear etiqueta
if (isset($_POST['crear'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    mysqli_query($conn, "INSERT INTO etiquetas (nombre) VALUES ('$nombre')");
    $etiqueta_id = mysqli_insert_id($conn);

    if (!empty($_POST['usuarios'])) {
        foreach ($_POST['usuarios'] as $usuario_id) {
            mysqli_query($conn, "INSERT INTO usuario_etiqueta (etiqueta_id, usuario_id) VALUES ($etiqueta_id, $usuario_id)");
        }
    }
    header("Location: etiquetas.php");
    exit();
}

// Editar etiqueta
if (isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    mysqli_query($conn, "UPDATE etiquetas SET nombre='$nombre' WHERE id=$id");

    if (!empty($_POST['usuarios'])) {
        // Insertar solo usuarios nuevos sin eliminar los existentes
        $res = mysqli_query($conn, "SELECT usuario_id FROM usuario_etiqueta WHERE etiqueta_id=$id");
        $actuales = [];
        while ($r = mysqli_fetch_assoc($res)) $actuales[] = $r['usuario_id'];

        foreach ($_POST['usuarios'] as $usuario_id) {
            if (!in_array($usuario_id, $actuales)) {
                mysqli_query($conn, "INSERT INTO usuario_etiqueta (etiqueta_id, usuario_id) VALUES ($id, $usuario_id)");
            }
        }
    }

    header("Location: etiquetas.php");
    exit();
}

// Eliminar etiqueta
if (isset($_POST['eliminar_etiqueta'])) {
    $id = intval($_POST['id']);
    mysqli_query($conn, "DELETE FROM tarea_etiqueta WHERE etiqueta_id = $id");
    mysqli_query($conn, "DELETE FROM usuario_etiqueta WHERE etiqueta_id = $id");
    mysqli_query($conn, "DELETE FROM etiquetas WHERE id = $id");
    header("Location: etiquetas.php");
    exit();
}

// Eliminar usuario asignado a etiqueta
if (isset($_POST['eliminar_usuario'])) {
    $usuario_id = intval($_POST['eliminar_usuario']);
    $etiqueta_id = intval($_POST['etiqueta_id']);
    mysqli_query($conn, "DELETE FROM usuario_etiqueta WHERE usuario_id=$usuario_id AND etiqueta_id=$etiqueta_id");
    header("Location: etiquetas.php");
    exit();
}

// Obtener etiquetas y usuarios
$etiquetas = mysqli_query($conn, "SELECT * FROM etiquetas");
$usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario WHERE estado=1");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gestión de Proyectos</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#crearModal">Nuevo Proyecto</button>
    </div>

    <div class="row">
        <?php while ($et = mysqli_fetch_assoc($etiquetas)) : ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($et['nombre']) ?></h5>
                        <p class="card-text">
                            <?php
                            $q = mysqli_query($conn, "SELECT COUNT(*) as total 
                                                       FROM tarea_etiqueta te
                                                       JOIN tareas t ON te.tarea_id = t.id
                                                       WHERE te.etiqueta_id={$et['id']} AND t.activo=1");
                            $c = mysqli_fetch_assoc($q);
                            echo $c['total'] . " tareas activas";
                            ?>
                        </p>
                        <a href="tareas.php?etiqueta_id=<?= $et['id'] ?>" class="btn btn-primary btn-sm">Ver Tareas</a>
                        <button class="btn btn-warning btn-sm editarBtn" 
                                data-id="<?= $et['id'] ?>" 
                                data-nombre="<?= htmlspecialchars($et['nombre']) ?>">Editar</button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar etiqueta?')">
                            <input type="hidden" name="id" value="<?= $et['id'] ?>">
                            <button type="submit" name="eliminar_etiqueta" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="etiquetas.php">
        <div class="modal-header">
          <h5 class="modal-title" id="crearModalLabel">Nuevo Proyecto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="usuarios" class="form-label">Asignar Personal</label>
            <select class="form-select" name="usuarios[]" multiple>
              <?php
              mysqli_data_seek($usuarios,0);
              while ($u = mysqli_fetch_assoc($usuarios)) {
                  echo "<option value='{$u['id']}'>{$u['nombre_completo']}</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="crear" class="btn btn-success">Crear</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="etiquetas.php">
        <div class="modal-header">
          <h5 class="modal-title" id="editarModalLabel">Editar Proyecto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editarId">
          <div class="mb-3">
            <label for="editarNombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="editarNombre" name="nombre" required>
          </div>

          <h6>Personal asignados:</h6>
          <ul class="list-group mb-3" id="usuariosAsignadosList"></ul>

          <div class="mb-3">
            <label for="editarUsuarios" class="form-label">Asignar Personal</label>
            <select class="form-select" id="editarUsuarios" name="usuarios[]" multiple>
              <?php
              mysqli_data_seek($usuarios,0);
              while ($u = mysqli_fetch_assoc($usuarios)) {
                  echo "<option value='{$u['id']}'>{$u['nombre_completo']}</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Abrir modal de editar con datos y usuarios asignados
document.querySelectorAll('.editarBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nombre = this.dataset.nombre;
        document.getElementById('editarId').value = id;
        document.getElementById('editarNombre').value = nombre;

        const list = document.getElementById('usuariosAsignadosList');
        list.innerHTML = '';

        <?php
        $etiquetas = mysqli_query($conn, "SELECT * FROM etiquetas");
        while ($et_row = mysqli_fetch_assoc($etiquetas)) {
            $usuarios_asignados = mysqli_query($conn, "SELECT u.id, u.nombre_completo FROM usuario_etiqueta ue JOIN usuario u ON ue.usuario_id=u.id WHERE ue.etiqueta_id=".$et_row['id']);
            echo "if(id == {$et_row['id']}) {";
            while ($u = mysqli_fetch_assoc($usuarios_asignados)) {
                echo "list.innerHTML += `<li class='list-group-item d-flex justify-content-between align-items-center'>{$u['nombre_completo']}
                    <form method='POST' style='margin:0'>
                        <input type='hidden' name='eliminar_usuario' value='{$u['id']}'>
                        <input type='hidden' name='etiqueta_id' value='{$et_row['id']}'>
                        <button type='submit' class='btn btn-sm btn-danger eliminarUsuarioBtn'>❌</button>
                    </form>
                </li>`;";
            }
            echo "}";
        }
        ?>
        new bootstrap.Modal(document.getElementById('editarModal')).show();
    });
});

// Listener global para confirmar eliminar usuario
document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('eliminarUsuarioBtn')){
        if(!confirm('¿Eliminar este usuario de la etiqueta?')){
            e.preventDefault();
        }
    }
});
</script>

<?php require '../layout/footer.php'; ?>
