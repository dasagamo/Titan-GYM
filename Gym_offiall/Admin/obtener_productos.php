<?php
session_start();
if (!isset($_SESSION['Id_Admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once "../Conexion.php";

if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

$result = mysqli_query($conexion, "SELECT Id_Producto, Nombre FROM producto");
$list = [];
while($row = mysqli_fetch_assoc($result)) $list[] = $row;

header('Content-Type: application/json');
echo json_encode($list);
?>