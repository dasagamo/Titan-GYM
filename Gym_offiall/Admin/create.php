<?php
// create.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['Id_Admin'])) {
  header('Location: ../forms/login.php');
  exit();
}

require_once "../Conexion.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
        $id_tipo = !empty($_POST['id_tipo_membrecia']) ? (int)$_POST['id_tipo_membrecia'] : null;

        mysqli_query($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena) 
                                 VALUES ('$nombre','$apellido','$telefono','$correo','$pass')");
        $id_cliente = mysqli_insert_id($conexion);

        if ($id_tipo) {
            $dur = 30;
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

        mysqli_query($conexion, "INSERT INTO entrenador (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Especialidad)
                                 VALUES ('$nombre','$apellido','$telefono','$correo','$pass',$id_esp)");
        $msg = "Entrenador creado correctamente.";
    }

// === CREACIÓN DE PRODUCTO ===
if ($mod === 'inventario' && $_POST['accion'] === 'crear') {
    // 1️⃣ Escapar los datos recibidos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $marca = mysqli_real_escape_string($conexion, $_POST['marca']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria_nombre = mysqli_real_escape_string($conexion, $_POST['categoria']);

    // 2️⃣ Verificar si la categoría existe o crearla
    $res = mysqli_query($conexion, "SELECT Id_Categoria FROM categoria_producto WHERE Nombre = '$categoria_nombre'");
    if (mysqli_num_rows($res) > 0) {
        $fila = mysqli_fetch_assoc($res);
        $id_categoria = $fila['Id_Categoria'];
    } else {
        mysqli_query($conexion, "INSERT INTO categoria_producto (Nombre) VALUES ('$categoria_nombre')");
        $id_categoria = mysqli_insert_id($conexion);
    }

    // 3️⃣ Manejar la imagen (opcional)
    $ruta_bd = NULL; // Valor por defecto
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Carpeta destino (asegúrate que exista dentro de tu proyecto)
        $dir_destino = __DIR__ . "/../Tienda/Imagenes_productos/";

        // Crear carpeta si no existe
        if (!file_exists($dir_destino)) {
            mkdir($dir_destino, 0777, true);
        }

        // Limpiar y generar un nombre seguro
        $nombre_img = basename($_FILES['imagen']['name']);
        $nombre_img = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $nombre_img);

        // Evitar colisiones de nombre
        $nombre_unico = time() . "_" . $nombre_img;

        // Ruta completa en el servidor
        $ruta_img = $dir_destino . $nombre_unico;

        // Mover archivo subido
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_img)) {
            // Ruta que se guardará en la BD (ruta relativa)
            $ruta_bd = "Tienda/Imagenes_productos/" . $nombre_unico;
        } else {
            echo "<p style='color:red;'>⚠️ Error al mover la imagen al destino: $ruta_img</p>";
        }
    } else {
        echo "<p style='color:orange;'>⚠️ No se subió ninguna imagen o hubo error (" . ($_FILES['imagen']['error'] ?? 'sin archivo') . ").</p>";
    }

    // 4️⃣ Insertar el producto (ya incluye la ruta de imagen)
    $sql_insert = "INSERT INTO producto (Nombre, Marca, Descripcion, Precio, Stock, Ruta_Imagen, Id_Categoria)
                   VALUES ('$nombre', '$marca', '$descripcion', $precio, $stock, " .
                   ($ruta_bd ? "'$ruta_bd'" : "NULL") . ", $id_categoria)";

    if (mysqli_query($conexion, $sql_insert)) {
        $msg = "✅ Producto creado correctamente.";
        header("Location: create.php?modulo=$mod&ok=" . urlencode($msg));
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error al insertar producto: " . mysqli_error($conexion) . "</p>";
    }
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

    // TIPOS DE MEMBRESÍA
    if ($mod === 'membrecias' && $_POST['accion'] === 'crear') {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
        $precio = (float)$_POST['precio'];

        mysqli_query($conexion, "INSERT INTO tipo_membrecia (Nombre_Tipo, Precio) VALUES ('$nombre',$precio)");
        $msg = "Tipo de membresía creado correctamente.";
    }

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
      <a href="create.php?modulo=inventario">Productos</a>
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

        <?php if($modulo_sel === 'inventario'):?>
          <h2>Nuevo Producto</h2>
          <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modulo" value="inventario">
              <input type="hidden" name="accion" value="crear">

              <label>Nombre del producto</label>
              <input type="text" name="nombre" required>

              <label>Marca</label>
              <input type="text" name="marca">

              <label>Descripción</label>
              <textarea name="descripcion" rows="3" placeholder="Descripción del producto..."></textarea>

              <label>Precio</label>
              <input type="number" name="precio" step="0.01" required>

              <label>Stock disponible</label>
              <input type="number" name="stock" min="1" required>

              <label>Categoría</label>
              <input type="text" name="categoria" placeholder="Ejemplo: Suplementos" required>

              <label>Imagen del producto</label>
              <input type="file" name="imagen" accept="image/*">

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
              <!-- Quitamos el campo duración ya que no pertenece a tipo_membrecia -->
              <button class="btn-save">Guardar tipo</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>