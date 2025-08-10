<?php
require '../system/session.php';
require '../layout/header.php';
?>
<HTML:5>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Notes Hoy</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/scripts.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php require '../layout/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Hoy</h1>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Tarea</th>
                                <th>Eiqueta</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se mostrarán las tareas del día -->
                            <?php
                            // Código para obtener y mostrar las tareas del día
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <?php require '../layout/footer.php'; ?>
</HTML:5>