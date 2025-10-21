<?php
session_start();
include '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['password'];

    // Buscar en todas las tablas de usuarios
    $tablas = ['cliente', 'administrador', 'entrenador'];
    $usuario = null;
    $tipo_usuario = null;

    foreach ($tablas as $tabla) {
        $consulta = mysqli_query($conexion, "SELECT * FROM $tabla WHERE Correo='$correo'");
        
        if (mysqli_num_rows($consulta) > 0) {
            $usuario = mysqli_fetch_assoc($consulta);
            $tipo_usuario = $tabla;
            break;
        }
    }

    if ($usuario && password_verify($contrasena, $usuario['Contrasena'])) {
        // Guardar datos de sesión según el tipo de usuario
        switch($tipo_usuario) {
            case 'cliente':
                $_SESSION['id_cliente'] = $usuario['Id_Cliente'];
                $_SESSION['nombre'] = $usuario['Nombre'];
                $_SESSION['tipo_usuario'] = 'cliente';
                header("Location: ../cliente.php");
                break;
                
            case 'administrador':
                $_SESSION['id_administrador'] = $usuario['Id_Administrador'];
                $_SESSION['nombre'] = $usuario['Nombre'];
                $_SESSION['tipo_usuario'] = 'administrador';
                header("Location: ../Admin.php");
                break;
                
            case 'entrenador':
                $_SESSION['id_entrenador'] = $usuario['Id_Entrenador'];
                $_SESSION['nombre'] = $usuario['Nombre'];
                $_SESSION['tipo_usuario'] = 'entrenador';
                header("Location: ../entrenador.php");
                break;
        }
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Titan GYM</title>
    <link rel="stylesheet" href="../Desing/login.css">
</head>
<body>
    <div class="login-container">
        <h1>INICIAR SESIÓN</h1>
        <?php if(isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="tu@correo.com" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">INGRESAR</button>
        </form>
        <div class="registro-texto">
            ¿No tienes cuenta? <a href="Inscripcion.php">Regístrate aquí</a>
        </div>
        <div class="registro-texto">
            <a href="../index_cliente.php">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>