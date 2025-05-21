<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$sql = "SELECT ticket, user_type, request_type, created_at, prioridad, estado, responsable FROM solicitudes WHERE 1=1";
$tiposValidos = ['student', 'professor', 'external', 'contractor', 'applicant', 'graduate'];
$estadosValidos = ['En proceso', 'Completado', 'Pendiente', 'Rechazado'];
$prioridadesValidas = ['alta', 'media', 'baja'];
$parametros = [];
$tipos = [];

// Si es POST, aplicar filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    // Filtro estado
    if (!empty($input['status']) && $input['status'] !== 'all') {
        $sql .= " AND estado = ?";
        $parametros[] = mapearEstado($input['status']); // Se convierte a nombre real
        $tipos[] = 's';
    }

    // Filtro tipo usuario
    if (!empty($input['type']) && $input['type'] !== 'all') {
        $sql .= " AND user_type = ?";
        $parametros[] = ucfirst($input['type']); // Asegura la mayúscula
        $tipos[] = 's';
    }

    // Filtro prioridad
    if (!empty($input['priority']) && $input['priority'] !== 'all') {
        $sql .= " AND prioridad = ?";
        $parametros[] = ucfirst($input['priority']);
        $tipos[] = 's';
    }

    // Filtro fecha
    if (!empty($input['date']) && $input['date'] !== 'all') {
        $fechaActual = date('Y-m-d');
        switch ($input['date']) {
            case 'today':
                $sql .= " AND DATE(created_at) = ?";
                $parametros[] = $fechaActual;
                $tipos[] = 's';
                break;
            case 'week':
                $sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)";
                break;
            case 'month':
                $sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                break;
        }
    }
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conexion->prepare($sql);

if (!empty($parametros)) {
    $stmt->bind_param(implode('', $tipos), ...$parametros);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
    echo json_encode(['success' => true, 'results' => $solicitudes]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay solicitudes']);
}

$stmt->close();
$conexion->close();

// Mapear clave de estado JS a texto de la base de datos
function mapearEstado($estado) {
    switch ($estado) {
        case 'new': return 'En proceso';
        case 'assigned': return 'Completado';
        case 'in-progress': return 'Pendiente';
        case 'closed': return 'Rechazado';
        default: return $estado;
    }
}
