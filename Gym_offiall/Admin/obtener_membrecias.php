<?php
require_once "../Conexion.php";

$res = mysqli_query($conexion, "SELECT Id_Tipo_Membrecia, Nombre_Tipo, Precio, Duracion FROM tipo_membrecia");
$list=[];
while($r=mysqli_fetch_assoc($res)) $list[]=$r;
header('Content-Type: application/json');
echo json_encode($list);
