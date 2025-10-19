
<?php
// Datos de conexión al servidor MySQL
$servidor = "localhost";   // Servidor local (XAMPP)
$usuario = "root";         // Usuario por defecto en XAMPP
$contrasena = "1212";          // En XAMPP, normalmente no hay contraseña
$base_datos = "gymdb"; // Nombre de tu base de datos

// Crear la conexión
$conexion = mysqli_connect($servidor, $usuario, $contrasena, $base_datos);

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("❌ Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Si quieres confirmar visualmente que se conectó (solo para pruebas):
// echo "✅ Conexión exitosa";
?>