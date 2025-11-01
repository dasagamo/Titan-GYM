<?php
session_start();
session_unset(); // Limpia cualquier sesión anterior
session_destroy();
session_start(); // Crea una nueva sesión limpia
session_start();
require_once "../Conexion.php"; // Fix capitalization to match actual file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasena = $_POST['password'];

$tablas = [
    'cliente' => ['id' => 'Id_Cliente', 'nombre' => 'Nombre', 'redirect' => '../Cliente/cliente.php'],
    'administrador' => ['id' => 'Id_Admin', 'nombre' => 'Nombre', 'redirect' => '../Admin/index_admin.php'],
    'entrenador' => ['id' => 'Id_Entrenador', 'nombre' => 'Nombre', 'redirect' => '../Entrenador/index.php']
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
                    header("Location: ../Cliente/cliente.php"); // Update path
                    break;

                case 'administrador':
                    $_SESSION['Id_Admin'] = $usuario['Id_Admin'];  // Fix session variable name
                    $_SESSION['rol'] = 'admin';
                    header("Location: ../Admin/index_admin.php"); // Fix admin path
                    break;

                case 'entrenador':
                    $_SESSION['id_entrenador'] = $usuario['Id_Entrenador'];
                    $_SESSION['rol'] = 'entrenador';
                    header("Location: ../Entrenador/index.php"); // Update path
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
    <title>Login - Titán GYM</title>
    <link rel="stylesheet" href="../Desing/login.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
</head>
<body>
    <div class="login-container">
        <h1>INICIAR SESIÓN</h1>
        <?php if(isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" 
                       value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>"
                       placeholder="tu@correo.com" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" 
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">INGRESAR</button>
        </form>
        <div class="registro-texto">
            ¿No tienes cuenta? <a href="Inscripcion.php">Regístrate aquí</a>
        </div>
        <div class="registro-texto">
            <a href="../Index.php">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>
