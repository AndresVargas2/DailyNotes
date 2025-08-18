<?php
require '../system/session.php';
require '../layout/header.php';

// Zona horaria local
date_default_timezone_set('America/Costa_Rica');
$usuario_id = $_SESSION['usuario_id'];

// 1. Buscar tareas próximas a su fecha de asignación (1 minuto antes para pruebas)
$sql = "SELECT id, titulo, fecha_asignacion 
        FROM tareas 
        WHERE asignado_a = ? 
          AND activo = 1 
          AND estado != 'completado' 
          AND TIMESTAMPDIFF(MINUTE, NOW(), fecha_asignacion) BETWEEN 0 AND 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id); // Solo se pasa $usuario_id
$stmt->execute();
$tareas = $stmt->get_result();

// 2. Crear notificaciones automáticas si no existen
while ($tarea = $tareas->fetch_assoc()) {
    $mensaje = "Tarea próxima: " . $tarea['titulo'] . " (fecha: " . $tarea['fecha_asignacion'] . ")";

    // Revisar si ya existe la notificación para evitar duplicados
    $check = $conn->prepare("SELECT id FROM notificaciones WHERE beneficiario = ? AND mensaje = ?");
    $check->bind_param('is', $usuario_id, $mensaje);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO notificaciones (mensaje, beneficiario) VALUES (?, ?)");
        $insert->bind_param('si', $mensaje, $usuario_id);
        $insert->execute();
    }
}

// 3. Mostrar todas las notificaciones del usuario
$sql_notif = "SELECT mensaje, fecha FROM notificaciones WHERE beneficiario = ? ORDER BY fecha DESC";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('i', $usuario_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();

echo '<div class="notificaciones">';
echo '<h2>Recordatorios</h2>';

if ($result_notif->num_rows == 0) {
    echo '<p>No tienes recordatorios pendientes.</p>';
} else {
    while ($row = $result_notif->fetch_assoc()) {
        echo '<div class="notificacion">';
        echo '<strong>' . htmlspecialchars($row['mensaje']) . '</strong><br>';
        echo '<em>' . htmlspecialchars($row['fecha']) . '</em>';
        echo '</div><hr>';
    }
}

echo '</div>';

require '../layout/footer.php';
?>
