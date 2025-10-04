<?php
session_start();

if (!isset($_SESSION['id_administrador'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

if (!$conn) {
    die("Error: No se pudo conectar a la base de datos");
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel Admin - Titán GYM</title>
  <link rel="stylesheet" href="Desing/admin.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    // Función para hacer scroll automático al formulario (SOLO para Agregar/Ver)
    function scrollToForm() {
      setTimeout(function() {
        document.querySelector('.crud-section').scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
        });
      }, 100);
    }

    // Función para manejar el botón Gestionar - SIN SCROLL
    function handleGestionar(modulo) {
      // Solo cambiar la URL para mostrar el listado
      window.location.href = `?modulo=${modulo}&accion=leer`;
    }
  </script>
</head>
<body>
  <header class="site-header">
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="menu-btn">
      <span class="hamb"></span>
      <span class="hamb"></span>
      <span class="hamb"></span>
    </label>
    <h1 class="gym-title"><i class="fas fa-dumbbell"></i> Panel Admin - Titán GYM</h1>
    <nav class="main-nav">
      <ul>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        <li><a href="#"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i> Estadísticas</a></li>
      </ul>
    </nav>
  </header>

  <main class="container">
    <section class="hero">
      <h2>Bienvenido al Dashboard</h2>
      <p>Gestiona todos los aspectos de tu gimnasio desde un solo lugar</p>
    </section>

    <section class="plans">
      <!-- CLIENTES -->
      <article class="plan-card">
        <h3>Clientes</h3>
        <?php
        try {
          $res = $conn->query("SELECT COUNT(*) AS total FROM cliente");
          if ($res) {
            $row = $res->fetch_assoc();
            echo "<p>Total registrados: <strong>".$row['total']."</strong></p>";
          } else {
            echo "<p>Total registrados: <strong>0</strong></p>";
          }
        } catch (Exception $e) {
          echo "<p>Total registrados: <strong>Error</strong></p>";
        }
        ?>
        <button class="cta" onclick="handleGestionar('clientes')">Gestionar</button>
        <details data-modulo="clientes" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'clientes') ? 'open' : ''; ?>>
          <summary style="display:none;">Gestionar Clientes</summary>
          <div class="crud-menu">
            <a href="?modulo=clientes&accion=crear" onclick="scrollToForm()"><button>Crear Cliente</button></a>
            <a href="?modulo=clientes&accion=leer" onclick="scrollToForm()"><button>Ver Clientes</button></a>
          </div>
        </details>
      </article>

      <!-- ENTRENADORES -->
      <article class="plan-card">
        <h3>Entrenadores</h3>
        <?php
        try {
          $res = $conn->query("SELECT COUNT(*) AS total FROM entrenador");
          if ($res) {
            $row = $res->fetch_assoc();
            echo "<p>Total registrados: <strong>".$row['total']."</strong></p>";
          } else {
            echo "<p>Total registrados: <strong>0</strong></p>";
          }
        } catch (Exception $e) {
          echo "<p>Total registrados: <strong>Error</strong></p>";
        }
        ?>
        <button class="cta" onclick="handleGestionar('entrenadores')">Gestionar</button>
        <details data-modulo="entrenadores" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'entrenadores') ? 'open' : ''; ?>>
          <summary style="display:none;">Gestionar Entrenadores</summary>
          <div class="crud-menu">
            <a href="?modulo=entrenadores&accion=crear" onclick="scrollToForm()"><button>Crear Entrenador</button></a>
            <a href="?modulo=entrenadores&accion=leer" onclick="scrollToForm()"><button>Ver Entrenadores</button></a>
          </div>
        </details>
      </article>

      <!-- INVENTARIO -->
      <article class="plan-card">
        <h3>Inventario</h3>
        <?php
        try {
          $res = $conn->query("SELECT COUNT(*) AS total FROM producto");
          if ($res) {
            $row = $res->fetch_assoc();
            echo "<p>Productos: <strong>".$row['total']."</strong></p>";
          } else {
            echo "<p>Productos: <strong>0</strong></p>";
          }
        } catch (Exception $e) {
          echo "<p>Productos: <strong>Error</strong></p>";
        }
        ?>
        <button class="cta" onclick="handleGestionar('inventario')">Gestionar</button>
        <details data-modulo="inventario" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'inventario') ? 'open' : ''; ?>>
          <summary style="display:none;">Gestionar Inventario</summary>
          <div class="crud-menu">
            <a href="?modulo=inventario&accion=crear" onclick="scrollToForm()"><button>Agregar Producto</button></a>
            <a href="?modulo=inventario&accion=leer" onclick="scrollToForm()"><button>Ver Productos</button></a>
          </div>
        </details>
      </article>

      <!-- PROVEEDORES -->
      <article class="plan-card">
        <h3>Proveedores</h3>
        <?php
        try {
          $res = $conn->query("SELECT COUNT(*) AS total FROM proveedor");
          if ($res) {
            $row = $res->fetch_assoc();
            echo "<p>Registrados: <strong>".$row['total']."</strong></p>";
          } else {
            echo "<p>Registrados: <strong>0</strong></p>";
          }
        } catch (Exception $e) {
          echo "<p>Registrados: <strong>Error</strong></p>";
        }
        ?>
        <button class="cta" onclick="handleGestionar('proveedores')">Gestionar</button>
        <details data-modulo="proveedores" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'proveedores') ? 'open' : ''; ?>>
          <summary style="display:none;">Gestionar Proveedores</summary>
          <div class="crud-menu">
            <a href="?modulo=proveedores&accion=crear" onclick="scrollToForm()"><button>Crear Proveedor</button></a>
            <a href="?modulo=proveedores&accion=leer" onclick="scrollToForm()"><button>Ver Proveedores</button></a>
          </div>
        </details>
      </article>
    </section>

    <!-- SECCIÓN CRUD -->
    <div class="crud-section">
      <?php 
      if(isset($_GET['modulo'])){
        $modulo = $_GET['modulo'];
        $accion = $_GET['accion'] ?? 'leer';

        if (!$conn) {
          echo "<div class='alert alert-error'>Error: No hay conexión a la base de datos</div>";
        } else {
          /* ------------------ CLIENTES ------------------ */
          if($modulo === "clientes"){
            echo "<h3>Gestión de Clientes</h3>";
            
            if($accion === 'crear'){
              echo '<form method="POST" action="?modulo=clientes&accion=guardar">';
              echo '<div class="form-group"><label>Nombre:</label><input type="text" name="nombre" required></div>';
              echo '<div class="form-group"><label>Apellido:</label><input type="text" name="apellido" required></div>';
              echo '<div class="form-group"><label>Teléfono:</label><input type="text" name="telefono"></div>';
              echo '<div class="form-group"><label>Correo:</label><input type="email" name="correo"></div>';
              echo '<div class="form-group"><label>Contraseña:</label><input type="password" name="contrasena" required></div>';
              echo '<div class="form-group"><label>Membresía:</label>';
              echo '<select name="id_membrecia">';
              echo '<option value="">Sin membresía</option>';
              // Obtener membresías disponibles
              $membresias = $conn->query("
                SELECT m.Id_Membrecia, tm.Nombre_Tipo, tm.Precio, m.Duracion 
                FROM membrecia m 
                JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
                ORDER BY tm.Nombre_Tipo, m.Duracion
              ");
              if($membresias && $membresias->num_rows > 0){
                while($memb = $membresias->fetch_assoc()){
                  echo '<option value="'.$memb['Id_Membrecia'].'">'.
                       $memb['Nombre_Tipo'].' - $'.number_format($memb['Precio'], 2).
                       ' ('.$memb['Duracion'].' días)</option>';
                }
              } else {
                echo '<option value="">No hay membresías disponibles</option>';
              }
              echo '</select></div>';
              echo '<button type="submit" class="btn btn-primary">Guardar Cliente</button>';
              echo '<a href="?modulo=clientes&accion=leer" class="btn">Cancelar</a>';
              echo '</form>';
            }
            elseif($accion === 'guardar'){
              $nombre = $_POST['nombre'];
              $apellido = $_POST['apellido'];
              $telefono = $_POST['telefono'];
              $correo = $_POST['correo'];
              $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
              $id_membrecia = $_POST['id_membrecia'] ?: NULL;
              
              // Obtener el ID correcto para cliente
              $tipo_query = $conn->query("SELECT Id_Tipo FROM tipo_usuario WHERE Tipo LIKE '%cliente%' OR Tipo LIKE '%Cliente%' LIMIT 1");
              if ($tipo_query && $tipo_query->num_rows > 0) {
                  $tipo_row = $tipo_query->fetch_assoc();
                  $id_tipo = $tipo_row['Id_Tipo'];
              } else {
                  // Si no encuentra, usar ID 3 como fallback
                  $id_tipo = 3;
              }

              $stmt = $conn->prepare("INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo, Id_Membrecia) VALUES (?, ?, ?, ?, ?, ?, ?)");
              $stmt->bind_param("sssssii", $nombre, $apellido, $telefono, $correo, $contrasena, $id_tipo, $id_membrecia);
              
              if($stmt->execute()){
                echo "<div class='alert alert-success'>Cliente creado exitosamente</div>";
              } else {
                echo "<div class='alert alert-error'>Error al crear cliente: ".$conn->error."</div>";
              }
              $stmt->close();
              echo '<a href="?modulo=clientes&accion=leer" class="btn">Ver Clientes</a>';
            }
            elseif($accion === 'leer'){
              echo '<a href="?modulo=clientes&accion=crear" class="btn btn-primary">Crear Nuevo Cliente</a>';
              
              $clientes = $conn->query("SELECT c.*, tm.Nombre_Tipo as Membresia 
                                      FROM cliente c 
                                      LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia 
                                      LEFT JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia");
              
              if($clientes && $clientes->num_rows > 0){
                echo '<table>';
                echo '<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Correo</th><th>Membresía</th><th>Acciones</th></tr>';
                while($cliente = $clientes->fetch_assoc()){
                  echo '<tr>';
                  echo '<td>'.$cliente['Id_Cliente'].'</td>';
                  echo '<td>'.$cliente['Nombre'].'</td>';
                  echo '<td>'.$cliente['Apellido'].'</td>';
                  echo '<td>'.$cliente['Telefono'].'</td>';
                  echo '<td>'.$cliente['Correo'].'</td>';
                  echo '<td>'.($cliente['Membresia'] ? $cliente['Membresia'] : 'Sin membresía').'</td>';
                  echo '<td class="actions">';
                  echo '<a href="?modulo=clientes&accion=editar&id='.$cliente['Id_Cliente'].'" class="btn btn-warning">Editar</a>';
                  echo '<a href="?modulo=clientes&accion=eliminar&id='.$cliente['Id_Cliente'].'" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro?\')">Eliminar</a>';
                  echo '</td>';
                  echo '</tr>';
                }
                echo '</table>';
              } else {
                echo '<p>No hay clientes registrados.</p>';
              }
            }
            elseif($accion === 'eliminar'){
              $id = $_GET['id'];
              $stmt = $conn->prepare("DELETE FROM cliente WHERE Id_Cliente = ?");
              $stmt->bind_param("i", $id);
              if($stmt->execute()){
                echo "<div class='alert alert-success'>Cliente eliminado exitosamente</div>";
              } else {
                echo "<div class='alert alert-error'>Error al eliminar cliente: ".$conn->error."</div>";
              }
              $stmt->close();
              echo '<a href="?modulo=clientes&accion=leer" class="btn">Volver a la lista</a>';
            }
          }

          /* ------------------ ENTRENADORES ------------------ */
          if($modulo === "entrenadores"){
            echo "<h3>Gestión de Entrenadores</h3>";
            
            if($accion === 'crear'){
              echo '<form method="POST" action="?modulo=entrenadores&accion=guardar">';
              echo '<div class="form-group"><label>Nombre:</label><input type="text" name="nombre" required></div>';
              echo '<div class="form-group"><label>Apellido:</label><input type="text" name="apellido" required></div>';
              echo '<div class="form-group"><label>Teléfono:</label><input type="text" name="telefono"></div>';
              echo '<div class="form-group"><label>Correo:</label><input type="email" name="correo"></div>';
              echo '<div class="form-group"><label>Especialidad:</label>';
              echo '<select name="especialidad">';
              echo '<option value="1">Fitness</option>';
              echo '<option value="2">Yoga</option>';
              echo '<option value="3">CrossFit</option>';
              echo '<option value="4">Pilates</option>';
              echo '<option value="5">Boxeo</option>';
              echo '</select></div>';
              echo '<div class="form-group"><label>Fecha de Contratación:</label><input type="date" name="fecha_contratacion" required></div>';
              echo '<div class="form-group"><label>Sueldo:</label><input type="number" step="0.01" name="sueldo" required></div>';
              echo '<div class="form-group"><label>Horario:</label>';
              echo '<select name="horario">';
              echo '<option value="Matutino">Matutino (6:00 - 14:00)</option>';
              echo '<option value="Vespertino">Vespertino (14:00 - 22:00)</option>';
              echo '<option value="Completo">Completo</option>';
              echo '</select></div>';
              echo '<button type="submit" class="btn btn-primary">Guardar Entrenador</button>';
              echo '<a href="?modulo=entrenadores&accion=leer" class="btn">Cancelar</a>';
              echo '</form>';
            }
            elseif($accion === 'guardar'){
              $nombre = $_POST['nombre'];
              $apellido = $_POST['apellido'];
              $telefono = $_POST['telefono'];
              $correo = $_POST['correo'];
              $especialidad = $_POST['especialidad'];
              $fecha_contratacion = $_POST['fecha_contratacion'];
              $sueldo = $_POST['sueldo'];
              $horario = $_POST['horario'];
              
              // Obtener el ID correcto para entrenador
              $tipo_query = $conn->query("SELECT Id_Tipo FROM tipo_usuario WHERE Tipo LIKE '%entrenador%' OR Tipo LIKE '%Entrenador%' LIMIT 1");
              if ($tipo_query && $tipo_query->num_rows > 0) {
                  $tipo_row = $tipo_query->fetch_assoc();
                  $id_tipo = $tipo_row['Id_Tipo'];
              } else {
                  // Si no encuentra, usar ID 2 como fallback
                  $id_tipo = 2;
              }

              $stmt = $conn->prepare("INSERT INTO entrenador (Nombre, Apellido, Telefono, Correo, Id_Especialidad, Id_Tipo, Fecha_Contratacion, Sueldo, Horario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
              $stmt->bind_param("ssssiisds", $nombre, $apellido, $telefono, $correo, $especialidad, $id_tipo, $fecha_contratacion, $sueldo, $horario);
              
              if($stmt->execute()){
                echo "<div class='alert alert-success'>Entrenador creado exitosamente</div>";
              } else {
                echo "<div class='alert alert-error'>Error al crear entrenador: ".$conn->error."</div>";
              }
              $stmt->close();
              echo '<a href="?modulo=entrenadores&accion=leer" class="btn">Ver Entrenadores</a>';
            }
            elseif($accion === 'leer'){
              echo '<a href="?modulo=entrenadores&accion=crear" class="btn btn-primary">Crear Nuevo Entrenador</a>';
              
              $entrenadores = $conn->query("SELECT * FROM entrenador");
              
              if($entrenadores && $entrenadores->num_rows > 0){
                echo '<table>';
                echo '<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Correo</th><th>Especialidad</th><th>Fecha Contratación</th><th>Sueldo</th><th>Horario</th><th>Acciones</th></tr>';
                while($entrenador = $entrenadores->fetch_assoc()){
                  echo '<tr>';
                  echo '<td>'.$entrenador['Id_Entrenador'].'</td>';
                  echo '<td>'.$entrenador['Nombre'].'</td>';
                  echo '<td>'.$entrenador['Apellido'].'</td>';
                  echo '<td>'.$entrenador['Telefono'].'</td>';
                  echo '<td>'.$entrenador['Correo'].'</td>';
                  echo '<td>'.$entrenador['Id_Especialidad'].'</td>';
                  echo '<td>'.$entrenador['Fecha_Contratacion'].'</td>';
                  echo '<td>$'.number_format($entrenador['Sueldo'], 2).'</td>';
                  echo '<td>'.$entrenador['Horario'].'</td>';
                  echo '<td class="actions">';
                  echo '<a href="?modulo=entrenadores&accion=editar&id='.$entrenador['Id_Entrenador'].'" class="btn btn-warning">Editar</a>';
                  echo '<a href="?modulo=entrenadores&accion=eliminar&id='.$entrenador['Id_Entrenador'].'" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro?\')">Eliminar</a>';
                  echo '</td>';
                  echo '</tr>';
                }
                echo '</table>';
              } else {
                echo '<p>No hay entrenadores registrados.</p>';
              }
            }
          }
        }
      }
      ?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="footer-grid">
      <div class="col">
        <h3><i class="fas fa-dumbbell"></i> Titán GYM</h3>
        <p>Tu camino hacia el éxito fitness comienza aquí.</p>
      </div>
      <div class="col">
        <h3>Contacto</h3>
        <p><i class="fas fa-envelope"></i> info@tiangym.com</p>
        <p><i class="fas fa-phone"></i> 123-456-7890</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Titán GYM. Todos los derechos reservados.</p>
    </div>
  </footer>

  <script>
    // Scroll automático cuando hay un módulo activo
    <?php if(isset($_GET['modulo'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        document.querySelector('.crud-section').scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
        });
      }, 300);
    });
    <?php endif; ?>
  </script>
</body>
</html>