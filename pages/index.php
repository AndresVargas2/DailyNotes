<?php
require '../system/session.php';
require '../layout/header.php';

// Consultar últimas 5 tareas del usuario (pendientes)
$sql = "SELECT titulo, fecha_asignacion, estado 
        FROM tareas 
        WHERE asignado_a = ? 
          AND activo = 1 
          AND estado != 'completado'
        ORDER BY fecha_asignacion ASC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h1 class="mb-4">Bienvenido a DailyNotes</h1>

    <h3>Tus próximas tareas</h3>
    <div class="list-group">
        <?php if ($result->num_rows == 0): ?>
            <p class="text-muted">No tienes tareas pendientes.</p>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong><?= htmlspecialchars($row['titulo']) ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= htmlspecialchars($row['fecha_asignacion']) ?>
                        </small>
                    </span>
                    <span class="badge bg-info text-dark">
                        <?= ucfirst(htmlspecialchars($row['estado'])) ?>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <div class="mt-3">
        <a href="tareas.php" class="btn btn-primary">Ver todas tus tareas</a>
    </div>
</div>

<?php require '../layout/footer.php'; ?>
