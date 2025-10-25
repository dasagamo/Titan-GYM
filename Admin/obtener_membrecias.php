<?php
// obtener_membrecias.php
session_start();
if (!isset($_SESSION['id_administrador'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once "../Conexion.php";

if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si la tabla existe
$check_table = mysqli_query($conexion, "SHOW TABLES LIKE 'tipo_membrecia'");
if (mysqli_num_rows($check_table) == 0) {
    http_response_code(500);
    echo json_encode(['error' => 'La tabla tipo_membrecia no existe']);
    exit();
}

try {
    $query = "SELECT Id_Tipo_Membrecia, Nombre_Tipo, Precio FROM tipo_membrecia ORDER BY Nombre_Tipo";
    $result = mysqli_query($conexion, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conexion));
    }
    
    $membrecias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $membrecias[] = [
            'Id_Tipo_Membrecia' => (int)$row['Id_Tipo_Membrecia'],
            'Nombre_Tipo' => $row['Nombre_Tipo'],
            'Precio' => (float)$row['Precio']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($membrecias);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>