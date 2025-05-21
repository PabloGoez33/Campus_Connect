<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$ticketId = $data['ticket'] ?? null;
$responsableName = $data['responsable'] ?? null; // ahora es el nombre, no el id

if (!$ticketId || !$responsableName) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$stmt = $conexion->prepare("UPDATE solicitudes SET responsable = ?, estado = 'En proceso' WHERE ticket = ?");
$stmt->bind_param("ss", $responsableName, $ticketId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error en la base de datos"]);
}

$stmt->close();
$conexion->close();