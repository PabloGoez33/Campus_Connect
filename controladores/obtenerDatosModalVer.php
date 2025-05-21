<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if (!isset($_GET['ticket'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ticket']);
    exit;
}

$ticket = $_GET['ticket'];

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$sql = "SELECT user_type, request_type, created_at, prioridad, estado, responsable, fullname, request_details, documents FROM solicitudes WHERE ticket = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $ticket);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No se encontró la solicitud']);
    exit;
}

$row = $result->fetch_assoc();

// Si el campo documents está guardado como JSON, decodificarlo
$documents = json_decode($row['documents'], true) ?: [];

$response = [
    'success' => true,
    'userType' => $row['user_type'],
    'requestType' => $row['request_type'],
    'date' => $row['created_at'],
    'priority' => $row['prioridad'],
    'status' => $row['estado'],
    'assignedUnit' => $row['responsable'],
    'requester' => $row['fullname'],
    'details' => $row['request_details'],
    'documents' => $documents,
];

echo json_encode($response);