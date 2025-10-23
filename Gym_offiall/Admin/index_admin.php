<?php
// index_admin.php
session_start();
if (!isset($_SESSION['id_administrador'])) {
    header('Location: forms/login.php');
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
    <div>
      <span style="color:#fff;margin-right:12px">👤 <?php echo $_SESSION['nombre']; ?></span>
      <a class="logout" href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <div class="layout">
    <aside class="sidebar">
      <h3>Menú</h3>
      <a href="index_admin.php">Inicio</a>
      <a href="create.php"><i class="fas fa-plus-circle"></i> Crear (clientes, entrenadores, etc.)</a>
      <a href="read.php"><i class="fas fa-list"></i> Listar / Leer</a>
      <a href="update.php"><i class="fas fa-pen"></i> Actualizar</a>
      <a href="delete.php"><i class="fas fa-trash"></i> Eliminar</a>
      <a href="funciones.php"><i class="fas fa-cog"></i> Funciones (QR, otros)</a>
    </aside>

    <main class="main">
      <section class="plans">
        <?php
        $modulos = [
          ['clientes','fa-users','Clientes',"SELECT COUNT(*) AS total FROM cliente"],
          ['entrenadores','fa-dumbbell','Entrenadores',"SELECT COUNT(*) AS total FROM entrenador"],
          ['inventario','fa-boxes','Inventario (productos)',"SELECT COUNT(*) AS total FROM producto"],
          ['proveedores','fa-truck','Proveedores',"SELECT COUNT(*) AS total FROM proveedor"],
          ['especialidades','fa-certificate','Especialidades',"SELECT COUNT(*) AS total FROM especialidad"],
          ['membrecias','fa-crown','Tipos Membresía',"SELECT COUNT(*) AS total FROM tipo_membrecia"]
        ];
        foreach($modulos as $m){
          $r = mysqli_fetch_assoc(mysqli_query($conexion, $m[3]));
          echo "<article class='plan-card'>
                  <h3><i class='fas {$m[1]}'></i> {$m[2]}</h3>
                  <p>Total registrados: <strong>{$r['total']}</strong></p>
                  <div>
                    <a class='cta' href='create.php?modulo={$m[0]}'>Añadir</a>
                    <a class='cta' href='read.php?modulo={$m[0]}'>Gestionar</a>
                  </div>
                </article>";
        }
        ?>
      </section>

      <div class="crud-section">
        <h2 class="titulo-crud"><i class="fas fa-home"></i> Panel principal</h2>
        <p>Usa el menú lateral para crear, listar, actualizar o eliminar entidades. La página <strong>Funciones</strong> contiene las acciones especiales (generar/inactivar QR, etc.).</p>
      </div>
    </main>
  </div>
</body>
</html>
