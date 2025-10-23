<?php
require_once "../Conexion.php";

$res = mysqli_query($conexion, "SELECT Id_Especialidad, Nombre_Especialidad FROM especialidad");
$list=[];
while($r=mysqli_fetch_assoc($res)) $list[]=$r;
header('Content-Type: application/json');
echo json_encode($list);
