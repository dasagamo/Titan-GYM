<?php
session_start();
include '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    
    // Validar longitud de contraseña
    if (strlen($contrasena) < 5) {
        $error = "La contraseña debe tener al menos 5 caracteres";
    } else {
        // Verificar si el correo ya existe
        $verificar = mysqli_query($conexion, "SELECT * FROM cliente WHERE Correo='$correo'");
        if (mysqli_num_rows($verificar) > 0) {
            $error = "Este correo ya está registrado";
        } else {
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            // Insertar como cliente (tipo 3)
            $stmt = mysqli_prepare($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo) VALUES (?, ?, ?, ?, ?, 3)");
            mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $telefono, $correo, $contrasena_hash);
            
            if(mysqli_stmt_execute($stmt)) {
                $exito = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar: " . mysqli_error($conexion);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripción - Titan GYM</title>
    <link rel="stylesheet" href="../Desing/login.css">
</head>
<body>
    <div class="login-container">
        <h1>REGISTRARSE</h1>
        <?php if(isset($exito)): ?>
            <div class="success-message"><?= $exito ?></div>
            <div class="registro-texto">
                <a href="login.php">Iniciar Sesión</a>
            </div>
        <?php elseif(isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return validarContrasena()">
            <div class="input-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
            </div>
            <div class="input-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" required>
            </div>
            <div class="input-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="1234567890" required>
            </div>
            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="tu@correo.com" required>
            </div>
            <div class="input-group">
                <label for="contrasena">Contraseña <small></small></label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required minlength="5">
                <small class="caracteres-info" id="caracteresInfo"></small>
            </div>
            <button type="submit" class="btn-login">REGISTRARSE</button>
        </form>
        <div class="registro-texto">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
        <div class="registro-texto">
            <a href="../index_cliente.php">← Volver al inicio</a>
        </div>
    </div>

    <script>
    function validarContrasena() {
        const contrasena = document.getElementById('contrasena').value;
        if (contrasena.length < 5) {
            alert('La contraseña debe tener al menos 5 caracteres');
            return false;
        }
        return true;
    }

    // Validación en tiempo real
    document.getElementById('contrasena').addEventListener('input', function() {
        const caracteresInfo = document.getElementById('caracteresInfo');
        if (this.value.length < 5) {
            caracteresInfo.style.color = '#e74c3c';
            caracteresInfo.textContent = `Mínimo 5 caracteres (actual: ${this.value.length})`;
        } else {
            caracteresInfo.style.color = '#27ae60';
            caracteresInfo.textContent = '✓ Contraseña válida';
        }
    });
    </script>

</body>
</html>