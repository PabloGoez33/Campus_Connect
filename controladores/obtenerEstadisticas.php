<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// 1. Total de solicitudes
$sqlTotal = "SELECT COUNT(*) as total FROM solicitudes";
$resultTotal = $conexion->query($sqlTotal);
$total = $resultTotal ? $resultTotal->fetch_assoc()['total'] : 0;

// 2. Fecha del último día con solicitudes
$sqlUltimaFecha = "SELECT MAX(DATE(created_at)) as ultima_fecha FROM solicitudes";
$resultFecha = $conexion->query($sqlUltimaFecha);
$ultima_fecha = $resultFecha ? $resultFecha->fetch_assoc()['ultima_fecha'] : null;

// 3. Cantidad de solicitudes enviadas el último día
$cantidadUltimoDia = 0;
if ($ultima_fecha) {
    $sqlUltimoDia = "SELECT COUNT(*) as total FROM solicitudes WHERE DATE(created_at) = '$ultima_fecha'";
    $resultUltimoDia = $conexion->query($sqlUltimoDia);
    $cantidadUltimoDia = $resultUltimoDia ? $resultUltimoDia->fetch_assoc()['total'] : 0;
}

// 4. Cantidad de solicitudes con estado 'completad0'
$sqlCompletadas = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Completado'";
$resultCompletadas = $conexion->query($sqlCompletadas);
$completadas = $resultCompletadas ? $resultCompletadas->fetch_assoc()['total'] : 0;

// 5. Cantidad de solicitudes con estado 'pendiente'
$sqlPendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Pendiente'";
$resultPendientes = $conexion->query($sqlPendientes);
$pendientes = $resultPendientes ? $resultPendientes->fetch_assoc()['total'] : 0;

// Enviar respuesta JSON con todas las estadísticas
echo json_encode([
    'success' => true,
    'stats' => [
        'total_solicitudes' => (int)$total,
        'solicitudes_ultimo_dia' => (int)$cantidadUltimoDia,
        'solicitudes_completadas' => (int)$completadas,
        'solicitudes_pendientes' => (int)$pendientes
    ]
]);

$conexion->close();