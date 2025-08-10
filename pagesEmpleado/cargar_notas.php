<?php
require '../system/session.php';

$tarea_id = intval($_GET['tarea_id'] ?? 0);

if ($tarea_id > 0) {
    $notas = mysqli_query($conn, "SELECT * FROM notas WHERE tarea_id = $tarea_id ORDER BY fecha_creacion DESC");
    if (mysqli_num_rows($notas) > 0) {
        echo "<ul class='list-group'>";
        while ($nota = mysqli_fetch_assoc($notas)) {
            $contenido = htmlspecialchars($nota['contenido']);
            $id = $nota['id'];
            echo "<li class='list-group-item d-flex justify-content-between align-items-start'>
                    <div>$contenido</div>
                    <div>
                      <button class='btn btn-sm btn-warning me-1' onclick='editarNota($id, `" . addslashes($contenido) . "`)'>Editar</button>
                      <button class='btn btn-sm btn-danger' onclick='eliminarNota($id)'>Eliminar</button>
                    </div>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay notas para esta tarea.</p>";
    }
} else {
    echo "<p>Tarea inválida.</p>";
}
?>
