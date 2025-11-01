<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Conexion.php';
include 'Header.php';

// Obtener término de búsqueda
$busqueda = trim($_GET['q'] ?? '');

$resultado = null;
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($busqueda === '') {
        // Si la barra está vacía, mostramos mensaje
        $mensaje = "Por favor, ingrese el producto que desea buscar.";
    } else {
        // Buscar por nombre o descripción
        $query = "SELECT * FROM producto 
                  WHERE Nombre LIKE '%$busqueda%' 
                  OR Descripcion LIKE '%$busqueda%'";
        $resultado = mysqli_query($conexion, $query);
    }
}
?>

<link rel="stylesheet" href="../Desing/estilo_tienda.css">

<div class="container mt-4">
  <!-- Botón para volver -->
  <div class="mb-3">
    <button onclick="window.location.href='Index.php'" class="btn btn-dark">⬅ Volver a la tienda</button>
  </div>

  <h3 class="text-center text-light mb-4">
    Resultados para <span class="text-warning">"<?php echo htmlspecialchars($busqueda); ?>"</span>
  </h3>

  <!-- Mostrar mensaje si la barra está vacía -->
  <?php if ($mensaje): ?>
    <div class="alert alert-warning text-center fw-bold">
      ⚠️ <?php echo $mensaje; ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
      <?php while ($producto = mysqli_fetch_assoc($resultado)): ?>
        <?php
          // Ruta guardada en la BD
          $rutaImagen = $producto['Ruta_Imagen'];

          // Si la ruta no está vacía y el archivo existe, la usamos
          if (!empty($rutaImagen) && file_exists("../$rutaImagen")) {
              $imagen = "../$rutaImagen";
          } else {
              // Si no, mostramos una imagen por defecto
              $imagen = "../Imagenes_productos/default.png";
          }
        ?>

        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm bg-dark text-light">
            <img src="<?php echo $imagen; ?>" class="card-img-top" alt="Producto" style="height:220px; object-fit:cover;">
            <div class="card-body">
              <h5 class="card-title text-warning"><?php echo htmlspecialchars($producto['Nombre']); ?></h5>
              <p class="card-text"><?php echo substr($producto['Descripcion'], 0, 80) . '...'; ?></p>
              <p class="text-success fw-bold">$<?php echo number_format($producto['Precio'], 2); ?></p>
              <a href="producto.php?id=<?php echo $producto['Id_Producto']; ?>" class="btn btn-danger">Ver detalle</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php elseif ($busqueda !== '' && $resultado && mysqli_num_rows($resultado) === 0): ?>
      <div class="text-center text-muted">
        <p class="fs-5">Producto no encontrado ❌</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'Footer.php'; ?>




