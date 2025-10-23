<?php
session_start();
if (!isset($_SESSION['id_administrador'])) {
  header('Location: forms/login.php');
  exit();
}
require_once "../Conexion.php";
if (!$conexion) die("Error: No se pudo conectar a la base de datos");

$mod = $_GET['modulo'] ?? 'clientes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listar / Gestionar</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="header">
    <h1>📋 Listar / Gestionar</h1>
    <a href="index_admin.php" class="logout"><i class="fa-solid fa-arrow-left"></i> Volver</a>
  </div>

  <div class="layout">
    <aside class="sidebar">
      <a href="read.php?modulo=clientes"><i class="fa-solid fa-users"></i> Clientes</a>
      <a href="read.php?modulo=entrenadores"><i class="fa-solid fa-user-tie"></i> Entrenadores</a>
      <a href="read.php?modulo=inventario"><i class="fa-solid fa-boxes-stacked"></i> Inventario</a>
      <a href="read.php?modulo=proveedores"><i class="fa-solid fa-truck"></i> Proveedores</a>
      <a href="read.php?modulo=especialidades"><i class="fa-solid fa-book"></i> Especialidades</a>
      <a href="read.php?modulo=membrecias"><i class="fa-solid fa-id-card"></i> Tipos Membresía</a>
    </aside>

    <main class="main">
      <div class="crud-section">
        <h2>Listado: <?php echo ucfirst($mod); ?></h2>

        <?php
        if ($mod === 'clientes') {
          $res = mysqli_query($conexion, "
            SELECT c.Id_Cliente, c.Nombre, c.Apellido, c.Telefono, c.Correo,
                   tm.Nombre_Tipo, a.Codigo
            FROM cliente c
            LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia
            LEFT JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
            LEFT JOIN acceso a ON c.Id_Cliente = a.Id_Cliente
            ORDER BY c.Id_Cliente ASC
          ");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Membresía</th><th>QR</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            $qrPath = "../qrcodes/{$r['Id_Cliente']}.png";
            $qr = file_exists($qrPath)
              ? "<img src='$qrPath' width='70'>"
              : "<span class='no-qr'>Sin QR</span>";

            echo "<tr>
                    <td>{$r['Id_Cliente']}</td>
                    <td>{$r['Nombre']} {$r['Apellido']}</td>
                    <td>{$r['Telefono']}</td>
                    <td>{$r['Correo']}</td>
                    <td>" . ($r['Nombre_Tipo'] ?? 'Sin membresía') . "</td>
                    <td>$qr</td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'entrenadores') {
          $res = mysqli_query($conexion, "
            SELECT e.Id_Entrenador, e.Nombre, e.Apellido, e.Telefono, e.Correo, esp.Nombre_Especialidad
            FROM entrenador e
            LEFT JOIN especialidad esp ON e.Id_Especialidad = esp.Id_Especialidad
          ");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Tel</th><th>Correo</th><th>Especialidad</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$r['Id_Entrenador']}</td>
                    <td>{$r['Nombre']} {$r['Apellido']}</td>
                    <td>{$r['Telefono']}</td>
                    <td>{$r['Correo']}</td>
                    <td>" . ($r['Nombre_Especialidad'] ?? 'Sin especialidad') . "</td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'inventario') {
          $res = mysqli_query($conexion, "
            SELECT p.Id_Producto, p.Nombre, p.Marca, i.Cantidad, i.Ubicacion
            FROM producto p
            JOIN inventario i ON p.Id_Producto = i.Id_Producto
          ");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Cantidad</th><th>Ubicación</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$r['Id_Producto']}</td>
                    <td>{$r['Nombre']}</td>
                    <td>{$r['Marca']}</td>
                    <td>{$r['Cantidad']}</td>
                    <td>{$r['Ubicacion']}</td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'proveedores') {
          $res = mysqli_query($conexion, "
            SELECT pr.Id_Proveedor, pr.Nombre, pr.Telefono, pr.Correo, pr.Precio_Proveedor, p.Nombre AS Producto
            FROM proveedor pr
            JOIN producto p ON pr.Id_Producto = p.Id_Producto
          ");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Tel</th><th>Correo</th><th>Producto</th><th>Precio</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$r['Id_Proveedor']}</td>
                    <td>{$r['Nombre']}</td>
                    <td>{$r['Telefono']}</td>
                    <td>{$r['Correo']}</td>
                    <td>{$r['Producto']}</td>
                    <td>\${$r['Precio_Proveedor']}</td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'especialidades') {
          $res = mysqli_query($conexion, "SELECT * FROM especialidad");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Descripción</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$r['Id_Especialidad']}</td>
                    <td>{$r['Nombre_Especialidad']}</td>
                    <td>" . ($r['Descripcion'] ?? '') . "</td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'membrecias') {
          $res = mysqli_query($conexion, "
            SELECT tm.Id_Tipo_Membrecia, tm.Nombre_Tipo, tm.Precio, 
                   COALESCE(MAX(m.Duracion), 30) AS Duracion
            FROM tipo_membrecia tm
            LEFT JOIN membrecia m ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
            GROUP BY tm.Id_Tipo_Membrecia
          ");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Duración (días)</th></tr>";
          while ($r = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$r['Id_Tipo_Membrecia']}</td>
                    <td>{$r['Nombre_Tipo']}</td>
                    <td>\${$r['Precio']}</td>
                    <td>{$r['Duracion']}</td>
                  </tr>";
          }
          echo "</table>";
        }
        ?>
      </div>
    </main>
  </div>
</body>
</html>
