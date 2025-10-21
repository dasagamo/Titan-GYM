<?php
session_start();

if (!isset($_SESSION['id_administrador'])) {
    $_SESSION = [];
    header("Location: forms/login.php");
    exit();
}

require_once "conexion.php";
if (!$conexion) die("Error: No se pudo conectar a la base de datos");

// ====== FUNCIÓN PARA GENERAR QR ======
function generarQR($codigo, $id_cliente) {
    $rutaQR = 'qrcodes/';
    if (!file_exists($rutaQR)) mkdir($rutaQR, 0777, true);
    include 'librerias/phpqrcode/qrlib.php';
    QRcode::png($codigo, $rutaQR . $id_cliente . '.png', 'L', 8, 2);
}

// ====== PROCESAR ACCIONES QR ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_qr'])) {
    $id_cliente = $_POST['id_cliente'];
    
    switch ($_POST['accion_qr']) {
        case 'generar':
            $codigo = 'GYM' . uniqid() . rand(1000,9999);
            // Eliminar QR anterior si existe
            mysqli_query($conexion, "DELETE FROM acceso WHERE Id_Cliente = $id_cliente");
            // Insertar nuevo QR
            mysqli_query($conexion, "INSERT INTO acceso (Id_Cliente, Estado_Acceso, Codigo, Fecha_Generado) 
                                   VALUES ($id_cliente, 'activo', '$codigo', NOW())");
            generarQR($codigo, $id_cliente);
            break;
            
        case 'inhabilitar':
            mysqli_query($conexion, "UPDATE acceso SET Estado_Acceso = 'inactivo' WHERE Id_Cliente = $id_cliente");
            break;
            
        case 'borrar':
            mysqli_query($conexion, "DELETE FROM acceso WHERE Id_Cliente = $id_cliente");
            break;
    }
    
    header("Location: ?modulo=clientes&accion=leer#crud-section");
    exit();
}

// ====== CRUD GLOBAL ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo'])) {
    $modulo = $_POST['modulo'];
    $accion = $_POST['accion'];

    switch ($modulo) {
        // CLIENTES - ACTUALIZADO CON MEMBRESÍAS
        case 'clientes':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $pass = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
                $id_tipo_membrecia = !empty($_POST['id_tipo_membrecia']) ? (int)$_POST['id_tipo_membrecia'] : NULL;
                
                // Insertar cliente
                mysqli_query($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo) 
                                       VALUES ('$nombre', '$apellido', '$telefono', '$correo', '$pass', 3)");
                
                $id_cliente = mysqli_insert_id($conexion);
                
                // Si se seleccionó un tipo de membresía, crear la membresía
                if ($id_tipo_membrecia) {
                    // Obtener la duración del tipo de membresía
                    $result_tipo = mysqli_query($conexion, "SELECT Duracion FROM tipo_membrecia WHERE Id_Tipo_Membrecia = $id_tipo_membrecia");
                    $tipo = mysqli_fetch_assoc($result_tipo);
                    $duracion = $tipo['Duracion'] ?: 30; // Si no tiene duración, 30 días por defecto

                    $fecha_inicio = date('Y-m-d');
                    $fecha_fin = date('Y-m-d', strtotime("+$duracion days"));
                    
                    mysqli_query($conexion, "INSERT INTO membrecia (Id_Cliente, Id_Tipo_Membrecia, Duracion, Fecha_Inicio, Fecha_Fin) 
                                           VALUES ($id_cliente, $id_tipo_membrecia, $duracion, '$fecha_inicio', '$fecha_fin')");
                    
                    // Actualizar el cliente con la membresía
                    mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = LAST_INSERT_ID() WHERE Id_Cliente = $id_cliente");
                }
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $id_tipo_membrecia = !empty($_POST['id_tipo_membrecia']) ? (int)$_POST['id_tipo_membrecia'] : NULL;
                
                // Actualizar datos del cliente
                mysqli_query($conexion, "UPDATE cliente SET 
                    Nombre = '$nombre',
                    Apellido = '$apellido',
                    Telefono = '$telefono',
                    Correo = '$correo'
                    WHERE Id_Cliente = $id");
                
                // Manejar la membresía
                if ($id_tipo_membrecia) {
                    // Verificar si ya tiene una membresía
                    $membrecia_existente = mysqli_query($conexion, "SELECT Id_Membrecia FROM cliente WHERE Id_Cliente = $id");
                    $cliente = mysqli_fetch_assoc($membrecia_existente);
                    
                    if ($cliente['Id_Membrecia']) {
                        // Obtener la duración del tipo de membresía
                        $result_tipo = mysqli_query($conexion, "SELECT Duracion FROM tipo_membrecia WHERE Id_Tipo_Membrecia = $id_tipo_membrecia");
                        $tipo = mysqli_fetch_assoc($result_tipo);
                        $duracion = $tipo['Duracion'] ?: 30;

                        $fecha_inicio = date('Y-m-d');
                        $fecha_fin = date('Y-m-d', strtotime("+$duracion days"));
                        
                        // Actualizar membresía existente
                        mysqli_query($conexion, "UPDATE membrecia SET 
                            Id_Tipo_Membrecia = $id_tipo_membrecia,
                            Duracion = $duracion,
                            Fecha_Inicio = '$fecha_inicio',
                            Fecha_Fin = '$fecha_fin'
                            WHERE Id_Membrecia = {$cliente['Id_Membrecia']}");
                    } else {
                        // Obtener la duración del tipo de membresía
                        $result_tipo = mysqli_query($conexion, "SELECT Duracion FROM tipo_membrecia WHERE Id_Tipo_Membrecia = $id_tipo_membrecia");
                        $tipo = mysqli_fetch_assoc($result_tipo);
                        $duracion = $tipo['Duracion'] ?: 30;

                        $fecha_inicio = date('Y-m-d');
                        $fecha_fin = date('Y-m-d', strtotime("+$duracion days"));
                        
                        // Crear nueva membresía
                        mysqli_query($conexion, "INSERT INTO membrecia (Id_Cliente, Id_Tipo_Membrecia, Duracion, Fecha_Inicio, Fecha_Fin) 
                                               VALUES ($id, $id_tipo_membrecia, $duracion, '$fecha_inicio', '$fecha_fin')");
                        
                        mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = LAST_INSERT_ID() WHERE Id_Cliente = $id");
                    }
                } else {
                    // Si no se selecciona membresía, eliminar la existente
                    mysqli_query($conexion, "DELETE FROM membrecia WHERE Id_Cliente = $id");
                    mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = NULL WHERE Id_Cliente = $id");
                }
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                // Primero eliminar la membresía si existe
                mysqli_query($conexion, "DELETE FROM membrecia WHERE Id_Cliente = $id");
                // Luego eliminar el cliente
                mysqli_query($conexion, "DELETE FROM cliente WHERE Id_Cliente = $id");
            }
            break;

        // ENTRENADORES
        case 'entrenadores':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $id_especialidad = (int)$_POST['id_especialidad'];
                $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
                
                mysqli_query($conexion, "INSERT INTO entrenador (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Especialidad, Id_Tipo) 
                                       VALUES ('$nombre', '$apellido', '$telefono', '$correo', '$contrasena', $id_especialidad, 2)");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $id_especialidad = (int)$_POST['id_especialidad'];
                
                if (!empty($_POST['contrasena'])) {
                    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
                    mysqli_query($conexion, "UPDATE entrenador SET 
                        Nombre = '$nombre',
                        Apellido = '$apellido',
                        Telefono = '$telefono',
                        Correo = '$correo',
                        Contrasena = '$contrasena',
                        Id_Especialidad = $id_especialidad
                        WHERE Id_Entrenador = $id");
                } else {
                    mysqli_query($conexion, "UPDATE entrenador SET 
                        Nombre = '$nombre',
                        Apellido = '$apellido',
                        Telefono = '$telefono',
                        Correo = '$correo',
                        Id_Especialidad = $id_especialidad
                        WHERE Id_Entrenador = $id");
                }
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM entrenador WHERE Id_Entrenador = $id");
            }
            break;

        // INVENTARIO
        case 'inventario':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $marca = mysqli_real_escape_string($conexion, $_POST['marca']);
                $cantidad = (int)$_POST['cantidad'];
                $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
                $id_admin = $_SESSION['id_administrador'];
                
                mysqli_query($conexion, "INSERT INTO producto (Nombre, Marca, Id_Administrador) 
                                       VALUES ('$nombre', '$marca', $id_admin)");
                $id_producto = mysqli_insert_id($conexion);
                
                mysqli_query($conexion, "INSERT INTO inventario (Id_Producto, Cantidad, Ubicacion) 
                                       VALUES ($id_producto, $cantidad, '$ubicacion')");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $marca = mysqli_real_escape_string($conexion, $_POST['marca']);
                $cantidad = (int)$_POST['cantidad'];
                $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
                
                mysqli_query($conexion, "UPDATE producto p 
                    JOIN inventario i ON p.Id_Producto = i.Id_Producto 
                    SET p.Nombre = '$nombre', 
                        p.Marca = '$marca',
                        i.Cantidad = $cantidad,
                        i.Ubicacion = '$ubicacion'
                    WHERE p.Id_Producto = $id");
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM inventario WHERE Id_Producto = $id");
                mysqli_query($conexion, "DELETE FROM producto WHERE Id_Producto = $id");
            }
            break;

        // PROVEEDORES
        case 'proveedores':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $id_producto = (int)$_POST['id_producto'];
                $precio_proveedor = (float)$_POST['precio_proveedor'];
                
                mysqli_query($conexion, "INSERT INTO proveedor (Nombre, Telefono, Correo, Id_Producto, Precio_Proveedor) 
                                       VALUES ('$nombre', '$telefono', '$correo', $id_producto, $precio_proveedor)");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $id_producto = (int)$_POST['id_producto'];
                $precio_proveedor = (float)$_POST['precio_proveedor'];
                
                mysqli_query($conexion, "UPDATE proveedor SET 
                    Nombre = '$nombre',
                    Telefono = '$telefono',
                    Correo = '$correo',
                    Id_Producto = $id_producto,
                    Precio_Proveedor = $precio_proveedor
                    WHERE Id_Proveedor = $id");
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM proveedor WHERE Id_Proveedor = $id");
            }
            break;

        // ESPECIALIDADES
        case 'especialidades':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_especialidad']);
                $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
                
                mysqli_query($conexion, "INSERT INTO especialidad (Nombre_Especialidad, Descripcion) 
                                       VALUES ('$nombre', '$descripcion')");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_especialidad']);
                $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
                
                mysqli_query($conexion, "UPDATE especialidad SET 
                    Nombre_Especialidad = '$nombre',
                    Descripcion = '$descripcion'
                    WHERE Id_Especialidad = $id");
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM especialidad WHERE Id_Especialidad = $id");
            }
            break;

        // TIPOS DE MEMBRESÍA - SOLO DURACIÓN (CORREGIDO)
        case 'membrecias':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
                $precio = (float)$_POST['precio'];
                $duracion = isset($_POST['duracion']) && !empty($_POST['duracion']) ? (int)$_POST['duracion'] : 30;
                
                mysqli_query($conexion, "INSERT INTO tipo_membrecia (Nombre_Tipo, Precio, Duracion) 
                                       VALUES ('$nombre', $precio, $duracion)");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
                $precio = (float)$_POST['precio'];
                $duracion = isset($_POST['duracion']) && !empty($_POST['duracion']) ? (int)$_POST['duracion'] : 30;
                
                mysqli_query($conexion, "UPDATE tipo_membrecia SET 
                    Nombre_Tipo = '$nombre',
                    Precio = $precio,
                    Duracion = $duracion
                    WHERE Id_Tipo_Membrecia = $id");
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM tipo_membrecia WHERE Id_Tipo_Membrecia = $id");
            }
            break;
    }

    header("Location: ?modulo=$modulo&accion=leer#crud-section");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin - Titán GYM</title>
<link rel="icon" href="Imagenes/favicon_1.png">
<link rel="stylesheet" href="Desing/admin.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="site-header">
  <h1><i class="fas fa-dumbbell"></i> Panel Admin - Titán GYM</h1>
  <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</header>

<main class="container">

<section class="plans">
<?php
$modulos = [
  ['clientes', 'fa-users', 'Clientes', "SELECT COUNT(*) AS total FROM cliente"],
  ['entrenadores', 'fa-dumbbell', 'Entrenadores', "SELECT COUNT(*) AS total FROM entrenador"],
  ['inventario', 'fa-boxes', 'Inventario', "SELECT COUNT(*) AS total FROM producto"],
  ['proveedores', 'fa-truck', 'Proveedores', "SELECT COUNT(*) AS total FROM proveedor"],
  ['especialidades', 'fa-certificate', 'Especialidades', "SELECT COUNT(*) AS total FROM especialidad"],
  ['membrecias', 'fa-crown', 'Tipos Membresía', "SELECT COUNT(*) AS total FROM tipo_membrecia"]
];
foreach($modulos as $m){
  $r = mysqli_fetch_assoc(mysqli_query($conexion, $m[3]));
  echo "
  <article class='plan-card'>
    <h3><i class='fas {$m[1]}'></i> {$m[2]}</h3>
    <p>Total registrados: <strong>{$r['total']}</strong></p>
    <button class='cta' onclick=\"goTo('{$m[0]}','leer')\">Gestionar</button>
    <button class='cta' onclick=\"goTo('{$m[0]}','crear')\">Añadir</button>
  </article>";
}
?>
</section>

<div id="crud-section" class="crud-section">
<?php
$modulo = $_GET['modulo'] ?? null;
$accion = $_GET['accion'] ?? 'leer';

if($modulo){
  echo "<h2 class='titulo-crud'><i class='fas fa-cog'></i> Gestión de ".ucfirst($modulo)."</h2>";

  // CLIENTES - ACTUALIZADO CON LA ESTRUCTURA CORRECTA
  if($modulo==='clientes'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"
        SELECT c.Id_Cliente, c.Nombre, c.Apellido, c.Telefono, c.Correo, c.Id_Membrecia,
               a.Codigo, a.Estado_Acceso, 
               m.Fecha_Inicio, m.Fecha_Fin, m.Duracion,
               tm.Nombre_Tipo as Membresia, tm.Precio
        FROM cliente c
        LEFT JOIN acceso a ON c.Id_Cliente = a.Id_Cliente AND a.Estado_Acceso = 'activo'
        LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia
        LEFT JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
        ORDER BY c.Id_Cliente ASC
      ");

      echo "<table>
        <tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Membresía</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>QR</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        $qrImg = ($r['Codigo'] && file_exists("qrcodes/{$r['Id_Cliente']}.png"))
          ? "<img src='qrcodes/{$r['Id_Cliente']}.png' width='60' alt='QR'>"
          : "<span style='color:#777'>Sin QR</span>";

        // Obtener el Id_Tipo_Membrecia actual para el modal de edición
        $id_tipo_membrecia_actual = null;
        if ($r['Id_Membrecia']) {
            $tipo_membrecia = mysqli_query($conexion, "SELECT Id_Tipo_Membrecia FROM membrecia WHERE Id_Membrecia = {$r['Id_Membrecia']}");
            if ($tipo_membrecia && mysqli_num_rows($tipo_membrecia) > 0) {
                $tipo = mysqli_fetch_assoc($tipo_membrecia);
                $id_tipo_membrecia_actual = $tipo['Id_Tipo_Membrecia'];
            }
        }

        echo "<tr>
          <td>{$r['Id_Cliente']}</td>
          <td>{$r['Nombre']} {$r['Apellido']}</td>
          <td>{$r['Telefono']}</td>
          <td>{$r['Correo']}</td>
          <td>".($r['Membresia'] ?? 'Sin membresía')."</td>
          <td>".($r['Fecha_Inicio'] ?? 'N/A')."</td>
          <td>".($r['Fecha_Fin'] ?? 'N/A')."</td>
          <td style='text-align:center;'>$qrImg
            <form method='POST' class='inline' style='margin-top:5px;'>
              <input type='hidden' name='id_cliente' value='{$r['Id_Cliente']}'>
              <button class='btn-edit' name='accion_qr' value='generar'>Generar QR</button>
              <button class='btn-save' name='accion_qr' value='inhabilitar'>Inhabilitar</button>
              <button class='btn-delete' name='accion_qr' value='borrar'>Eliminar QR</button>
            </form>
          </td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionCliente({$r['Id_Cliente']}, '{$r['Nombre']}', '{$r['Apellido']}', '{$r['Telefono']}', '{$r['Correo']}', " . ($id_tipo_membrecia_actual ?: 'null') . ")\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='clientes'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Cliente']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar cliente?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      }
      echo "</table>";
    }
    elseif($accion==='crear'){
      $membrecias = mysqli_query($conexion,"SELECT Id_Tipo_Membrecia, Nombre_Tipo, Precio FROM tipo_membrecia");
      
      echo "
      <div class='form-card'>
        <h3>Nuevo Cliente</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='clientes'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label><input name='nombre' required>
          <label>Apellido:</label><input name='apellido' required>
          <label>Teléfono:</label><input name='telefono'>
          <label>Correo:</label><input type='email' name='correo'>
          <label>Contraseña:</label> <input type='password' name='contrasena' required minlength='5'>
          <label>Tipo de Membresía:</label>
          <select name='id_tipo_membrecia'>
            <option value=''>--Seleccionar Membresía--</option>";
            while($mem=mysqli_fetch_assoc($membrecias)){
              echo "<option value='{$mem['Id_Tipo_Membrecia']}'>{$mem['Nombre_Tipo']} - \${$mem['Precio']}</option>";
            }
      echo "</select>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // ENTRENADORES
  if($modulo==='entrenadores'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"
        SELECT e.*, esp.Nombre_Especialidad 
        FROM entrenador e 
        LEFT JOIN especialidad esp ON e.Id_Especialidad = esp.Id_Especialidad
      ");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Especialidad</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Entrenador']}</td>
          <td>{$r['Nombre']} {$r['Apellido']}</td>
          <td>{$r['Telefono']}</td>
          <td>{$r['Correo']}</td>
          <td>".($r['Nombre_Especialidad'] ?? 'Sin especialidad')."</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionEntrenador({$r['Id_Entrenador']}, '{$r['Nombre']}', '{$r['Apellido']}', '{$r['Telefono']}', '{$r['Correo']}', '{$r['Id_Especialidad']}')\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='entrenadores'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Entrenador']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar entrenador?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      } 
      echo "</table>";
    }
    elseif($accion==='crear'){
      $especialidades = mysqli_query($conexion,"SELECT Id_Especialidad, Nombre_Especialidad FROM especialidad");
      echo "
      <div class='form-card'>
        <h3>Nuevo Entrenador</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='entrenadores'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label><input name='nombre' required>
          <label>Apellido:</label><input name='apellido' required>
          <label>Teléfono:</label><input name='telefono'>
          <label>Correo:</label><input type='email' name='correo'>
          <label>Contraseña:</label> <input type='password' name='contrasena' required minlength='5'>
          <label>Especialidad:</label>
          <select name='id_especialidad'>
            <option value=''>--Seleccionar--</option>";
            while($esp=mysqli_fetch_assoc($especialidades)){
              echo "<option value='{$esp['Id_Especialidad']}'>{$esp['Nombre_Especialidad']}</option>";
            }
      echo "</select>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // INVENTARIO
  if($modulo==='inventario'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"
        SELECT p.Id_Producto, p.Nombre, p.Marca, i.Cantidad, i.Ubicacion
        FROM producto p
        JOIN inventario i ON p.Id_Producto = i.Id_Producto
      ");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Cantidad</th><th>Ubicación</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Producto']}</td>
          <td>{$r['Nombre']}</td>
          <td>{$r['Marca']}</td>
          <td>{$r['Cantidad']}</td>
          <td>{$r['Ubicacion']}</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionInventario({$r['Id_Producto']}, '{$r['Nombre']}', '{$r['Marca']}', {$r['Cantidad']}, '{$r['Ubicacion']}')\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='inventario'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Producto']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar producto?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      } 
      echo "</table>";
    }
    elseif($accion==='crear'){
      echo "
      <div class='form-card'>
        <h3>Nuevo Producto</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='inventario'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label><input name='nombre' required>
          <label>Marca:</label><input name='marca'>
          <label>Cantidad:</label><input type='number' name='cantidad' required>
          <label>Ubicación:</label><input name='ubicacion'>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // PROVEEDORES
  if($modulo==='proveedores'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"
        SELECT pr.*, p.Nombre as Producto
        FROM proveedor pr
        JOIN producto p ON pr.Id_Producto = p.Id_Producto
      ");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Producto</th><th>Precio Proveedor</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Proveedor']}</td>
          <td>{$r['Nombre']}</td>
          <td>{$r['Telefono']}</td>
          <td>{$r['Correo']}</td>
          <td>{$r['Producto']}</td>
          <td>\${$r['Precio_Proveedor']}</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionProveedor({$r['Id_Proveedor']}, '{$r['Nombre']}', '{$r['Telefono']}', '{$r['Correo']}', {$r['Id_Producto']}, {$r['Precio_Proveedor']})\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='proveedores'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Proveedor']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar proveedor?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      } 
      echo "</table>";
    }
    elseif($accion==='crear'){
      $productos = mysqli_query($conexion,"SELECT Id_Producto, Nombre FROM producto");
      echo "
      <div class='form-card'>
        <h3>Nuevo Proveedor</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='proveedores'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label><input name='nombre' required>
          <label>Teléfono:</label><input name='telefono'>
          <label>Correo:</label><input type='email' name='correo'>
          <label>Producto:</label>
          <select name='id_producto' required>
            <option value=''>--Seleccionar--</option>";
            while($p=mysqli_fetch_assoc($productos)){
              echo "<option value='{$p['Id_Producto']}'>{$p['Nombre']}</option>";
            }
      echo "</select>
          <label>Precio Proveedor:</label>
          <input type='number' step='0.01' name='precio_proveedor' required>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // ESPECIALIDADES
  if($modulo==='especialidades'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"SELECT * FROM especialidad");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Especialidad']}</td>
          <td>{$r['Nombre_Especialidad']}</td>
          <td>".($r['Descripcion'] ?? 'Sin descripción')."</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionEspecialidad({$r['Id_Especialidad']}, '{$r['Nombre_Especialidad']}', '{$r['Descripcion']}')\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='especialidades'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Especialidad']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar especialidad?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      } 
      echo "</table>";
    }
    elseif($accion==='crear'){
      echo "
      <div class='form-card'>
        <h3>Nueva Especialidad</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='especialidades'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label><input name='nombre_especialidad' required>
          <label>Descripción:</label>
          <textarea name='descripcion' rows='3' placeholder='Descripción de la especialidad'></textarea>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // TIPOS DE MEMBRESÍA - SOLO DURACIÓN (CORREGIDO)
  if($modulo==='membrecias'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"SELECT * FROM tipo_membrecia");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Duración (días)</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Tipo_Membrecia']}</td>
          <td>{$r['Nombre_Tipo']}</td>
          <td>\${$r['Precio']}</td>
          <td>".($r['Duracion'] ?? '30')."</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionMembresia({$r['Id_Tipo_Membrecia']}, '{$r['Nombre_Tipo']}', {$r['Precio']}, " . ($r['Duracion'] ?? '30') . ")\">Editar</button>
            <form method='POST' class='inline'>
              <input type='hidden' name='modulo' value='membrecias'>
              <input type='hidden' name='accion' value='eliminar'>
              <input type='hidden' name='id' value='{$r['Id_Tipo_Membrecia']}'>
              <button class='btn-delete' onclick=\"return confirm('¿Eliminar tipo de membresía?')\">Eliminar</button>
            </form>
          </td>
        </tr>";
      } 
      echo "</table>";
    }
    elseif($accion==='crear'){
      echo "
      <div class='form-card'>
        <h3>Nuevo Tipo de Membresía</h3>
        <form method='POST'>
          <input type='hidden' name='modulo' value='membrecias'>
          <input type='hidden' name='accion' value='crear'>
          <label>Nombre:</label>
          <input name='nombre_tipo' required placeholder='Ej: Básica, Premium, VIP'>
          <label>Precio:</label>
          <input type='number' step='0.01' name='precio' required placeholder='0.00'>
          <label>Duración (días):</label>
          <input type='number' name='duracion' value='30' min='1' max='365'>
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }
}
?>
</div>

<!-- MODALES PARA EDITAR -->
<div id="modalEdicion" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <div id="contenidoModal"></div>
  </div>
</div>

<footer class="site-footer">
  <p>&copy; 2025 Titán GYM. Todos los derechos reservados.</p>
</footer>

<script>
function goTo(modulo,accion){
  window.location.href='?modulo='+modulo+'&accion='+accion+'#crud-section';
}

// Función para cargar membresías via AJAX
async function cargarMembrecias() {
    try {
        const response = await fetch('obtener_membrecias.php');
        const membresias = await response.json();
        return membresias;
    } catch (error) {
        console.error('Error al cargar membresías:', error);
        return [];
    }
}

// Función específica para edición de clientes
async function mostrarFormularioEdicionCliente(id, nombre, apellido, telefono, correo, idTipoMembrecia) {
    const modal = document.getElementById('modalEdicion');
    const contenido = document.getElementById('contenidoModal');
    
    const membresias = await cargarMembrecias();
    let options = '<option value="">--Seleccionar Membresía--</option>';
    
    membresias.forEach(mem => {
        const selected = mem.Id_Tipo_Membrecia == idTipoMembrecia ? 'selected' : '';
        options += `<option value="${mem.Id_Tipo_Membrecia}" ${selected}>${mem.Nombre_Tipo} - $${mem.Precio}</option>`;
    });
    
    contenido.innerHTML = `
        <h3>Editar Cliente</h3>
        <form method="POST" id="formEdicion">
            <input type="hidden" name="modulo" value="clientes">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" value="${id}">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="${nombre}" required>
            <label>Apellido:</label>
            <input type="text" name="apellido" value="${apellido}" required>
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="${telefono}">
            <label>Correo:</label>
            <input type="email" name="correo" value="${correo}">
            <label>Tipo de Membresía:</label>
            <select name="id_tipo_membrecia">${options}</select>
            <button type="submit" class="btn-save">Actualizar</button>
        </form>
    `;
    
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    
    document.getElementById('formEdicion').onsubmit = function(e) {
        e.preventDefault();
        this.submit();
    };
}

// Función para edición de membresías SOLO CON DURACIÓN
function mostrarFormularioEdicionMembresia(id, nombre, precio, duracion) {
    const modal = document.getElementById('modalEdicion');
    const contenido = document.getElementById('contenidoModal');
    
    const duracionValue = duracion && duracion !== 'null' ? duracion : 30;
    
    contenido.innerHTML = `
        <h3>Editar Tipo de Membresía</h3>
        <form method="POST" id="formEdicion">
            <input type="hidden" name="modulo" value="membrecias">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" value="${id}">
            <label>Nombre:</label>
            <input type="text" name="nombre_tipo" value="${nombre}" required placeholder="Ej: Básica, Premium, VIP">
            <label>Precio:</label>
            <input type="number" step="0.01" name="precio" value="${precio}" required placeholder="0.00">
            <label>Duración (días):</label>
            <input type="number" name="duracion" value="${duracionValue}" min="1" max="365">
            <button type="submit" class="btn-save">Actualizar</button>
        </form>
    `;
    
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    
    document.getElementById('formEdicion').onsubmit = function(e) {
        e.preventDefault();
        this.submit();
    };
}

// Funciones existentes para otros módulos
function mostrarFormularioEdicionEntrenador(id, nombre, apellido, telefono, correo, idEspecialidad) {
    const modal = document.getElementById('modalEdicion');
    const contenido = document.getElementById('contenidoModal');
    
    contenido.innerHTML = `
        <h3>Editar Entrenador</h3>
        <form method="POST" id="formEdicion">
            <input type="hidden" name="modulo" value="entrenadores">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" value="${id}">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="${nombre}" required>
            <label>Apellido:</label>
            <input type="text" name="apellido" value="${apellido}" required>
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="${telefono}">
            <label>Correo:</label>
            <input type="email" name="correo" value="${correo}">
            <label>Nueva Contraseña (opcional):</label>
            <input type="password" name="contrasena" placeholder="Dejar vacío para no cambiar">
            <label>Especialidad:</label>
            <select name="id_especialidad" required>
                <option value="">--Seleccionar--</option>
                ${obtenerOpcionesEspecialidades(idEspecialidad)}
            </select>
            <button type="submit" class="btn-save">Actualizar</button>
        </form>
    `;
    
    modal.style.display = 'block';
    
    document.getElementById('formEdicion').onsubmit = function(e) {
        e.preventDefault();
        this.submit();
    };
}

function obtenerOpcionesEspecialidades(idSeleccionado) {
    return `
        <option value="1" ${idSeleccionado == 1 ? 'selected' : ''}>Culturismo</option>
        <option value="2" ${idSeleccionado == 2 ? 'selected' : ''}>Cardio</option>
        <option value="3" ${idSeleccionado == 3 ? 'selected' : ''}>Yoga</option>
        <option value="4" ${idSeleccionado == 4 ? 'selected' : ''}>CrossFit</option>
    `;
}

// Cerrar modal
document.querySelector('.close').addEventListener('click', function() {
    cerrarModal();
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalEdicion');
    if (event.target == modal) {
        cerrarModal();
    }
});

function cerrarModal() {
    const modal = document.getElementById('modalEdicion');
    modal.classList.add('fade-out');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('fade-out');
        document.body.classList.remove('modal-open');
    }, 300);
}

document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('modalEdicion');
    if (event.key === 'Escape' && modal.style.display === 'block') {
        cerrarModal();
    }
});

window.addEventListener('load',()=>{
  if(window.location.hash==='#crud-section'){
    document.querySelector('#crud-section').scrollIntoView({behavior:'smooth'});
  }
});
</script>

</main>
</body>
</html>