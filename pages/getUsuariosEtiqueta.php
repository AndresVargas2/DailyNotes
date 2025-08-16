<?php
require '../system/session.php';
require '../system/connection.php';

if (isset($_GET['etiqueta_id'])) {
    $etiqueta_id = intval($_GET['etiqueta_id']);

    $sql = "SELECT u.id, u.nombre 
            FROM usuario_etiqueta ue
            JOIN usuarios u ON ue.usuario_id = u.id
            WHERE ue.etiqueta_id = $etiqueta_id";
    $res = mysqli_query($conn, $sql);

    $usuarios = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $usuarios[] = $row;
    }

    echo json_encode($usuarios);
}
