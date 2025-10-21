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
        // CLIENTES
        case 'clientes':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                $pass = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
                
                mysqli_query($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo) 
                                       VALUES ('$nombre', '$apellido', '$telefono', '$correo', '$pass', 3)");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
                $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
                $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
                $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
                
                mysqli_query($conexion, "UPDATE cliente SET 
                    Nombre = '$nombre',
                    Apellido = '$apellido',
                    Telefono = '$telefono',
                    Correo = '$correo'
                    WHERE Id_Cliente = $id");
            } elseif ($accion === 'eliminar') {
                $id = (int)$_POST['id'];
                mysqli_query($conexion, "DELETE FROM cliente WHERE Id_Cliente = $id");
            }
            break;

        // ENTRENADORES - CORREGIDO CON CONTRASEÑA
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
                
                // Si se proporciona nueva contraseña, actualizarla
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
                
                // Insertar producto
                mysqli_query($conexion, "INSERT INTO producto (Nombre, Marca, Id_Administrador) 
                                       VALUES ('$nombre', '$marca', $id_admin)");
                $id_producto = mysqli_insert_id($conexion);
                
                // Insertar en inventario
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

        // TIPOS DE MEMBRESÍA
        case 'membrecias':
            if ($accion === 'crear') {
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
                $precio = (float)$_POST['precio'];
                
                mysqli_query($conexion, "INSERT INTO tipo_membrecia (Nombre_Tipo, Precio) 
                                       VALUES ('$nombre', $precio)");
            } elseif ($accion === 'editar') {
                $id = (int)$_POST['id'];
                $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
                $precio = (float)$_POST['precio'];
                
                mysqli_query($conexion, "UPDATE tipo_membrecia SET 
                    Nombre_Tipo = '$nombre',
                    Precio = $precio
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

  // CLIENTES
  if($modulo==='clientes'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"
        SELECT c.Id_Cliente, c.Nombre, c.Apellido, c.Telefono, c.Correo,
               a.Codigo, a.Estado_Acceso, tm.Nombre_Tipo as Membresia
        FROM cliente c
        LEFT JOIN acceso a ON c.Id_Cliente = a.Id_Cliente AND a.Estado_Acceso = 'activo'
        LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia
        LEFT JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
        ORDER BY c.Id_Cliente ASC
      ");

      echo "<table>
        <tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Membresía</th><th>QR</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        $qrImg = ($r['Codigo'] && file_exists("qrcodes/{$r['Id_Cliente']}.png"))
          ? "<img src='qrcodes/{$r['Id_Cliente']}.png' width='60' alt='QR'>"
          : "<span style='color:#777'>Sin QR</span>";

        echo "<tr>
          <td>{$r['Id_Cliente']}</td>
          <td>{$r['Nombre']} {$r['Apellido']}</td>
          <td>{$r['Telefono']}</td>
          <td>{$r['Correo']}</td>
          <td>".($r['Membresia'] ?? 'Sin membresía')."</td>
          <td style='text-align:center;'>$qrImg
            <form method='POST' class='inline' style='margin-top:5px;'>
              <input type='hidden' name='id_cliente' value='{$r['Id_Cliente']}'>
              <button class='btn-edit' name='accion_qr' value='generar'>Generar QR</button>
              <button class='btn-save' name='accion_qr' value='inhabilitar'>Inhabilitar</button>
              <button class='btn-delete' name='accion_qr' value='borrar'>Eliminar QR</button>
            </form>
          </td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicion('clientes', {$r['Id_Cliente']}, '{$r['Nombre']}', '{$r['Apellido']}', '{$r['Telefono']}', '{$r['Correo']}')\">Editar</button>
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
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }

  // ENTRENADORES - CORREGIDO CON CONTRASEÑA
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

  // TIPOS DE MEMBRESÍA
  if($modulo==='membrecias'){
    if($accion==='leer'){
      $res = mysqli_query($conexion,"SELECT * FROM tipo_membrecia");
      echo "<table><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Acciones</th></tr>";
      while($r=mysqli_fetch_assoc($res)){
        echo "<tr>
          <td>{$r['Id_Tipo_Membrecia']}</td>
          <td>{$r['Nombre_Tipo']}</td>
          <td>\${$r['Precio']}</td>
          <td>
            <button class='btn-edit' onclick=\"mostrarFormularioEdicionMembresia({$r['Id_Tipo_Membrecia']}, '{$r['Nombre_Tipo']}', {$r['Precio']})\">Editar</button>
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
          <button class='btn-save'>Guardar</button>
        </form>
      </div>";
    }
  }
}
?>
</div>

<!-- MODALES PARA EDITAR -->
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

// Funciones de edición COMPLETAS con modales
function mostrarFormularioEdicion(modulo, id, nombre, apellido, telefono, correo) {
    const modal = document.getElementById('modalEdicion');
    const contenido = document.getElementById('contenidoModal');
    
    contenido.innerHTML = `
        <h3>Editar ${modulo.charAt(0).toUpperCase() + modulo.slice(1)}</h3>
        <form method="POST" id="formEdicion">
            <input type="hidden" name="modulo" value="${modulo}">
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
            <button type="submit" class="btn-save">Actualizar</button>
        </form>
    `;
    
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    
    // Configurar el envío del formulario
    document.getElementById('formEdicion').onsubmit = function(e) {
        e.preventDefault();
        this.submit();
    };
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

// Función para cerrar modal con animación
function cerrarModal() {
    const modal = document.getElementById('modalEdicion');
    modal.classList.add('fade-out');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('fade-out');
        document.body.classList.remove('modal-open');
    }, 300);
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('modalEdicion');
    if (event.key === 'Escape' && modal.style.display === 'block') {
        cerrarModal();
    }
});
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
    // Esta función debería obtener las especialidades via AJAX o cargarlas previamente
    // Por ahora, devolvemos opciones básicas
    return `
        <option value="1" ${idSeleccionado == 1 ? 'selected' : ''}>Culturismo</option>
        <option value="2" ${idSeleccionado == 2 ? 'selected' : ''}>Cardio</option>
        <option value="3" ${idSeleccionado == 3 ? 'selected' : ''}>Yoga</option>
        <option value="4" ${idSeleccionado == 4 ? 'selected' : ''}>CrossFit</option>
    `;
}

// Cerrar modal
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('modalEdicion').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalEdicion');
    if (event.target == modal) {
        modal.style.display = 'none';
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