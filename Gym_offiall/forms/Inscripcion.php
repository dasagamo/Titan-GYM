<?php
include '../conexion.php'; // conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Cifra segura de la contraseña
    $contrasenaCifrada = password_hash($contrasena, PASSWORD_DEFAULT);

    // Asignar rol de usuario normal
    $tipoUsuario = 3;

    // Verificar si ya existe el correo
    $check = mysqli_query($conexion, "SELECT * FROM cliente WHERE Correo='$correo'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('El correo ya está registrado.');</script>";
    } else {
        $sql = "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo_Usuario)
                VALUES ('$nombre', '$apellido', '$telefono', '$correo', '$contrasenaCifrada', '$tipoUsuario')";
        if (mysqli_query($conexion, $sql)) {
            echo "<script>alert('Usuario registrado exitosamente'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error al registrar el usuario.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <link rel="stylesheet" href="../Desing/Inscripcion.css">
</head>
<body>
  <div class="registro-contenedor">
    <div class="registro-card">
      <h2>Crear Cuenta</h2>
      <form method="POST">
        <div class="grupo-input">
          <input type="text" name="nombre" required>
          <label>Nombre</label>
        </div>

        <div class="grupo-input">
          <input type="text" name="apellido" required>
          <label>Apellido</label>
        </div>

        <div class="grupo-input">
          <input type="text" name="telefono" required>
          <label>Teléfono</label>
        </div>

        <div class="grupo-input">
          <input type="email" name="correo" required>
          <label>Correo electrónico</label>
        </div>

        <div class="grupo-input">
          <input type="password" name="contrasena" required>
          <label>Contraseña</label>
        </div>

        <button type="submit" class="btn-rojo">Registrarse</button>

        <p class="mensaje">¿Ya tienes una cuenta?  
          <a href="Inicio_Secion.php">Inicia sesión</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>
