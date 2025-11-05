<?php
// delete.php
session_start();
if (!isset($_SESSION['Id_Admin'])) { header('Location: ../forms/login.php'); exit(); }
require_once "../Conexion.php";

if (!$conexion) die("Error: No se pudo conectar a la base de datos");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo']) && isset($_POST['id'])) {
    $mod = $_POST['modulo'];
    $id = (int)$_POST['id'];

    switch($mod){
        case 'clientes':
            // eliminar acceso, membrecia vinculada y cliente
            mysqli_query($conexion, "DELETE FROM acceso WHERE Id_Cliente=$id");
            // borrar membresia si existe (cliente tiene FK Id_Membrecia)
            $m = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT Id_Membrecia FROM cliente WHERE Id_Cliente=$id"));
            if($m && $m['Id_Membrecia']) mysqli_query($conexion,"DELETE FROM membrecia WHERE Id_Membrecia={$m['Id_Membrecia']}");
            mysqli_query($conexion, "DELETE FROM cliente WHERE Id_Cliente=$id");
            $msg = "Cliente eliminado.";
            break;
        case 'entrenadores':
            mysqli_query($conexion, "DELETE FROM entrenador WHERE Id_Entrenador=$id");
            $msg = "Entrenador eliminado.";
            break;
        case 'inventario':
            mysqli_query($conexion, "DELETE FROM inventario WHERE Id_Producto=$id");
            mysqli_query($conexion, "DELETE FROM producto WHERE Id_Producto=$id");
            $msg = "Producto eliminado.";
            break;
        case 'proveedores':
            mysqli_query($conexion, "DELETE FROM proveedor WHERE Id_Proveedor=$id");
            $msg = "Proveedor eliminado.";
            break;
        case 'especialidades':
            mysqli_query($conexion, "DELETE FROM especialidad WHERE Id_Especialidad=$id");
            $msg = "Especialidad eliminada.";
            break;
        case 'membrecias':
            mysqli_query($conexion, "DELETE FROM tipo_membrecia WHERE Id_Tipo_Membrecia=$id");
            $msg = "Tipo de membresía eliminado.";
            break;
    }
    header("Location: delete.php?ok=" . urlencode($msg) . "&modulo=$mod");
    exit();
}

$mod = $_GET['modulo'] ?? 'clientes';
$ok = $_GET['ok'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar - Admin</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="header">
    <h1>Eliminar registros</h1>
    <a href="index_admin.php" class="logout">Volver</a>
  </div>
  <div class="layout">
    <aside class="sidebar">
      <a href="delete.php?modulo=clientes">Clientes</a>
      <a href="delete.php?modulo=entrenadores">Entrenadores</a>
      <a href="delete.php?modulo=inventario">Inventario</a>
      <a href="delete.php?modulo=proveedores">Proveedores</a>
      <a href="delete.php?modulo=especialidades">Especialidades</a>
      <a href="delete.php?modulo=membrecias">Tipos Membresía</a>
    </aside>
    <main class="main">
      <div class="crud-section">
        <?php if($ok): ?>
          <div style="padding:10px;background:#ffecec;border:1px solid #f5c2c2;margin-bottom:12px;"><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>
        <h2><?php echo ucfirst($mod); ?></h2>

        <?php
        // mostrar tablas similares a read.php, pero con botón eliminar
        if ($mod === 'clientes') {
            $res = mysqli_query($conexion, "SELECT Id_Cliente, Nombre, Apellido, Correo FROM cliente");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Cliente']}</td><td>{$r['Nombre']} {$r['Apellido']}</td><td>{$r['Correo']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar cliente?')\">
                          <input type='hidden' name='modulo' value='clientes'>
                          <input type='hidden' name='id' value='{$r['Id_Cliente']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }

        if ($mod === 'entrenadores') {
            $res = mysqli_query($conexion, "SELECT Id_Entrenador, Nombre, Apellido, Correo FROM entrenador");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Entrenador']}</td><td>{$r['Nombre']} {$r['Apellido']}</td><td>{$r['Correo']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar entrenador?')\">
                          <input type='hidden' name='modulo' value='entrenadores'>
                          <input type='hidden' name='id' value='{$r['Id_Entrenador']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }

        if ($mod === 'inventario') {
            $res = mysqli_query($conexion, "SELECT p.Id_Producto, p.Nombre, i.Cantidad FROM producto p JOIN inventario i ON p.Id_Producto=i.Id_Producto");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Cantidad</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Producto']}</td><td>{$r['Nombre']}</td><td>{$r['Cantidad']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar producto?')\">
                          <input type='hidden' name='modulo' value='inventario'>
                          <input type='hidden' name='id' value='{$r['Id_Producto']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }

        if ($mod === 'proveedores') {
            $res = mysqli_query($conexion, "SELECT Id_Proveedor, Nombre, Correo FROM proveedor");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Proveedor']}</td><td>{$r['Nombre']}</td><td>{$r['Correo']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar proveedor?')\">
                          <input type='hidden' name='modulo' value='proveedores'>
                          <input type='hidden' name='id' value='{$r['Id_Proveedor']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }

        if ($mod === 'especialidades') {
            $res = mysqli_query($conexion, "SELECT Id_Especialidad, Nombre_Especialidad FROM especialidad");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Especialidad']}</td><td>{$r['Nombre_Especialidad']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar especialidad?')\">
                          <input type='hidden' name='modulo' value='especialidades'>
                          <input type='hidden' name='id' value='{$r['Id_Especialidad']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }

        if ($mod === 'membrecias') {
            $res = mysqli_query($conexion, "SELECT Id_Tipo_Membrecia, Nombre_Tipo FROM tipo_membrecia");
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Eliminar</th></tr>";
            while($r=mysqli_fetch_assoc($res)){
              echo "<tr><td>{$r['Id_Tipo_Membrecia']}</td><td>{$r['Nombre_Tipo']}</td>
                    <td><form method='POST' onsubmit=\"return confirm('Eliminar tipo?')\">
                          <input type='hidden' name='modulo' value='membrecias'>
                          <input type='hidden' name='id' value='{$r['Id_Tipo_Membrecia']}'>
                          <button class='btn-delete'>Eliminar</button>
                        </form></td></tr>";
            }
            echo "</table>";
        }
        ?>
      </div>
    </main>
  </div>
</body>
</html>
