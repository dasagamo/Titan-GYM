<?php
include '../conexion.php'; // conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Buscar el usuario por correo
    $consulta = mysqli_query($conexion, "SELECT * FROM cliente WHERE Correo='$correo'");
    if (mysqli_num_rows($consulta) > 0) {
        $usuario = mysqli_fetch_assoc($consulta);

        // Verificar contraseña cifrada
        if (password_verify($contrasena, $usuario['Contrasena'])) {
            // Determinar vista según el tipo de usuario
            if (strpos($correo, '@entrenador') !== false) {
                header("Location: ../vista_entrenador.php");
            } elseif (strpos($correo, '@admin') !== false) {
                header("Location: ../vista_admin.php");
            } else {
                header("Location: ../vista_cliente.php");
            }
            exit;
        } else {
            echo "<script>alert('Contraseña incorrecta');</script>";
        }
    } else {
        echo "<script>alert('Correo no registrado');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../Desing/Inicio_Secion.css">
</head>
<body>

    <div class="login-container">
        <h1>Iniciar Sesión</h1>

        <form action="../conexion_login.php" method="POST">
            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
            </div>

            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
            </div>

            <button type="submit" class="btn-login">Ingresar</button>

            <p class="registro-texto">¿No tienes cuenta? 
                <a href="registro.php">Regístrate aquí</a>
            </p>
        </form>
    </div>

</body>
</html>
