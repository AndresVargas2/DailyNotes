<?php
require '../system/session.php';
require '../layout/header.php';

$mensaje = [];
if (isset($_POST['accion'])) {
  switch ($_POST['accion']) {
    case 'agregar':
      $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
      $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
      $asignado_a = mysqli_real_escape_string($conn, $_POST['asignado_a']);
      $estado = mysqli_real_escape_string($conn, $_POST['estado']);
      $prioridad = mysqli_real_escape_string($conn, $_POST['prioridad']);
      $fecha_asignacion = mysqli_real_escape_string($conn, $_POST['fecha_asignacion']);
      $sql = "INSERT INTO tareas (titulo, descripcion, asignado_a, estado, prioridad, fecha_asignacion) 
              VALUES ('$titulo', '$descripcion', '$asignado_a', '$estado', '$prioridad', '$fecha_asignacion')";
      if (mysqli_query($conn, $sql)) {
        $mensaje = ['mensaje' => "Tarea agregada correctamente.", 'tipo' => 'success'];
      } else {
        $mensaje = ['mensaje' => "Error al agregar la tarea: " . mysqli_error($conn), 'tipo' => 'danger'];
      }
      break;

    case 'editar':
      $id_tarea = mysqli_real_escape_string($conn, $_POST['id']);
      if (is_numeric($id_tarea) && $id_tarea > 0 && $id_tarea == (int)$id_tarea) {
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
        $asignado_a = mysqli_real_escape_string($conn, $_POST['asignado_a']);
        $estado = mysqli_real_escape_string($conn, $_POST['estado']);
        $prioridad = mysqli_real_escape_string($conn, $_POST['prioridad']);
        $fecha_asignacion = mysqli_real_escape_string($conn, $_POST['fecha_asignacion']);
        $sql = "UPDATE tareas 
                SET titulo='$titulo', descripcion='$descripcion', asignado_a='$asignado_a', 
                    estado='$estado', prioridad='$prioridad', fecha_asignacion = '$fecha_asignacion' 
                WHERE id='$id_tarea'";
        if (mysqli_query($conn, $sql)) {
          $mensaje = ['mensaje' => "Tarea actualizada correctamente.", 'tipo' => 'success'];
        } else {
          $mensaje = ['mensaje' => "Error al actualizar la tarea: " . mysqli_error($conn), 'tipo' => 'danger'];
        }
      } else {
        $mensaje = ['mensaje' => "ERROR.", 'tipo' => 'danger'];
      }
      break;
//buenas
    case 'eliminar':
      $id_tarea = mysqli_real_escape_string($conn, $_POST['id']);
      if (is_numeric($id_tarea) && $id_tarea > 0 && $id_tarea == (int)$id_tarea) {
        $sql = "UPDATE tareas SET activo = 0 WHERE id='$id_tarea'";
        if (mysqli_query($conn, $sql)) {
          $mensaje = ['mensaje' => "Tarea eliminada correctamente.", 'tipo' => 'success'];
        } else {
          $mensaje = ['mensaje' => "Error al eliminar la tarea: " . mysqli_error($conn), 'tipo' => 'danger'];
        }
      } else {
        $mensaje = ['mensaje' => "ERROR.", 'tipo' => 'danger'];
      }
      break;
  }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Tareas</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
      Agregar Tareas
    </button>
  </div>
</div>

<?php if (!empty($mensaje)): ?>
  <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible fade show" role="alert">
    <strong><?= $mensaje['mensaje'] ?></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="table-responsive">
  <table class="table table-striped table-sm">
    <thead>
      <tr>
        <th>Titulo</th>
        <th>Descripcion</th>
        <th>Asignado a</th>
        <th>Estado</th>
        <th>Prioridad</th>
        <th>Fecha Limite</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>

       <?php
      $tareas = mysqli_query($conn, "SELECT * FROM tareas WHERE activo !=0 ORDER BY fecha_asignacion ASC");
      foreach ($tareas as $tarea) {
        $estado_color = $tarea['activo'] == 1 ? 'success' : 'danger';
        $estado_nombre = $tarea['activo'] == 1  ? '<span data-feather="check-circle" class="align-text-bottom"></span>'
          : '<span data-feather="clock" class="align-text-bottom"></span>';
        $asignado_id = $tarea['asignado_a'] ?? 0;
        $asignado_nombre = 'No asignado';
        if ($asignado_id) {
        $res = mysqli_query($conn, "SELECT nombre_completo FROM usuario WHERE id = $asignado_id");
        if ($fila = mysqli_fetch_assoc($res)) {
          $asignado_nombre = $fila['nombre_completo'];
        }
        }

        echo "<tr id='tarea-{$tarea['id']}' data-asignado-id='{$asignado_id}'>
        <td>" . htmlspecialchars($tarea['titulo']) . "</td>
        <td>" . htmlspecialchars($tarea['descripcion']) . "</td>
        <td>" . htmlspecialchars($asignado_nombre) . "</td>
        <td>" . htmlspecialchars($tarea['estado']) . "</td>
        <td>" . htmlspecialchars($tarea['prioridad']) . "</td>
        <td>" . $tarea['fecha_asignacion'] . "</td>
        <td>
          <button onclick='editarTarea({$tarea['id']})' class='btn btn-primary'><span data-feather=\"edit\" class=\"align-text-bottom\"></span></button>
          <button onclick='eliminarTarea({$tarea['id']})' class='btn btn-danger'><span data-feather=\"trash\" class=\"align-text-bottom\"></span></button>
        </td>
      </tr>";

      }
      ?>
    </tbody>
  </table>
</div>




<!-- Modal para agregar-->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="add
ModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Agregar Tarea</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="accion" value="agregar">
          <div class="mb-3">
            <label for="titulo" class="form-label">Titulo</label>
            <input type="text" class="form-control" id="titulo" name="titulo" required>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripcion</label>
            <textarea class="form-control" id="descripcion" name="descripcion"></textarea>
          </div>
          <div class="mb-3">
            <label for="asignado_a" class="form-label">Asignado a</label>
            <select class="form-select" id="asignado_a" name="asignado_a">
              <?php
              $usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario");
              while ($usuario = mysqli_fetch_assoc($usuarios)) {
                echo "<option value='{$usuario['id']}'>{$usuario['nombre_completo']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado">
              <option value="Pendiente">Pendiente</option>
              <option value="En_progreso">En progreso</option>
              <option value="Completado">Completado</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="prioridad" class="form-label">Prioridad</label>
            <select class="form-select" id="prioridad" name="prioridad">
              <option value="Baja">Baja</option>
              <option value="Media">Media</option>
              <option value="Alta">Alta</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="fecha_asignacion" class="form-label">Fecha Limite</label>
            <input type="datetime-local" class="form-control" id="fecha_asignacion" name="fecha_asignacion" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
  <!-- Modal de editar -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="editModalLabel">Editar Tarea #<span id="spanNumTarea"></span></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" action="">  
          <input type="hidden" name="id" id="id">
          <div class="modal-body">
            <div class="mb-3">
              <label for="tituloEdit" class="form-label">Titulo</label>
              <input type="text" class="form-control" id="tituloEdit" name="titulo" required placeholder="Titulo de la tarea">
            </div>
            <div class="mb-3">
              <label for="descripcionEdit" class="form-label">Descripcion</label>
              <input type="text" class="form-control" id="descripcionEdit" name="descripcion" required placeholder="Descripcion de la tarea">
            </div>
            <div class="mb-3">
              <label for="asignadoAEdit" class="form-label">Asignado A</label>
                <select class="form-select" id="asignadoAEdit" name="asignado_a">
                  <?php
                  $usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario");
                  while ($usuario = mysqli_fetch_assoc($usuarios)) {
                    echo "<option value='{$usuario['id']}'>{$usuario['nombre_completo']}</option>";
                  }
                  ?>  
              </Select>
            </div>
            <div class="mb-3">
              <label for="estadoEdit" class="form-label">Estado</label>
              <Select class="form-select" id="estadoEdit" name="estado">
                <option value="pendiente">Pendiente</option>
                <option value="en_progreso">En progreso</option>
                <option value="completado">Completado</option>
              </Select>
            </div>

            <div class="mb-3">
              <label for="prioridadEdit" class="form-label">Prioridad</label>
              <Select class="form-select" id="prioridadEdit" name="prioridad" required placeholder="Prioridad de la tarea">
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
              </Select>
            </div>
            <div class="mb-3">
              </select>
            </div>
            <div class="mb-3">
              <label for="fechaAsignacionEdit" class="form-label">Fecha limite</label>
              <input type="datetime-local" class="form-control" id="fechaAsignacionEdit" name="fecha_asignacion" required placeholder="Fecha limite de la tarea">
            </div>
            <div class="text-center">
              <button type="submit" name="accion" value="editar" class="btn btn-success">Guardar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>
  function editarTarea(id_tarea) {
  const tareaRow = document.getElementById('tarea-' + id_tarea);
  if (tareaRow) {
    const titulo = tareaRow.cells[0].innerText;
    const descripcion = tareaRow.cells[1].innerText;
    const asignadoId = tareaRow.getAttribute('data-asignado-id') || '';
    const estado = tareaRow.cells[3].innerText.trim();
    const prioridad = tareaRow.cells[4].innerText;
    const fechaAsignacion = tareaRow.cells[5].innerText;

    document.getElementById('id').value = id_tarea;
    document.getElementById('tituloEdit').value = titulo;
    document.getElementById('descripcionEdit').value = descripcion;
    document.getElementById('asignadoAEdit').value = asignadoId;
    document.getElementById('estadoEdit').value = estado;
    document.getElementById('prioridadEdit').value = prioridad;
    document.getElementById('fechaAsignacionEdit').value = fechaAsignacion.replace(' ', 'T');
    document.getElementById('spanNumTarea').innerText = id_tarea;

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
  } else {
    alert("Tarea no encontrada.");
  }
}


    function eliminarTarea(id_tarea) {
      if (confirm("¿Estás seguro de eliminar la tarea #" + id_tarea + "?")) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = id_tarea;
        form.appendChild(input);
        const accionInput = document.createElement('input');
        accionInput.type = 'hidden';
        accionInput.name = 'accion';
        accionInput.value = 'eliminar';
        form.appendChild(accionInput);
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
  <?php
  require '../layout/footer.php';