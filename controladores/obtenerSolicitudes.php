<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$sql = "SELECT ticket, user_type, request_type, created_at, prioridad, estado, responsable
        FROM solicitudes
        ORDER BY created_at DESC";

$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }

    echo json_encode(['success' => true, 'results' => $solicitudes]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay solicitudes']);
}

$conexion->close();