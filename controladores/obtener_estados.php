<?php
include '../modelos/conexion.php';

$sql = "SELECT estado, COUNT(*) AS cantidad FROM solicitudes GROUP BY estado";
$resultado = $conexion->query($sql);

$datos = [];

if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
}

header('Content-Type: application/json');
echo json_encode($datos);
?>
