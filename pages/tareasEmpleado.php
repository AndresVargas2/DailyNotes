<?php
require '../system/session.php';
require '../layout/header.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['rol'] === 'admin') {
  
    echo "<h1>Bienvenido Admin</h1>";
} else {
    echo "<h1>Bienvenido Empleado</h1>";
}
$mensaje = [];
if (isset($_POST['accion'])) {
  switch ($_POST['accion']) {
  
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



<?php if (!empty($mensaje)): ?>
  <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible fade show" role="alert">
    <strong><?= $mensaje['mensaje'] ?></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
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

        echo "<tr id='tarea-{$tarea['id']}' data-asignado-id='{$asignado_id}'data-title='" . htmlspecialchars($tarea['titulo'], ENT_QUOTES) . "'>
        <td>" . htmlspecialchars($tarea['titulo']) . "</span></td>
        <td>" . htmlspecialchars($tarea['descripcion']) . "</td>
        <td>" . htmlspecialchars($asignado_nombre) . "</td>
        <td>" . htmlspecialchars($tarea['estado']) . "</td>
        <td>" . htmlspecialchars($tarea['prioridad']) . "</td>
        <td>" . $tarea['fecha_asignacion'] . "</td>
        <td>
          <button onclick='editarTarea({$tarea['id']})' class='btn btn-primary'><span data-feather=\"edit\" class=\"align-text-bottom\"></span></button>
          <button onclick='eliminarTarea({$tarea['id']})' class='btn btn-danger'><span data-feather=\"trash\" class=\"align-text-bottom\"></span></button>
          <button onclick='verNotas({$tarea['id']})' class='btn btn-info'><span data-feather=\"file-text\" class=\"align-text-bottom\"></span></button>

        </td>
      </tr>";

      }
      ?>
    </tbody>
  </table>
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


  <script>
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

}); // DOMContentLoaded
  </script>
  <?php
  require '../layout/footer.php';
  ?>