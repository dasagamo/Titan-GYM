<?php
// index_admin.php
session_start();
if (!isset($_SESSION['id_administrador'])) {
    header('Location: ../forms/login.php');
    exit();
}
require_once "../Conexion.php";

if (!$conexion) die("Error: No se pudo conectar a la base de datos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin - Titán GYM</title>
  <link rel="stylesheet" href="../Desing/admin.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header class="header">
    <h1><i class="fas fa-dumbbell"></i> Panel Admin - Titán GYM</h1>
    <div class="user-info">
      <span>👤 <?php echo $_SESSION['nombre']; ?></span>
      <a class="logout" href="../forms/cerrar_sesion.php">
        <i class="fas fa-sign-out-alt"></i>Cerrar sesión
      </a>
    </div>
  </header>

  <div class="layout">
    <aside class="sidebar">
      <h3>Menú</h3>
      <a href="index_admin.php"><i class="fas fa-home"></i> Inicio</a>
      <a href="create.php"><i class="fas fa-plus-circle"></i> Crear</a>
      <a href="read.php"><i class="fas fa-list"></i> Listar / Leer</a>
      <a href="update.php"><i class="fas fa-pen"></i> Actualizar</a>
      <a href="delete.php"><i class="fas fa-trash"></i> Eliminar</a>
      <a href="funciones.php"><i class="fas fa-cog"></i> Funciones</a>
    </aside>

    <main class="main">
      <!-- Estadísticas Rápidas -->
      <section class="dashboard-section">
        <div class="section-header">
          <h2 class="section-title"><i class="fas fa-chart-bar"></i> Resumen General</h2>
        </div>
        
        <div class="stats-grid">
          <?php
          $modulos = [
            ['clientes','fa-users','Clientes',"SELECT COUNT(*) AS total FROM cliente"],
            ['entrenadores','fa-dumbbell','Entrenadores',"SELECT COUNT(*) AS total FROM entrenador"],
            ['inventario','fa-boxes','Productos',"SELECT COUNT(*) AS total FROM producto"],
            ['proveedores','fa-truck','Proveedores',"SELECT COUNT(*) AS total FROM proveedor"],
            ['especialidades','fa-certificate','Especialidades',"SELECT COUNT(*) AS total FROM especialidad"],
            ['membrecias','fa-crown','Membresías',"SELECT COUNT(*) AS total FROM tipo_membrecia"]
          ];
          
          foreach($modulos as $m){
            $r = mysqli_fetch_assoc(mysqli_query($conexion, $m[3]));
            echo "<div class='stat-card'>
                    <div class='stat-label'>{$m[2]}</div>
                    <div class='stat-value'>{$r['total']}</div>
                  </div>";
          }
          ?>
        </div>
      </section>

      <!-- Tabla de Módulos -->
      <section class="dashboard-section">
        <div class="data-table-container">
          <div class="table-header">
            <h3 class="table-title"><i class="fas fa-table"></i> Gestión de Módulos</h3>
            <div class="table-actions">
              <a href="create.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo
              </a>
            </div>
          </div>
          
          <table class="data-table modules-table">
            <thead>
              <tr>
                <th>Módulo</th>
                <th>Total Registros</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach($modulos as $m){
                $r = mysqli_fetch_assoc(mysqli_query($conexion, $m[3]));
                echo "<tr>
                        <td>
                          <div class='module-name'>
                            <i class='fas {$m[1]}'></i>
                            <span>{$m[2]}</span>
                          </div>
                        </td>
                        <td>
                          <span class='total-count'>{$r['total']}</span>
                        </td>
                        <td>
                          <div class='table-actions-cell'>
                            <a href='create.php?modulo={$m[0]}' class='btn btn-primary btn-sm'>
                              <i class='fas fa-plus'></i>Añadir
                            </a>
                            <a href='read.php?modulo={$m[0]}' class='btn btn-outline btn-sm'>
                              <i class='fas fa-cog'></i>Gestionar
                            </a>
                          </div>
                        </td>
                      </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Información del Panel -->
      <section class="crud-section">
        <h2 class="titulo-crud"><i class="fas fa-home"></i> Panel Principal</h2>
        <p>Usa el menú lateral para crear, listar, actualizar o eliminar entidades. La página <strong>Funciones</strong> contiene las acciones especiales (generar/inactivar QR, etc.).</p>
        
        <div class="stats-grid mb-3">
          <div class="stat-card">
            <div class="stat-label">Sesión Activa</div>
            <div class="stat-value text-success">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Último Acceso</div>
            <div class="stat-value"><?php echo date('d/m/Y H:i'); ?></div>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>