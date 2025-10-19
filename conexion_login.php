<?php
include 'conexion.php'; // conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['password']; // 👈 coincide con el 'name' del input

    // Buscar el usuario por correo
    $consulta = mysqli_query($conexion, "SELECT * FROM cliente WHERE Correo='$correo'");

    if (mysqli_num_rows($consulta) > 0) {
        $usuario = mysqli_fetch_assoc($consulta);

        // Verificar contraseña cifrada
        if (password_verify($contrasena, $usuario['Contrasena'])) {

            // Determinar vista según el tipo de usuario
            if (strpos($correo, '@entrenador') !== false) {
                header("Location: vistas/vista_entrenador.php");
            } elseif (strpos($correo, '@admin') !== false) {
                header("Location: vistas/vista_admin.php");
            } else {
                header("Location: cliente.php");
            }
            exit;
        } else {
            echo "<script>alert('❌ Contraseña incorrecta'); window.location='forms/login.php';</script>";
        }
    } else {
        echo "<script>alert('⚠ Correo no registrado'); window.location='forms/login.php';</script>";
    }
}
?>