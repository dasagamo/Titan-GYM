<?php
// funciones.php
session_start();
if (!isset($_SESSION['id_administrador'])) { header('Location: forms/login.php'); exit(); }
require_once "../Conexion.php";
if (!$conexion) die("Error: No se pudo conectar a la base de datos");

// función generar QR
function generarQR($codigo, $id_cliente) {
    $rutaQR = '../qrcodes/'; // Carpeta fuera de Admin
    if (!file_exists($rutaQR)) mkdir($rutaQR, 0777, true);
    include '../librerias/phpqrcode/qrlib.php';
    QRcode::png($codigo, $rutaQR . $id_cliente . '.png', 'L', 8, 2);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_qr'])) {
    $id_cliente = (int)$_POST['id_cliente'];
    $accion = $_POST['accion_qr'];

    if ($accion === 'generar') {
        $codigo = 'GYM' . uniqid() . rand(1000,9999);
        mysqli_query($conexion, "DELETE FROM acceso WHERE Id_Cliente=$id_cliente");
        mysqli_query($conexion, "INSERT INTO acceso (Id_Cliente, Estado_Acceso, Codigo, Fecha_Generado) VALUES ($id_cliente,'activo','$codigo',NOW())");
        generarQR($codigo, $id_cliente);
        $msg = "QR generado.";
    } elseif ($accion === 'inhabilitar') {
        mysqli_query($conexion, "UPDATE acceso SET Estado_Acceso='inactivo' WHERE Id_Cliente=$id_cliente");
        $msg = "QR inhabilitado.";
    } elseif ($accion === 'borrar') {
        mysqli_query($conexion, "DELETE FROM acceso WHERE Id_Cliente=$id_cliente");
        if (file_exists("../qrcodes/$id_cliente.png")) unlink("../qrcodes/$id_cliente.png");
        $msg = "QR eliminado.";
    }
    header("Location: funciones.php?ok=" . urlencode($msg));
    exit();
}

$ok = $_GET['ok'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Funciones - Admin</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="header">
    <h1>Funciones (QR y acciones)</h1>
    <a href="index_admin.php" class="logout">Volver</a>
  </div>
  <div class="layout">
    <aside class="sidebar">
      <a href="funciones.php">QR / Acciones</a>
      <a href="index_admin.php">Volver al inicio</a>
    </aside>
    <main class="main">
      <div class="crud-section">
        <?php if($ok): ?>
          <div style="padding:10px;background:#e6ffed;border:1px solid #b6f2c0;margin-bottom:12px;"><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>

        <h2>Generar / Inhabilitar / Eliminar QR por cliente</h2>
        <p>Al generar un QR se elimina el anterior y se crea un nuevo registro en <code>acceso</code>.</p>

        <?php
        $res = mysqli_query($conexion, "SELECT c.Id_Cliente, c.Nombre, c.Apellido, a.Codigo, a.Estado_Acceso FROM cliente c LEFT JOIN acceso a ON c.Id_Cliente=a.Id_Cliente");
        echo "<table><tr><th>ID</th><th>Nombre</th><th>QR actual</th><th>Estado</th><th>Acciones</th></tr>";
        while($r=mysqli_fetch_assoc($res)){
          // nota la ruta ../ para mostrar correctamente el QR
          $qrRuta = "../qrcodes/{$r['Id_Cliente']}.png";
          $qr = file_exists($qrRuta) ? "<img src='$qrRuta' width='70' alt='QR'>" : "<span style='color:#777'>Sin QR</span>";
          echo "<tr>
                  <td>{$r['Id_Cliente']}</td>
                  <td>{$r['Nombre']} {$r['Apellido']}</td>
                  <td>$qr</td>
                  <td>".($r['Estado_Acceso'] ?? 'N/A')."</td>
                  <td>
                    <form method='POST' style='display:inline-block;margin-right:6px;'>
                      <input type='hidden' name='id_cliente' value='{$r['Id_Cliente']}'>
                      <button name='accion_qr' value='generar' class='btn-edit'>Generar</button>
                    </form>
                    <form method='POST' style='display:inline-block;margin-right:6px;'>
                      <input type='hidden' name='id_cliente' value='{$r['Id_Cliente']}'>
                      <button name='accion_qr' value='inhabilitar' class='btn-save'>Inhabilitar</button>
                    </form>
                    <form method='POST' style='display:inline-block;'>
                      <input type='hidden' name='id_cliente' value='{$r['Id_Cliente']}'>
                      <button name='accion_qr' value='borrar' class='btn-delete' onclick=\"return confirm('Eliminar QR?')\">Eliminar QR</button>
                    </form>
                  </td>
                </tr>";
        }
        echo "</table>";
        ?>
      </div>
    </main>
  </div>
</body>
</html>
