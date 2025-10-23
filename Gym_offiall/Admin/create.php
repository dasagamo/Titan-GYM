<?php
// create.php
session_start();
if (!isset($_SESSION['id_administrador'])) { header('Location: forms/login.php'); exit(); }
require_once "../Conexion.php";
if (!$conexion) die("Error: No se pudo conectar a la base de datos");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo'])) {
    $mod = $_POST['modulo'];

    // CLIENTES
    if ($mod === 'clientes' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $pass = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
        $id_tipo = isset($_POST['id_tipo_membrecia']) && $_POST['id_tipo_membrecia'] !== '' ? (int)$_POST['id_tipo_membrecia'] : null;

        mysqli_query($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo) 
                                VALUES ('$nombre','$apellido','$telefono','$correo','$pass',3)");
        $id_cliente = mysqli_insert_id($conexion);

        if ($id_tipo) {
            // obtener duración
            $res = mysqli_query($conexion, "SELECT Duracion FROM tipo_membrecia WHERE Id_Tipo_Membrecia = $id_tipo");
            $row = mysqli_fetch_assoc($res);
            $dur = $row['Duracion'] ?? 30;
            $f_inicio = date('Y-m-d');
            $f_fin = date('Y-m-d', strtotime("+$dur days"));

            mysqli_query($conexion, "INSERT INTO membrecia (Id_Tipo_Membrecia, Duracion, Fecha_Inicio, Fecha_Fin)
                                    VALUES ($id_tipo, $dur, '$f_inicio', '$f_fin')");
            $id_memb = mysqli_insert_id($conexion);
            mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = $id_memb WHERE Id_Cliente = $id_cliente");
        }

        $msg = "Cliente creado correctamente.";
    }

    // ENTRENADORES
    if ($mod === 'entrenadores' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $id_esp = !empty($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : "NULL";
        $pass = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);

        mysqli_query($conexion, "INSERT INTO entrenador (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Especialidad, Id_Tipo)
                                VALUES ('$nombre','$apellido','$telefono','$correo','$pass',$id_esp,2)");
        $msg = "Entrenador creado correctamente.";
    }

    // PRODUCTO + INVENTARIO
    if ($mod === 'inventario' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $marca = mysqli_real_escape_string($conexion, $_POST['marca']);
        $cantidad = (int)$_POST['cantidad'];
        $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
        $id_admin = $_SESSION['id_administrador'];

        mysqli_query($conexion, "INSERT INTO producto (Nombre, Marca, Id_Administrador) VALUES ('$nombre','$marca',$id_admin)");
        $id_producto = mysqli_insert_id($conexion);
        mysqli_query($conexion, "INSERT INTO inventario (Id_Producto, Cantidad, Ubicacion) VALUES ($id_producto,$cantidad,'$ubicacion')");
        $msg = "Producto e inventario creados correctamente.";
    }

    // PROVEEDORES
    if ($mod === 'proveedores' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $id_producto = (int)$_POST['id_producto'];
        $precio = (float)$_POST['precio_proveedor'];

        mysqli_query($conexion, "INSERT INTO proveedor (Nombre, Telefono, Correo, Id_Producto, Precio_Proveedor)
                                VALUES ('$nombre','$telefono','$correo',$id_producto,$precio)");
        $msg = "Proveedor creado correctamente.";
    }

    // ESPECIALIDADES
    if ($mod === 'especialidades' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_especialidad']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        mysqli_query($conexion, "INSERT INTO especialidad (Nombre_Especialidad, Descripcion) VALUES ('$nombre','$descripcion')");
        $msg = "Especialidad creada correctamente.";
    }

    // TIPOS MEMBRESIA
    if ($mod === 'membrecias' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
        $precio = (float)$_POST['precio'];
        $dur = isset($_POST['duracion']) && $_POST['duracion'] !== '' ? (int)$_POST['duracion'] : 30;
        mysqli_query($conexion, "INSERT INTO tipo_membrecia (Nombre_Tipo, Precio, Duracion) VALUES ('$nombre',$precio,$dur)");
        $msg = "Tipo de membresía creado correctamente.";
    }

    // redirigir o mostrar mensaje
    header("Location: create.php?modulo=$mod&ok=" . urlencode($msg));
    exit();
}

// variables para formularios
$modulo_sel = $_GET['modulo'] ?? '';
$ok = $_GET['ok'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear - Admin</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="header">
    <h1>Crear registros</h1>
    <a href="index_admin.php" class="logout">Volver</a>
  </div>
  <div class="layout">
    <aside class="sidebar">
      <a href="create.php?modulo=clientes">Clientes</a>
      <a href="create.php?modulo=entrenadores">Entrenadores</a>
      <a href="create.php?modulo=inventario">Productos / Inventario</a>
      <a href="create.php?modulo=proveedores">Proveedores</a>
      <a href="create.php?modulo=especialidades">Especialidades</a>
      <a href="create.php?modulo=membrecias">Tipos Membresía</a>
    </aside>
    <main class="main">
      <div class="crud-section">
        <?php if($ok): ?>
          <div style="padding:10px;background:#e6ffed;border:1px solid #b6f2c0;margin-bottom:12px;"><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>

        <?php if($modulo_sel === 'clientes' || $modulo_sel === ''): ?>
          <h2>Nuevo Cliente</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="clientes">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre</label><input name="nombre" required>
              <label>Apellido</label><input name="apellido" required>
              <label>Teléfono</label><input name="telefono">
              <label>Correo</label><input type="email" name="correo">
              <label>Contraseña</label><input type="password" name="contrasena" required>
              <label>Tipo de Membresía (opcional)</label>
              <select name="id_tipo_membrecia">
                <option value="">--Sin membresía--</option>
                <?php $t = mysqli_query($conexion,"SELECT * FROM tipo_membrecia"); while($row=mysqli_fetch_assoc($t)): ?>
                  <option value="<?php echo $row['Id_Tipo_Membrecia']?>"><?php echo $row['Nombre_Tipo']?> - $<?php echo $row['Precio']?></option>
                <?php endwhile; ?>
              </select>
              <button class="btn-save">Guardar cliente</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if($modulo_sel === 'entrenadores'): ?>
          <h2>Nuevo Entrenador</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="entrenadores">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre</label><input name="nombre" required>
              <label>Apellido</label><input name="apellido" required>
              <label>Teléfono</label><input name="telefono">
              <label>Correo</label><input type="email" name="correo">
              <label>Contraseña</label><input type="password" name="contrasena" required>
              <label>Especialidad</label>
              <select name="id_especialidad">
                <option value="">--Sin especialidad--</option>
                <?php $esp = mysqli_query($conexion,"SELECT * FROM especialidad"); while($e=mysqli_fetch_assoc($esp)): ?>
                  <option value="<?php echo $e['Id_Especialidad']?>"><?php echo $e['Nombre_Especialidad']?></option>
                <?php endwhile; ?>
              </select>
              <button class="btn-save">Guardar entrenador</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if($modulo_sel === 'inventario'): ?>
          <h2>Nuevo Producto / Inventario</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="inventario">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre producto</label><input name="nombre" required>
              <label>Marca</label><input name="marca">
              <label>Cantidad</label><input type="number" name="cantidad" value="1" required>
              <label>Ubicación</label><input name="ubicacion">
              <button class="btn-save">Guardar producto</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if($modulo_sel === 'proveedores'): ?>
          <h2>Nuevo Proveedor</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="proveedores">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre</label><input name="nombre" required>
              <label>Teléfono</label><input name="telefono">
              <label>Correo</label><input type="email" name="correo">
              <label>Producto</label>
              <select name="id_producto" required>
                <option value="">--Seleccionar producto--</option>
                <?php $prod = mysqli_query($conexion,"SELECT * FROM producto"); while($p=mysqli_fetch_assoc($prod)): ?>
                  <option value="<?php echo $p['Id_Producto']?>"><?php echo $p['Nombre']?></option>
                <?php endwhile; ?>
              </select>
              <label>Precio proveedor</label><input type="number" step="0.01" name="precio_proveedor" required>
              <button class="btn-save">Guardar proveedor</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if($modulo_sel === 'especialidades'): ?>
          <h2>Nueva Especialidad</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="especialidades">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre</label><input name="nombre_especialidad" required>
              <label>Descripción</label><textarea name="descripcion" rows="4"></textarea>
              <button class="btn-save">Guardar especialidad</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if($modulo_sel === 'membrecias'): ?>
          <h2>Nuevo Tipo de Membresía</h2>
          <div class="form-card">
            <form method="POST">
              <input type="hidden" name="modulo" value="membrecias">
              <input type="hidden" name="accion" value="crear">
              <label>Nombre</label><input name="nombre_tipo" required>
              <label>Precio</label><input type="number" step="0.01" name="precio" required>
              <label>Duración (días)</label><input type="number" name="duracion" value="30" min="1">
              <button class="btn-save">Guardar tipo</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
