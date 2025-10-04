<?php
// Datos de conexión
$servidor   = 'localhost';
$usuario    = 'root';
$contrasena = '1212';   // tu contraseña real
$baseDatos  = 'gymdb';  // nombre real de la BD

// Crear conexión
$conn = new mysqli($servidor, $usuario, $contrasena, $baseDatos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
