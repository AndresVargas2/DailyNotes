<?php
require '../system/session.php';
require '../layout/header.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<?php

if ($_SESSION['rol'] === 'empleado') {
    $empleado_id = $_SESSION['usuario_id'];
    $query = mysqli_query($conn, "SELECT nombre_completo FROM usuario WHERE id = $empleado_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
      $empleado_nombre = htmlspecialchars($row['nombre_completo']);
    }
    echo "<h2>Tareas asignadas para: $empleado_nombre</h2>";
    // Aquí podrías agregar lógica para mostrar las tareas asignadas al empleado
    // Por ejemplo, podrías hacer una consulta a la base de datos para obtener las tareas asignadas
    $tareas = mysqli_query($conn, "SELECT * FROM tareas WHERE asignado_a = $empleado_id AND activo != 0 AND estado  != 'completado' ORDER BY fecha_asignacion ASC");
    if (mysqli_num_rows($tareas) == 0) {
        echo "<p>No tienes tareas asignadas actualmente.</p>";
    } else {
        // Aquí podrías mostrar las tareas en una tabla o lista
        echo "<table id='tablaTareas' class='display table table-striped'>
        <thead>
            <tr>
                <th>Título</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Fecha de Asignación</th>
                <th>Proyecto</th> 
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>";
while ($tarea = mysqli_fetch_assoc($tareas)) {
  $etiquetaNombre = "Tarea temporal";
  $resEtiqueta = mysqli_query($conn, "SELECT e.nombre FROM tarea_etiqueta te JOIN etiquetas e ON te.etiqueta_id = e.id WHERE te.tarea_id = {$tarea['id']} LIMIT 1");
  if ($rowEtiqueta = mysqli_fetch_assoc($resEtiqueta)) {
    $etiquetaNombre = htmlspecialchars($rowEtiqueta['nombre']);
  }
    echo "<tr>
            <td>" . htmlspecialchars($tarea['titulo']) . "</td>
            <td>" . htmlspecialchars($tarea['descripcion']) . "</td>
            <td>" . htmlspecialchars($tarea['estado']) . "</td>
            <td>" . htmlspecialchars($tarea['prioridad']) . "</td>
            <td>" . $tarea['fecha_asignacion'] . "</td>
            <td>{$etiquetaNombre}</td>
            <td>
                <button onclick='verNotas({$tarea['id']})' class='btn btn-info'><span data-feather=\"file-text\" class=\"align-text-bottom\"></span></button>
                <button onclick='TareaCompletada({$tarea['id']})' class='btn btn-success' title='Marcar como completada'><span data-feather=\"check-circle\" class=\"align-text-bottom\"></span></button>
            </td>
        </tr>";
} 
echo "</tbody></table>";
    }
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
      case 'completarTarea':
        $tarea_id = mysqli_real_escape_string($conn, $_POST['tarea_id']);
        $sql = "UPDATE tareas SET estado='completado' WHERE id='$tarea_id'";
        if (mysqli_query($conn, $sql)) {
            $mensaje = ['mensaje' => 'Tarea marcada como completada.', 'tipo' => 'success'];
        } else {
            $mensaje = ['mensaje' => 'Error al completar tarea: ' . mysqli_error($conn), 'tipo' => 'danger'];
        }
        break;
}
}
?>

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
        "order": [
            [0, "asc"]
        ], // por defecto ordena por la primera columna (fecha)
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });
});
// Función para ver notas de una tarea
document.addEventListener('DOMContentLoaded', function() {

    // inicializa feather (si lo usas)
    if (window.feather) {
        try {
            feather.replace()
        } catch (e) {
            console.warn(e)
        }
    }

    const notasModalEl = document.getElementById('notasModal');
    if (!notasModalEl) return console.error('No existe #notasModal en la página');
    const notasModal = new bootstrap.Modal(notasModalEl);
    const listaNotas = document.getElementById('listaNotas');
    const tituloSpan = document.getElementById('tituloTareaNota');
    const tareaIdInput = document.getElementById('tarea_id_nota');
    const formNota = document.getElementById('formNota');

    // aseguramos funciones globales para que onclick inline funcione
    window.verNotas = function(tareaId) {
        limpiarFormularioNota();
        tareaIdInput.value = tareaId;

        const tareaRow = document.getElementById('tarea-' + tareaId);
        // prioridad: data-title, luego primera celda, luego fallback
        const titulo = tareaRow?.dataset?.title || tareaRow?.cells?. [0]?.innerText?.trim() || 'Tarea';
        tituloSpan.innerText = titulo;

        // limpiar backdrops viejos por seguridad
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');

        notasModal.show();
        cargarNotas(tareaId);
    };

    function cargarNotas(tareaId) {
        fetch('cargar_notas.php?tarea_id=' + encodeURIComponent(tareaId))
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => {
                listaNotas.innerHTML = html;
                if (window.feather) {
                    try {
                        feather.replace()
                    } catch (e) {}
                }
            })
            .catch(err => {
                console.error('Error cargar notas:', err);
                listaNotas.innerHTML = '<div class="text-danger">Error cargando notas.</div>';
            });
    }

    function limpiarFormularioNota() {
        if (!formNota) return;
        formNota.reset();
        document.getElementById('accionNota').value = 'agregarNota';
        document.getElementById('idNota').value = '';
    }

    if (formNota) {
        formNota.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formNota);
            fetch('', {
                    method: 'POST',
                    body: formData
                })
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
    window.editarNota = function(id, contenido) {
        document.getElementById('accionNota').value = 'editarNota';
        document.getElementById('idNota').value = id;
        document.getElementById('contenidoNota').value = contenido;
    };

    window.eliminarNota = function(id) {
        if (!confirm('¿Eliminar esta nota?')) return;
        const data = new FormData();
        data.append('accion', 'eliminarNota');
        data.append('id', id);
        fetch('', {
                method: 'POST',
                body: data
            })
            .then(() => {
                const tareaId = tareaIdInput.value;
                if (tareaId) cargarNotas(tareaId);
            })
            .catch(err => console.error('Error eliminar nota:', err));
    };

    // limpiar backdrops residuales al cargar por si quedó algo
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');


    window.TareaCompletada = function(tareaId) {
        if (!confirm('¿Marcar esta tarea como completada?')) return;
        const data = new FormData();
        data.append('accion', 'completarTarea');
        data.append('tarea_id', tareaId);
        fetch('', {
                method: 'POST',
                body: data
            })
            .then(() => {
                location.reload();
            })
            .catch(err => console.error('Error al completar tarea:', err));
    };



}); // DOMContentLoaded
</script>
<?php
  require '../layout/footer.php';
  ?>