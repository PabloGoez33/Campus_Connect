<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Verificar si se recibió una búsqueda
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["busqueda"])) {
    $busqueda = trim($_POST["busqueda"]);
    $likeBusqueda = "%" . $busqueda . "%";

    // Preparar la consulta
    $stmt = $conexion->prepare("SELECT id, user_type, request_type, fullname, id_type, id_number, email, phone, request_details, documents, ticket, created_at, estado, responsable, fecha_ultima_actualizacion, comentarios 
                                FROM solicitudes 
                                WHERE ticket LIKE ? OR id_number LIKE ?");
    $stmt->bind_param("ss", $likeBusqueda, $likeBusqueda);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $solicitudes = [];

        while ($row = $result->fetch_assoc()) {
            $solicitudes[] = $row;
        }

        if (count($solicitudes) > 0) {
            echo json_encode(['success' => true, 'results' => $solicitudes]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron solicitudes con ese dato.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}

$conexion->close();