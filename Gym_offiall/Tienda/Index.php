<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Conexion.php';
include 'Header.php';

// Consultar todos los productos
$query = "SELECT * FROM producto";
$resultado = mysqli_query($conexion, $query);
?>

<link rel="stylesheet" href="../Desing/estilo_tienda.css">

<h3 class="text-center mb-4">Bienvenido</h3>

<div class="row">
<?php while ($producto = mysqli_fetch_assoc($resultado)): ?>
  <?php
    // Si el producto no tiene imagen, usamos una por defecto
    $ruta_imagen = !empty($producto['Ruta_Imagen']) ? $producto['Ruta_Imagen'] : 'Tienda/Imagenes_productos/default.png';
  ?>
  <div class="col-md-4 mb-4">
    <div class="card h-100 shadow-sm">
      <img 
        src="../<?php echo htmlspecialchars($ruta_imagen); ?>" 
        class="card-img-top" 
        alt="Imagen del producto"
        style="object-fit: cover; height: 220px; border-radius: 8px;"
      >
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($producto['Nombre']); ?></h5>
        <p class="card-text text-muted">
          <?php echo htmlspecialchars(substr($producto['Descripcion'], 0, 80)) . '...'; ?>
        </p>
        <p class="text-success fw-bold">
          $<?php echo number_format($producto['Precio'], 2); ?>
        </p>
        <a href="Producto.php?id=<?php echo $producto['Id_Producto']; ?>" class="btn btn-primary">
          Ver Detalle
        </a>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>

<?php include 'Footer.php'; ?>


