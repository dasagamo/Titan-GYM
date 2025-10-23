<?php
session_start();
include '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasena = $_POST['password'];

    $tablas = [
        'cliente' => ['id' => 'Id_Cliente', 'nombre' => 'Nombre', 'redirect' => '../cliente.php'],
        'administrador' => ['id' => 'Id_Administrador', 'nombre' => 'Nombre', 'redirect' => '../index_admin.php'],
        'entrenador' => ['id' => 'Id_Entrenador', 'nombre' => 'Nombre', 'redirect' => '../entrenador.php']
    ];

    $usuario = null;
    $tipo_usuario = null;

    foreach ($tablas as $tabla => $data) {
        $query = mysqli_query($conexion, "SELECT * FROM $tabla WHERE Correo='$correo' LIMIT 1");
        if ($query && mysqli_num_rows($query) > 0) {
            $usuario = mysqli_fetch_assoc($query);
            $tipo_usuario = $tabla;
            break;
        }
    }

    if ($usuario) {
        // Verificar contraseña (acepta hash o texto plano)
        if (password_verify($contrasena, $usuario['Contrasena']) || $usuario['Contrasena'] === $contrasena) {

            $_SESSION['nombre'] = $usuario['Nombre'];
            $_SESSION['correo'] = $usuario['Correo'];

            switch ($tipo_usuario) {
                case 'cliente':
                    $_SESSION['id_cliente'] = $usuario['Id_Cliente'];
                    $_SESSION['rol'] = 'cliente';
                    header("Location: ../cliente.php");
                    break;

                case 'administrador':
                    $_SESSION['id_admin'] = $usuario['Id_Administrador'];
                    $_SESSION['rol'] = 'admin';
                    header("Location: ../index_admin.php");
                    break;

                case 'entrenador':
                    $_SESSION['id_entrenador'] = $usuario['Id_Entrenador'];
                    $_SESSION['rol'] = 'entrenador';
                    header("Location: ../entrenador.php");
                    break;
            }
            exit;
        } else {
            $error = "⚠️ Contraseña incorrecta";
        }
    } else {
        $error = "⚠️ Usuario no encontrado";
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
