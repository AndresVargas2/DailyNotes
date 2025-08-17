<?php
require '../system/session.php';
require '../layout/header.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}
$mensaje = [];
if ($_SESSION['rol'] === 'empleado') {
    header("Location: tareasEmpleado.php");
    exit();
} elseif ($_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
$etiqueta_id = isset($_GET['etiqueta_id']) ? (int)$_GET['etiqueta_id'] : 0;

if (isset($_POST['accion'])) {
  switch ($_POST['accion']) {
    case 'agregarTarea':
      $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
      $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
      $asignado_a = mysqli_real_escape_string($conn, $_POST['asignado_a']);
      $estado = mysqli_real_escape_string($conn, $_POST['estado']);
      $prioridad = mysqli_real_escape_string($conn, $_POST['prioridad']);
      $fecha_asignacion = mysqli_real_escape_string($conn, $_POST['fecha_asignacion']);

      // 1) Insertamos en tareas
      $sql = "INSERT INTO tareas (titulo, descripcion, asignado_a, estado, prioridad, fecha_asignacion) 
              VALUES ('$titulo', '$descripcion', '$asignado_a', '$estado', '$prioridad', '$fecha_asignacion')";
      
      if (mysqli_query($conn, $sql)) {
        // 2) Obtenemos el ID de la tarea recién insertada
        $tarea_id = mysqli_insert_id($conn);

        // 3) Si venimos desde una etiqueta (por URL), la vinculamos
        if (isset($_GET['etiqueta_id']) && (int)$_GET['etiqueta_id'] > 0) {
          $etiqueta_id = (int)$_GET['etiqueta_id'];
          mysqli_query($conn, "INSERT INTO tarea_etiqueta (tarea_id, etiqueta_id) VALUES ($tarea_id, $etiqueta_id)");
        }

        $mensaje = ['mensaje' => "Tarea agregada correctamente.", 'tipo' => 'success'];
      } else {
        $mensaje = ['mensaje' => "Error al agregar la tarea: " . mysqli_error($conn), 'tipo' => 'danger'];
      }
      break;

    case 'editarTarea':
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
    case 'eliminarTarea':
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
  
    case 'agregarNota':
      $tarea_id = mysqli_real_escape_string($conn, $_POST['tarea_id']);
      $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
      $sql = "INSERT INTO notas (tarea_id, contenido) VALUES ('$tarea_id', '$contenido')";
      if (mysqli_query($conn, $sql)) {
          $mensaje = ['mensaje' => 'Nota agregada correctamente.', 'tipo' => 'success'];
      } else {
          $mensaje = ['mensaje' => 'Error al agregar nota: ' . mysqli_error($conn), 'tipo' => 'danger'];
      }
      break;

    case 'editarNota':
      $id = mysqli_real_escape_string($conn, $_POST['id']);
      $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
      $sql = "UPDATE notas SET contenido='$contenido' WHERE id='$id'";
      if (mysqli_query($conn, $sql)) {
          $mensaje = ['mensaje' => 'Nota actualizada correctamente.', 'tipo' => 'success'];
      } else {
          $mensaje = ['mensaje' => 'Error al actualizar nota: ' . mysqli_error($conn), 'tipo' => 'danger'];
      }
      break;

    case 'eliminarNota':
      $id = mysqli_real_escape_string($conn, $_POST['id']);
      $sql = "DELETE FROM notas WHERE id='$id'";
      if (mysqli_query($conn, $sql)) {
          $mensaje = ['mensaje' => 'Nota eliminada correctamente.', 'tipo' => 'success'];
      } else {
          $mensaje = ['mensaje' => 'Error al eliminar nota: ' . mysqli_error($conn), 'tipo' => 'danger'];
      }
      break;
  } 
}
?>
<div class="container mt-4">
  <h2>
    Tareas
    <?php
      if ($etiqueta_id > 0) {
        $res = mysqli_query($conn, "SELECT nombre FROM etiquetas WHERE id = $etiqueta_id");
        if ($row = mysqli_fetch_assoc($res)) {
          echo ' - ' . htmlspecialchars($row['nombre']);
        }
      }
    ?>
  </h2>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Agregar Tarea</button>
  <table id= "tablaTareas"class="display table table-striped">
    <thead>
      <tr>
        <th>Titulo</th>
        <th>Descripcion</th>
        <th>Asignado a</th>
        <th>Estado</th>
        <th>Prioridad</th>
        <th>Fecha Asignacion</th>
        <th>Etiqueta</th> 
        <th>Acciones</th>
        
      </tr>
    </thead>
    <tbody>
      



<?php if (!empty($mensaje)): ?>
  <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible fade show" role="alert">
    <strong><?= $mensaje['mensaje'] ?></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
       <?php
    if ($etiqueta_id > 0) {
    // Solo tareas de esta etiqueta
    $sql = "SELECT t.* 
            FROM tareas t
            JOIN tarea_etiqueta te ON t.id = te.tarea_id
            WHERE t.activo !=0 AND te.etiqueta_id = $etiqueta_id
            ORDER BY t.fecha_asignacion ASC";
} else {
    // Todas las tareas
    $sql = "SELECT * FROM tareas WHERE activo !=0 ORDER BY fecha_asignacion ASC";
}

$tareas = mysqli_query($conn, $sql);
foreach ($tareas as $tarea) {
  $estado_color = $tarea['activo'] == 1 ? 'success' : 'danger';
  $estado_nombre = $tarea['activo'] == 1  
    ? '<span data-feather="check-circle" class="align-text-bottom"></span>'
    : '<span data-feather="clock" class="align-text-bottom"></span>';

  // Buscar asignado
  $asignado_id = $tarea['asignado_a'] ?? 0;
  $asignado_nombre = 'No asignado';
  if ($asignado_id) {
    $res = mysqli_query($conn, "SELECT nombre_completo FROM usuario WHERE id = $asignado_id");
    if ($fila = mysqli_fetch_assoc($res)) {
      $asignado_nombre = $fila['nombre_completo'];
    }
  }

  // Buscar etiqueta
  $etiquetaNombre = "Tarea temporal";
  $resEtiqueta = mysqli_query($conn, "SELECT e.nombre FROM tarea_etiqueta te JOIN etiquetas e ON te.etiqueta_id = e.id WHERE te.tarea_id = {$tarea['id']} LIMIT 1");
  if ($rowEtiqueta = mysqli_fetch_assoc($resEtiqueta)) {
    $etiquetaNombre = htmlspecialchars($rowEtiqueta['nombre']);
  }

  // Ahora sí imprimimos
  echo "<tr id='tarea-{$tarea['id']}' data-asignado-id='{$asignado_id}' data-title='" . htmlspecialchars($tarea['titulo'], ENT_QUOTES) . "'>
    <td>" . htmlspecialchars($tarea['titulo']) . "</td>
    <td>" . htmlspecialchars($tarea['descripcion']) . "</td>
    <td>" . htmlspecialchars($asignado_nombre) . "</td>
    <td>" . htmlspecialchars($tarea['estado']) . "</td>
    <td>" . htmlspecialchars($tarea['prioridad']) . "</td>
    <td>" . $tarea['fecha_asignacion'] . "</td>
    <td>{$etiquetaNombre}</td>
    <td>
      <button onclick='editarTarea({$tarea['id']})' class='btn btn-primary'><span data-feather=\"edit\"></span></button>
      <button onclick='eliminarTarea({$tarea['id']})' class='btn btn-danger'><span data-feather=\"trash\"></span></button>
      <button onclick='verNotas({$tarea['id']})' class='btn btn-info'><span data-feather=\"file-text\"></span></button>
    </td>
  </tr>";
}
      ?>
      

    </tbody>
  </table>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- Modal para agregar Tareas-->
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
          <input type="hidden" name="accion" value="agregarTarea">
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
              //mostrar solo los usuarios activos
              $usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario WHERE estado = 1");
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
  <!-- Modal de editar Tareas -->
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
                  $usuarios = mysqli_query($conn, "SELECT id, nombre_completo FROM usuario WHERE estado =1");
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
              <button type="submit" name="accion" value="editarTarea" class="btn btn-success">Guardar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Modal para Notas-->
<div class="modal fade" id="notasModal" tabindex="-1" aria-labelledby="notasModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notasModalLabel">Notas de la tarea: <span id="tituloTareaNota"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="listaNotas"></div>
        <hr>
        <form id="formNota">
          <input type="hidden" name="accion" value="agregarNota" id="accionNota">
          <input type="hidden" name="tarea_id" id="tarea_id_nota">
          <input type="hidden" name="id" id="idNota">
          <div class="mb-3">
            <label for="contenidoNota" class="form-label">Contenido</label>
            <textarea class="form-control" id="contenidoNota" name="contenido" rows="4" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="btnGuardarNota">Guardar Nota</button>
          <button type="button" class="btn btn-secondary" onclick="limpiarFormularioNota()">Limpiar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> 
<script>
  $(document).ready(function() {
    $('#tablaTareas').DataTable({
        "order": [[0, "asc"]], // por defecto ordena por la primera columna (fecha)
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });
});
    // Funciones para manejar las acciones de las tareas
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
        accionInput.value = 'eliminarTarea';
        form.appendChild(accionInput);
        document.body.appendChild(form);
        form.submit();
      }
    }
    // Función para ver notas de una tarea
document.addEventListener('DOMContentLoaded', function(){

  // inicializa feather (si lo usas)
  if (window.feather) { try { feather.replace() } catch(e){ console.warn(e) } }

  const notasModalEl = document.getElementById('notasModal');
  if (!notasModalEl) return console.error('No existe #notasModal en la página');
  const notasModal = new bootstrap.Modal(notasModalEl);
  const listaNotas = document.getElementById('listaNotas');
  const tituloSpan = document.getElementById('tituloTareaNota');
  const tareaIdInput = document.getElementById('tarea_id_nota');
  const formNota = document.getElementById('formNota');

  // aseguramos funciones globales para que onclick inline funcione
  window.verNotas = function(tareaId){
    limpiarFormularioNota();
    tareaIdInput.value = tareaId;

    const tareaRow = document.getElementById('tarea-' + tareaId);
    // prioridad: data-title, luego primera celda, luego fallback
    const titulo = tareaRow?.dataset?.title || tareaRow?.cells?.[0]?.innerText?.trim() || 'Tarea';
    tituloSpan.innerText = titulo;

    // limpiar backdrops viejos por seguridad
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');

    notasModal.show();
    cargarNotas(tareaId);
  };

  function cargarNotas(tareaId){
    fetch('cargar_notas.php?tarea_id=' + encodeURIComponent(tareaId))
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.text();
      })
      .then(html => {
        listaNotas.innerHTML = html;
        if (window.feather) { try { feather.replace() } catch(e){} }
      })
      .catch(err => {
        console.error('Error cargar notas:', err);
        listaNotas.innerHTML = '<div class="text-danger">Error cargando notas.</div>';
      });
  }

  function limpiarFormularioNota(){
    if (!formNota) return;
    formNota.reset();
    document.getElementById('accionNota').value = 'agregarNota';
    document.getElementById('idNota').value = '';
  }

  if (formNota) {
    formNota.addEventListener('submit', function(e){
      e.preventDefault();
      const formData = new FormData(formNota);
      fetch('', { method: 'POST', body: formData })
        .then(res => {
          // opcional: comprobar status o contenido
          return res.text();
        })
        .then(() => {
          // solo recargamos la lista dentro del modal (no reabrimos)
          const tareaId = formData.get('tarea_id');
          if (tareaId) cargarNotas(tareaId);
          limpiarFormularioNota();
        })
        .catch(err => console.error('Error guardar nota:', err));
    });
  }

  // Exponer funciones para botones dentro de la lista de notas
  window.editarNota = function(id, contenido){
    document.getElementById('accionNota').value = 'editarNota';
    document.getElementById('idNota').value = id;
    document.getElementById('contenidoNota').value = contenido;
  };

  window.eliminarNota = function(id){
    if (!confirm('¿Eliminar esta nota?')) return;
    const data = new FormData();
    data.append('accion','eliminarNota');
    data.append('id', id);
    fetch('', { method: 'POST', body: data })
      .then(() => {
        const tareaId = tareaIdInput.value;
        if (tareaId) cargarNotas(tareaId);
      })
      .catch(err => console.error('Error eliminar nota:', err));
  };

  // limpiar backdrops residuales al cargar por si quedó algo
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.classList.remove('modal-open');

});
 // DOMContentLoaded
  </script>
  <?php
  require '../layout/footer.php';
  ?>