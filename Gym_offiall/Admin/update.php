<?php
// update.php
session_start();
if (!isset($_SESSION['id_administrador'])) { header('Location: forms/login.php'); exit(); }
require_once "../Conexion.php";

if (!$conexion) die("Error: No se pudo conectar a la base de datos");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo'])) {
    $mod = $_POST['modulo'];
    // CLIENTE - editar
    if ($mod === 'clientes' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $id_tipo = !empty($_POST['id_tipo_membrecia']) ? (int)$_POST['id_tipo_membrecia'] : null;

        mysqli_query($conexion, "UPDATE cliente SET Nombre='$nombre', Apellido='$apellido', Telefono='$telefono', Correo='$correo' WHERE Id_Cliente=$id");

        // membresía
        if ($id_tipo) {
            $existing = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT Id_Membrecia FROM cliente WHERE Id_Cliente=$id"));
            $dur = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT Duracion FROM tipo_membrecia WHERE Id_Tipo_Membrecia=$id_tipo"))['Duracion'] ?? 30;
            $f_inicio = date('Y-m-d');
            $f_fin = date('Y-m-d', strtotime("+$dur days"));
            if ($existing && $existing['Id_Membrecia']) {
                mysqli_query($conexion, "UPDATE membrecia SET Id_Tipo_Membrecia=$id_tipo, Duracion=$dur, Fecha_Inicio='$f_inicio', Fecha_Fin='$f_fin' WHERE Id_Membrecia={$existing['Id_Membrecia']}");
            } else {
                mysqli_query($conexion, "INSERT INTO membrecia (Id_Tipo_Membrecia, Duracion, Fecha_Inicio, Fecha_Fin) VALUES ($id_tipo,$dur,'$f_inicio','$f_fin')");
                $id_m = mysqli_insert_id($conexion);
                mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia=$id_m WHERE Id_Cliente=$id");
            }
        } else {
            // eliminar membresía si existe (cliente queda sin membresía)
            $existing = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT Id_Membrecia FROM cliente WHERE Id_Cliente=$id"));
            if ($existing && $existing['Id_Membrecia']) {
                mysqli_query($conexion, "DELETE FROM membrecia WHERE Id_Membrecia={$existing['Id_Membrecia']}");
                mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia=NULL WHERE Id_Cliente=$id");
            }
        }

        $msg = "Cliente actualizado.";
    }

    // ENTRENADOR - editar
    if ($mod === 'entrenadores' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $id_esp = !empty($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : "NULL";
        if (!empty($_POST['contrasena'])) {
            $pass = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
            mysqli_query($conexion, "UPDATE entrenador SET Nombre='$nombre',Apellido='$apellido',Telefono='$telefono',Correo='$correo',Contrasena='$pass',Id_Especialidad=$id_esp WHERE Id_Entrenador=$id");
        } else {
            mysqli_query($conexion, "UPDATE entrenador SET Nombre='$nombre',Apellido='$apellido',Telefono='$telefono',Correo='$correo',Id_Especialidad=$id_esp WHERE Id_Entrenador=$id");
        }
        $msg = "Entrenador actualizado.";
    }

    // PRODUCTO / inventario - editar
    if ($mod === 'inventario' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $marca = mysqli_real_escape_string($conexion, $_POST['marca']);
        $cantidad = (int)$_POST['cantidad'];
        $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
        mysqli_query($conexion, "UPDATE producto p JOIN inventario i ON p.Id_Producto=i.Id_Producto SET p.Nombre='$nombre', p.Marca='$marca', i.Cantidad=$cantidad, i.Ubicacion='$ubicacion' WHERE p.Id_Producto=$id");
        $msg = "Producto actualizado.";
    }

    // PROVEEDOR editar
    if ($mod === 'proveedores' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $id_producto = (int)$_POST['id_producto'];
        $precio = (float)$_POST['precio_proveedor'];
        mysqli_query($conexion, "UPDATE proveedor SET Nombre='$nombre', Telefono='$telefono', Correo='$correo', Id_Producto=$id_producto, Precio_Proveedor=$precio WHERE Id_Proveedor=$id");
        $msg = "Proveedor actualizado.";
    }

    // ESPECIALIDAD editar
    if ($mod === 'especialidades' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_especialidad']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        mysqli_query($conexion, "UPDATE especialidad SET Nombre_Especialidad='$nombre', Descripcion='$descripcion' WHERE Id_Especialidad=$id");
        $msg = "Especialidad actualizada.";
    }

    // MEMBRECIAS tipos editar (sin columna Duracion en tipo_membrecia)
    if ($mod === 'membrecias' && $_POST['accion'] === 'editar') {
        $id = (int)$_POST['id'];
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_tipo']);
        $precio = (float)$_POST['precio'];
        $dur = isset($_POST['duracion']) && $_POST['duracion'] !== '' ? (int)$_POST['duracion'] : 30;

        // Actualiza nombre y precio del tipo de membresía
        mysqli_query($conexion, "UPDATE tipo_membrecia SET Nombre_Tipo='$nombre', Precio=$precio WHERE Id_Tipo_Membrecia=$id");

        // Actualiza la duración de todas las membresías asociadas a ese tipo
        mysqli_query($conexion, "UPDATE membrecia SET Duracion=$dur WHERE Id_Tipo_Membrecia=$id");

        $msg = "Tipo de membresía actualizado correctamente.";
    }

    header("Location: update.php?modulo=$mod&ok=" . urlencode($msg));
    exit();
}

$mod = $_GET['modulo'] ?? 'clientes';
$ok = $_GET['ok'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actualizar - Admin</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
  <script>
    function openEdit(mod, id, dataJson){
      const data = JSON.parse(dataJson);
      // abrir formulario de edición sencillo
      let html = `<h3>Editar ${mod}</h3>
        <form method="POST">
          <input type="hidden" name="modulo" value="${mod}">
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id" value="${id}">`;

      if(mod === 'clientes'){
        html += ` <label>Nombre</label><input name="nombre" value="${data.Nombre}" required>
                  <label>Apellido</label><input name="apellido" value="${data.Apellido}" required>
                  <label>Teléfono</label><input name="telefono" value="${data.Telefono}">
                  <label>Correo</label><input name="correo" value="${data.Correo}">
                  <label>Tipo de membresía</label>
                  <select name="id_tipo_membrecia" id="selMemb"> <option value="">--Sin membresía--</option></select>`;
      }
      if(mod === 'entrenadores'){
        html += ` <label>Nombre</label><input name="nombre" value="${data.Nombre}" required>
                  <label>Apellido</label><input name="apellido" value="${data.Apellido}" required>
                  <label>Teléfono</label><input name="telefono" value="${data.Telefono}">
                  <label>Correo</label><input name="correo" value="${data.Correo}">
                  <label>Contraseña (opcional)</label><input name="contrasena" placeholder="Dejar vacío para no cambiar">
                  <label>Especialidad</label>
                  <select name="id_especialidad" id="selEsp"></select>`;
      }
      if(mod === 'inventario'){
        html += ` <label>Nombre</label><input name="nombre" value="${data.Nombre}" required>
                  <label>Marca</label><input name="marca" value="${data.Marca}">
                  <label>Cantidad</label><input type="number" name="cantidad" value="${data.Cantidad}">
                  <label>Ubicación</label><input name="ubicacion" value="${data.Ubicacion}">`;
      }
      if(mod === 'proveedores'){
        html += ` <label>Nombre</label><input name="nombre" value="${data.Nombre}" required>
                  <label>Teléfono</label><input name="telefono" value="${data.Telefono}">
                  <label>Correo</label><input name="correo" value="${data.Correo}">
                  <label>Producto</label><select name="id_producto" id="selProd"></select>
                  <label>Precio proveedor</label><input type="number" step="0.01" name="precio_proveedor" value="${data.Precio_Proveedor}">`;
      }
      if(mod === 'especialidades'){
        html += ` <label>Nombre</label><input name="nombre_especialidad" value="${data.Nombre_Especialidad}" required>
                  <label>Descripción</label><textarea name="descripcion">${data.Descripcion || ''}</textarea>`;
      }
      if(mod === 'membrecias'){
        html += ` <label>Nombre</label><input name="nombre_tipo" value="${data.Nombre_Tipo}" required>
                  <label>Precio</label><input type="number" step="0.01" name="precio" value="${data.Precio}">
                  <label>Duración (días)</label><input type="number" name="duracion" value="${data.Duracion || 30}">`;
      }

      html += `<button class="btn-save">Actualizar</button></form>`;

      // mostrar en modal simple
      const modal = document.getElementById('modalEdicion');
      document.getElementById('contenidoModal').innerHTML = html;
      modal.style.display = 'flex';

      // cargar opciones dinamicas si aplica
      if(mod === 'clientes'){
        fetch('obtener_membrecias.php').then(r=>r.json()).then(list=>{
          let sel = document.getElementById('selMemb');
          list.forEach(it=>{
            const opt = document.createElement('option');
            opt.value = it.Id_Tipo_Membrecia;
            opt.text = `${it.Nombre_Tipo} - $${it.Precio}`;
            if(it.Id_Tipo_Membrecia == data.Id_Tipo_Membrecia) opt.selected = true;
            sel.appendChild(opt);
          });
        });
      }
      if(mod === 'entrenadores'){
        fetch('obtener_especialidades.php').then(r=>r.json()).then(list=>{
          let sel = document.getElementById('selEsp');
          list.forEach(it=>{
            const opt = document.createElement('option');
            opt.value = it.Id_Especialidad;
            opt.text = it.Nombre_Especialidad;
            if(it.Id_Especialidad == data.Id_Especialidad) opt.selected = true;
            sel.appendChild(opt);
          });
        });
      }
      if(mod === 'proveedores'){
        fetch('obtener_productos.php').then(r=>r.json()).then(list=>{
          let sel = document.getElementById('selProd');
          list.forEach(it=>{
            const opt = document.createElement('option');
            opt.value = it.Id_Producto;
            opt.text = it.Nombre;
            if(it.Id_Producto == data.Id_Producto) opt.selected = true;
            sel.appendChild(opt);
          });
        });
      }
    }
    function closeModal(){
      document.getElementById('modalEdicion').style.display='none';
    }
  </script>
  <style>.modal{display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);position:fixed;left:0;top:0;width:100%;height:100%;z-index:999}.modal-content{background:white;padding:12px;border-radius:8px;width:90%;max-width:700px}</style>
</head>
<body>
  <div class="header">
    <h1>Actualizar registros</h1>
    <a href="index_admin.php" class="logout">Volver</a>
  </div>
  <div class="layout">
    <aside class="sidebar">
      <a href="update.php?modulo=clientes">Clientes</a>
      <a href="update.php?modulo=entrenadores">Entrenadores</a>
      <a href="update.php?modulo=inventario">Inventario</a>
      <a href="update.php?modulo=proveedores">Proveedores</a>
      <a href="update.php?modulo=especialidades">Especialidades</a>
      <a href="update.php?modulo=membrecias">Tipos Membresía</a>
    </aside>

    <main class="main">
      <div class="crud-section">
        <?php if($ok = $_GET['ok'] ?? ''): ?>
          <div style="padding:10px;background:#e6ffed;border:1px solid #b6f2c0;margin-bottom:12px;"><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>

        <h2><?php echo ucfirst($mod); ?></h2>

        <?php
        if ($mod === 'clientes') {
          $res = mysqli_query($conexion, "SELECT c.*, m.Id_Tipo_Membrecia AS Id_Tipo_Membrecia FROM cliente c LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Tel</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr>
                    <td>{$r['Id_Cliente']}</td>
                    <td>{$r['Nombre']} {$r['Apellido']}</td>
                    <td>{$r['Correo']}</td>
                    <td>{$r['Telefono']}</td>
                    <td><button class='btn-edit' onclick='openEdit(\"clientes\", {$r['Id_Cliente']}, `{$data}`)'>Editar</button></td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'entrenadores') {
          $res = mysqli_query($conexion, "SELECT * FROM entrenador");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Tel</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr>
                    <td>{$r['Id_Entrenador']}</td>
                    <td>{$r['Nombre']} {$r['Apellido']}</td>
                    <td>{$r['Correo']}</td>
                    <td>{$r['Telefono']}</td>
                    <td><button class='btn-edit' onclick='openEdit(\"entrenadores\", {$r['Id_Entrenador']}, `{$data}`)'>Editar</button></td>
                  </tr>";
          }
          echo "</table>";
        }

        if ($mod === 'inventario') {
          $res = mysqli_query($conexion, "SELECT p.Id_Producto, p.Nombre, p.Marca, i.Cantidad, i.Ubicacion FROM producto p JOIN inventario i ON p.Id_Producto=i.Id_Producto");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Cantidad</th><th>Ubicación</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $r['Cantidad'] = $r['Cantidad'] ?? 0;
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr><td>{$r['Id_Producto']}</td><td>{$r['Nombre']}</td><td>{$r['Marca']}</td><td>{$r['Cantidad']}</td><td>{$r['Ubicacion']}</td><td><button class='btn-edit' onclick='openEdit(\"inventario\", {$r['Id_Producto']}, `{$data}`)'>Editar</button></td></tr>";
          }
          echo "</table>";
        }

        if ($mod === 'proveedores') {
          $res = mysqli_query($conexion, "SELECT pr.*, p.Nombre as Producto FROM proveedor pr JOIN producto p ON pr.Id_Producto=p.Id_Producto");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Tel</th><th>Correo</th><th>Producto</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr><td>{$r['Id_Proveedor']}</td><td>{$r['Nombre']}</td><td>{$r['Telefono']}</td><td>{$r['Correo']}</td><td>{$r['Producto']}</td><td><button class='btn-edit' onclick='openEdit(\"proveedores\", {$r['Id_Proveedor']}, `{$data}`)'>Editar</button></td></tr>";
          }
          echo "</table>";
        }

        if ($mod === 'especialidades') {
          $res = mysqli_query($conexion, "SELECT * FROM especialidad");
          echo "<table><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr><td>{$r['Id_Especialidad']}</td><td>{$r['Nombre_Especialidad']}</td><td>{$r['Descripcion']}</td><td><button class='btn-edit' onclick='openEdit(\"especialidades\", {$r['Id_Especialidad']}, `{$data}`)'>Editar</button></td></tr>";
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

          echo "<table><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Duración (días)</th><th>Acción</th></tr>";
          while($r=mysqli_fetch_assoc($res)){
            $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
            echo "<tr>
              <td>{$r['Id_Tipo_Membrecia']}</td>
              <td>{$r['Nombre_Tipo']}</td>
              <td>\${$r['Precio']}</td>
              <td>{$r['Duracion']}</td>
              <td><button class='btn-edit' onclick='openEdit(\"membrecias\", {$r['Id_Tipo_Membrecia']}, `{$data}`)'>Editar</button></td>
            </tr>";
          }
          echo "</table>";
        }
        ?>
      </div>
    </main>
  </div>

  <div id="modalEdicion" class="modal">
    <div class="modal-content" id="contenidoModal">
      <button class="close" onclick="closeModal()">×</button>
    </div>
  </div>
</body>
</html>
