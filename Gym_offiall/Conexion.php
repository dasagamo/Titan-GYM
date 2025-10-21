<?php
$servername = "localhost";
$username = "root";
$password = "1212";
$database = "gymdb";

// Crear conexión MySQLi
$conexion = mysqli_connect($servername, $username, $password, $database);

// Verificar conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>