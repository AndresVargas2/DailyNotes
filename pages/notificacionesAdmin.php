<?php
require '../system/session.php';
require '../layout/header.php';
?>
<style>
.notificaciones { max-width: 500px; margin: 20px auto; }
.notificacion { background: #f7f7f7; padding: 10px; border-radius: 6px; margin-bottom: 10px; }
</style>
<?php


// Obtener ID del usuario en sesión
$usuario_id = $_SESSION['usuario_id'];

// Consulta segura
$sql = "SELECT mensaje, fecha, beneficiario, fue_leido 
        FROM notificaciones 
        WHERE beneficiario = ?
        ORDER BY fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id); // "i" = integer
$stmt->execute();
$result = $stmt->get_result();

echo '<div class="notificaciones">';
echo '<h2>Mis Notificaciones</h2>';

if ($result->num_rows === 0) {
    echo '<p>No tienes notificaciones pendientes.</p>';
} else {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="notificacion">';
        echo '<strong>Mensaje:</strong> ' . htmlspecialchars($row['mensaje']) . '<br>';
        echo '<em> Fecha:</em> ' . htmlspecialchars($row['fecha']) . '<br>';
        echo '</div>';
    }
}

echo '</div>';

$stmt->close();
$conn->close();

require '../layout/footer.php';
?>