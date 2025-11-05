<?php
// update.php
session_start();
if (!isset($_SESSION['Id_Admin'])) { header('Location: ../forms/login.php'); exit(); }
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

        // Actualizar datos básicos del cliente
        mysqli_query($conexion, "UPDATE cliente SET Nombre='$nombre', Apellido='$apellido', Telefono='$telefono', Correo='$correo' WHERE Id_Cliente=$id");

        // Gestión de membresía
        if ($id_tipo) {
            // Consulta para verificar membresía existente
            $existing = mysqli_fetch_assoc(mysqli_query($conexion, 
                "SELECT m.Id_Membrecia, m.Fecha_Inicio, m.Fecha_Fin, m.Duracion 
                 FROM cliente c 
                 LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia 
                 WHERE c.Id_Cliente = $id"));

            // Manejar fechas y duración
            if (!empty($_POST['fecha_inicio'])) {
                $f_inicio = $_POST['fecha_inicio'];
                
                if (!empty($_POST['fecha_fin'])) {
                    $f_fin = $_POST['fecha_fin'];
                    // Calcular duración basada en las fechas proporcionadas
                    $dur = (int) ((strtotime($f_fin) - strtotime($f_inicio)) / (60*60*24));
                } else {
                    // Si no hay fecha fin, usar duración existente o 30 días por defecto
                    $duracion_existente = $existing['Duracion'] ?? 30;
                    $f_fin = date('Y-m-d', strtotime("$f_inicio + $duracion_existente days"));
                    $dur = $duracion_existente;
                }
            } else {
                // Si no hay fecha inicio, usar hoy y 30 días por defecto
                $f_inicio = date('Y-m-d');
                $duracion_existente = $existing['Duracion'] ?? 30;
                $f_fin = date('Y-m-d', strtotime("+ $duracion_existente days"));
                $dur = $duracion_existente;
            }

            // Si se indicó duración manualmente en el formulario, usarla y recalcular fecha fin
            if (!empty($_POST['duracion'])) {
                $dur = (int)$_POST['duracion'];
                $f_fin = date('Y-m-d', strtotime("$f_inicio + $dur days"));
            }

            // Validar duración mínima
            if ($dur <= 0) $dur = 30;

            if ($existing && $existing['Id_Membrecia']) {
                // Actualizar membresía existente
                mysqli_query($conexion, "UPDATE membrecia 
                    SET Id_Tipo_Membrecia = $id_tipo, Duracion = $dur, Fecha_Inicio = '$f_inicio', Fecha_Fin = '$f_fin' 
                    WHERE Id_Membrecia = {$existing['Id_Membrecia']}");
            } else {
                // Crear nueva membresía
                mysqli_query($conexion, "INSERT INTO membrecia (Id_Tipo_Membrecia, Duracion, Fecha_Inicio, Fecha_Fin) 
                    VALUES ($id_tipo, $dur, '$f_inicio', '$f_fin')");
                $id_m = mysqli_insert_id($conexion);
                mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = $id_m WHERE Id_Cliente = $id");
            }
            
            $msg = "Cliente y membresía actualizados correctamente.";
        } else {
            // Eliminar membresía si existe
            $existing = mysqli_fetch_assoc(mysqli_query($conexion, 
                "SELECT Id_Membrecia FROM cliente WHERE Id_Cliente = $id"));
            if ($existing && $existing['Id_Membrecia']) {
                mysqli_query($conexion, "DELETE FROM membrecia WHERE Id_Membrecia = {$existing['Id_Membrecia']}");
                mysqli_query($conexion, "UPDATE cliente SET Id_Membrecia = NULL WHERE Id_Cliente = $id");
            }
            $msg = "Cliente actualizado (sin membresía).";
        }
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
function openEdit(mod, id, dataJson) {
  const data = JSON.parse(dataJson);
  
  const moduleConfig = {
    'clientes': { icon: 'fa-user', title: 'Cliente' },
    'entrenadores': { icon: 'fa-user-tie', title: 'Entrenador' },
    'inventario': { icon: 'fa-box', title: 'Producto' },
    'proveedores': { icon: 'fa-truck', title: 'Proveedor' },
    'especialidades': { icon: 'fa-certificate', title: 'Especialidad' },
    'membrecias': { icon: 'fa-id-card', title: 'Tipo de Membresía' }
  };

  const config = moduleConfig[mod] || { icon: 'fa-edit', title: mod };

  let html = `
    <div class="modal-header">
      <h3><i class="fas ${config.icon}"></i> Editar ${config.title}</h3>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    <div class="modal-body">
      <form method="POST" class="modal-form">
        <input type="hidden" name="modulo" value="${mod}">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" value="${id}">
        <div class="modal-form-grid">
  `;

  if(mod === 'clientes'){
    html += `
      <div class="form-group-enhanced">
        <label class="form-label-enhanced">
          <i class="fas fa-user"></i>Nombre
        </label>
        <input class="form-input-enhanced" name="nombre" value="${data.Nombre || ''}" required>
      </div>
      
      <div class="form-group-enhanced">
        <label class="form-label-enhanced">
          <i class="fas fa-user"></i>Apellido
        </label>
        <input class="form-input-enhanced" name="apellido" value="${data.Apellido || ''}" required>
      </div>
      
      <div class="form-group-enhanced">
        <label class="form-label-enhanced">
          <i class="fas fa-phone"></i>Teléfono
        </label>
        <input class="form-input-enhanced" name="telefono" value="${data.Telefono || ''}">
      </div>
      
      <div class="form-group-enhanced">
        <label class="form-label-enhanced">
          <i class="fas fa-envelope"></i>Correo
        </label>
        <input class="form-input-enhanced" type="email" name="correo" value="${data.Correo || ''}">
      </div>
      
      <div class="form-group-enhanced">
        <label class="form-label-enhanced">
          <i class="fas fa-id-card"></i>Tipo de membresía
        </label>
        <select class="form-select-enhanced" name="id_tipo_membrecia" id="selMemb">
          <option value="">-- Sin membresía --</option>
        </select>
      </div>
      
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-calendar-alt"></i>Datos de Membresía
        </div>
        <div class="form-group-row">
          <div class="form-group-enhanced">
            <label class="form-label-enhanced">Fecha Inicio</label>
            <input type="date" class="form-input-enhanced" name="fecha_inicio" value="${data.Fecha_Inicio || ''}">
          </div>
          <div class="form-group-enhanced">
            <label class="form-label-enhanced">Fecha Fin</label>
            <input type="date" class="form-input-enhanced" name="fecha_fin" value="${data.Fecha_Fin || ''}">
          </div>
        </div>
        <div class="form-group-enhanced">
          <label class="form-label-enhanced">
            <i class="fas fa-calendar-day"></i>Duración (días)
          </label>
          <input type="number" class="form-input-enhanced" name="duracion" value="${data.Duracion || 30}" min="1">
        </div>
      </div>
    `;
  }
  
  if(mod === 'entrenadores'){
    html += `
        <div>
          <label>Nombre</label>
          <input name="nombre" value="${data.Nombre || ''}" required>
        </div>
        
        <div>
          <label>Apellido</label>
          <input name="apellido" value="${data.Apellido || ''}" required>
        </div>
        
        <div>
          <label>Teléfono</label>
          <input name="telefono" value="${data.Telefono || ''}">
        </div>
        
        <div>
          <label>Correo</label>
          <input type="email" name="correo" value="${data.Correo || ''}">
        </div>
        
        <div>
          <label>Contraseña (opcional)</label>
          <input type="password" name="contrasena" placeholder="Dejar vacío para no cambiar">
        </div>
        
        <div>
          <label>Especialidad</label>
          <select name="id_especialidad" id="selEsp">
            <option value="">-- Sin especialidad --</option>
          </select>
        </div>
    `;
  }

  if(mod === 'inventario'){
    html += `
        <div>
          <label>Nombre</label>
          <input name="nombre" value="${data.Nombre || ''}" required>
        </div>
        
        <div>
          <label>Marca</label>
          <input name="marca" value="${data.Marca || ''}">
        </div>
        
        <div>
          <label>Cantidad</label>
          <input type="number" name="cantidad" value="${data.Cantidad || 0}">
        </div>
        
        <div>
          <label>Ubicación</label>
          <input name="ubicacion" value="${data.Ubicacion || ''}">
        </div>
    `;
  }

  if(mod === 'proveedores'){
    html += `
        <div>
          <label>Nombre</label>
          <input name="nombre" value="${data.Nombre || ''}" required>
        </div>
        
        <div>
          <label>Teléfono</label>
          <input name="telefono" value="${data.Telefono || ''}">
        </div>
        
        <div>
          <label>Correo</label>
          <input type="email" name="correo" value="${data.Correo || ''}">
        </div>
        
        <div>
          <label>Producto</label>
          <select name="id_producto" id="selProd" required>
            <option value="">-- Seleccionar producto --</option>
          </select>
        </div>
        
        <div>
          <label>Precio proveedor</label>
          <input type="number" step="0.01" name="precio_proveedor" value="${data.Precio_Proveedor || 0}" required>
        </div>
    `;
  }

  if(mod === 'especialidades'){
    html += `
        <div>
          <label>Nombre</label>
          <input name="nombre_especialidad" value="${data.Nombre_Especialidad || ''}" required>
        </div>
        
        <div>
          <label>Descripción</label>
          <textarea name="descripcion" rows="4">${data.Descripcion || ''}</textarea>
        </div>
    `;
  }

  if(mod === 'membrecias'){
    html += `
        <div>
          <label>Nombre</label>
          <input name="nombre_tipo" value="${data.Nombre_Tipo || ''}" required>
        </div>
        
        <div>
          <label>Precio</label>
          <input type="number" step="0.01" name="precio" value="${data.Precio || 0}" required>
        </div>
        
        <div>
          <label>Duración (días)</label>
          <input type="number" name="duracion" value="${data.Duracion || 30}" min="1">
        </div>
    `;
  }

  html += `
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-enhanced btn-secondary-enhanced" onclick="closeModal()">
            <i class="fas fa-times"></i>Cancelar
          </button>
          <button type="submit" class="btn-enhanced btn-primary-enhanced">
            <i class="fas fa-save"></i>Actualizar
          </button>
        </div>
      </form>
    </div>
  `;

  const modal = document.getElementById('modalEdicion');
  const contenidoModal = document.getElementById('contenidoModal');
  contenidoModal.innerHTML = html;
  modal.style.display = 'flex';

  // Cargar opciones dinámicas
  loadDynamicOptions(mod, data);
}

function loadDynamicOptions(mod, data) {
  console.log('Cargando opciones para:', mod, 'Datos:', data);
  
  if(mod === 'clientes'){
    console.log('Buscando select selMemb...');
    let sel = document.getElementById('selMemb');
    if (!sel) {
      console.error('❌ NO se encontró el elemento selMemb');
      return;
    }
    console.log('✅ Select selMemb encontrado');
    
    // Mostrar loading
    sel.innerHTML = '<option value="">Cargando membresías...</option>';
    
    fetch('obtener_membrecias.php')
      .then(response => {
        console.log('Respuesta del servidor:', response.status, response.statusText);
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        return response.json();
      })
      .then(list => {
        console.log('✅ Membresías cargadas:', list);
        
        if (list.error) {
          throw new Error(list.error);
        }
        
        if (!Array.isArray(list)) {
          throw new Error('La respuesta no es un array válido');
        }
        
        sel.innerHTML = '<option value="">-- Sin membresía --</option>';
        
        list.forEach(it => {
          const opt = document.createElement('option');
          opt.value = it.Id_Tipo_Membrecia;
          opt.textContent = `${it.Nombre_Tipo} - $${it.Precio}`;
          
          // Comparar IDs como números
          const currentId = parseInt(data.Id_Tipo_Membrecia || 0);
          const optionId = parseInt(it.Id_Tipo_Membrecia);
          if (optionId === currentId) {
            opt.selected = true;
            console.log('✅ Membresía seleccionada:', it.Nombre_Tipo);
          }
          
          sel.appendChild(opt);
        });
        
        console.log('✅ Select poblado con', list.length, 'opciones');
      })
      .catch(error => {
        console.error('❌ Error cargando membresías:', error);
        sel.innerHTML = '<option value="">Error al cargar membresías</option>';
        alert('Error al cargar las membresías: ' + error.message);
      });
  }
  
  if(mod === 'entrenadores'){
    fetch('obtener_especialidades.php')
      .then(response => response.json())
      .then(list => {
        let sel = document.getElementById('selEsp');
        if (!sel) return;
        
        sel.innerHTML = '<option value="">-- Sin especialidad --</option>';
        list.forEach(it => {
          const opt = document.createElement('option');
          opt.value = it.Id_Especialidad;
          opt.textContent = it.Nombre_Especialidad;
          if (parseInt(it.Id_Especialidad) === parseInt(data.Id_Especialidad || 0)) {
            opt.selected = true;
          }
          sel.appendChild(opt);
        });
      })
      .catch(error => console.error('Error cargando especialidades:', error));
  }
  
  if(mod === 'proveedores'){
    fetch('obtener_productos.php')
      .then(response => response.json())
      .then(list => {
        let sel = document.getElementById('selProd');
        if (!sel) return;
        
        sel.innerHTML = '<option value="">-- Seleccionar producto --</option>';
        list.forEach(it => {
          const opt = document.createElement('option');
          opt.value = it.Id_Producto;
          opt.textContent = it.Nombre;
          if (parseInt(it.Id_Producto) === parseInt(data.Id_Producto || 0)) {
            opt.selected = true;
          }
          sel.appendChild(opt);
        });
      })
      .catch(error => console.error('Error cargando productos:', error));
  }
}

function closeModal() {
  document.getElementById('modalEdicion').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalEdicion').addEventListener('click', function(e) {
  if (e.target.id === 'modalEdicion') {
    closeModal();
  }
});
  </script>
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
          $res = mysqli_query($conexion, "
              SELECT c.*, 
                     m.Id_Tipo_Membrecia AS Id_Tipo_Membrecia, 
                     m.Fecha_Inicio, 
                     m.Fecha_Fin, 
                     m.Duracion
              FROM cliente c 
              LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia
          ");
          
          echo "<table>
          <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Tel</th>
              <th>Fecha Inicio</th>
              <th>Fecha Fin</th>
              <th>Duración (días)</th>
              <th>Acción</th>
          </tr>";

          while($r=mysqli_fetch_assoc($res)){
              $data = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
              echo "<tr>
                      <td>{$r['Id_Cliente']}</td>
                      <td>{$r['Nombre']} {$r['Apellido']}</td>
                      <td>{$r['Correo']}</td>
                      <td>{$r['Telefono']}</td>
                      <td>".($r['Fecha_Inicio'] ?? '-') ."</td>
                      <td>".($r['Fecha_Fin'] ?? '-') ."</td>
                      <td>".($r['Duracion'] ?? '-') ."</td>
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
