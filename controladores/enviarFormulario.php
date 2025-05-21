<?php
header('Content-Type: application/json');
require_once '../modelos/conexion.php';

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Obtener datos de $_POST
$userType = $_POST['userType'] ?? null;
$requestType = $_POST['requestType'] ?? null;
$fullname = $_POST['fullname'] ?? null;
$idType = $_POST['idType'] ?? null;
$idNumber = $_POST['idNumber'] ?? null;
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$requestDetails = $_POST['requestDetails'] ?? '{}';
$ticket = $_POST['ticket'] ?? null;
$estado = 'Pendiente';

// Decodificar detalles de la solicitud
$requestDetailsArray = json_decode($requestDetails, true);

// Manejar archivos subidos
$uploadedFilesInfo = [];
if (!empty($_FILES['documents'])) {
    $files = $_FILES['documents'];
    $totalFiles = count($files['name']);

    $uploadDir = '../uploads/'; // Carpeta donde guardar los archivos, debe existir y tener permisos
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    for ($i = 0; $i < $totalFiles; $i++) {
        $tmpName = $files['tmp_name'][$i];
        $name = basename($files['name'][$i]);
        $targetFile = $uploadDir . time() . '_' . $name; // evitar sobreescritura con timestamp

        if (move_uploaded_file($tmpName, $targetFile)) {
            $uploadedFilesInfo[] = [
                'originalName' => $name,
                'storedPath' => $targetFile,
                'size' => $files['size'][$i],
                'type' => $files['type'][$i]
            ];
        }
    }
}

// Prioridad según tipo de usuario
$prioridades = ['Alta', 'Media', 'Baja'];
$prioridad = $prioridades[array_rand($prioridades)];

// Guardar info en la base de datos
$documentsJson = json_encode($uploadedFilesInfo);

$stmt = $conexion->prepare("INSERT INTO solicitudes 
    (user_type, request_type, fullname, id_type, id_number, email, phone, request_details, documents, ticket, estado,responsable, fecha_ultima_actualizacion, comentarios, prioridad) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

$responsable = "Administrador";
$comentarios = "Sin comentarios";
$stmt->bind_param("ssssssssssssss", $userType, $requestType, $fullname, $idType, $idNumber, $email, $phone, $requestDetails, $documentsJson, $ticket, $estado, $responsable, $comentarios, $prioridad);

if ($stmt->execute()) {
    // Envío de correo en texto plano
    $subject = "Confirmación de solicitud – Campus Connect";

    $message = "Estimado/a $fullname,\n\n";
    $message .= "Hemos recibido tu solicitud correctamente con el número de ticket: $ticket.\n\n";
    $message .= "Resumen de tu solicitud:\n";
    $message .= "- Tipo de usuario: $userType\n";
    $message .= "- Tipo de solicitud: $requestType\n";
    $message .= "- Identificación: $idType $idNumber\n";
    $message .= "- Correo: $email\n";
    $message .= "- Teléfono: $phone\n";
    $message .= "- Prioridad: $prioridad\n\n";
    $message .= "Uno de nuestros agentes revisará tu solicitud y te contactará si es necesario.\n\n";
    $message .= "Atentamente,\nEquipo de soporte – Campus Connect";

    $headers = "From: Campus Connect <no-reply@campusconnect.edu>\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Solicitud guardada y correo enviado correctamente', 'ticket' => $ticket]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Solicitud guardada, pero hubo un error al enviar el correo', 'ticket' => $ticket]);
    }
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conexion->close();
