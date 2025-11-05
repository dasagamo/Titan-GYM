<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Conexion.php';
include 'Header.php';

// Obtener el ID del producto desde la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consultar producto
$query = "SELECT * FROM producto WHERE Id_Producto = $id";
$resultado = mysqli_query($conexion, $query);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    echo "<p style='color:red; text-align:center;'>⚠️ Producto no encontrado.</p>";
    include 'Footer.php';
    exit;
}
?>

<link rel="stylesheet" href="../Desing/estilo_tienda.css">

<div class="container mt-4">
  <!-- 🔙 Botón para volver -->
  <div class="mb-3">
    <button onclick="window.history.back()" class="btn btn-dark">
      ⬅ Volver a la tienda
    </button>
  </div>

  <div class="row">
    <!-- Imagen del producto -->
    <div class="col-md-6 text-center">
      <?php
        $ruta_imagen = !empty($producto['Ruta_Imagen'])
            ? "../" . htmlspecialchars($producto['Ruta_Imagen'])
            : "Imagenes_productos/default.png";
      ?>
      <img src="<?php echo $ruta_imagen; ?>" 
           class="img-fluid rounded shadow-sm" 
           alt="Imagen del producto"
           style="max-height: 400px; object-fit: contain;">
    </div>

    <!-- Información del producto -->
    <div class="col-md-6">
      <h2><?php echo htmlspecialchars($producto['Nombre']); ?></h2>
      <p><?php echo nl2br(htmlspecialchars($producto['Descripcion'])); ?></p>
      <h4 class="text-success">$<?php echo number_format($producto['Precio'], 2); ?></h4>

      <form id="formCarrito" method="post">
        <input type="hidden" name="id_producto" value="<?php echo $producto['Id_Producto']; ?>">
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" value="1" min="1" class="form-control w-25 mb-3">
        <button type="submit" class="btn btn-danger">🛒 Agregar al carrito</button>
      </form>

      <!-- Mensaje de confirmación -->
      <div id="mensaje" class="mt-3" style="display:none; color:#28a745; font-weight:bold;">
        Producto añadido al carrito
      </div>
    </div>
  </div>
</div>

<!-- ✅ Script para añadir sin recargar -->
<script>
document.getElementById('formCarrito').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  fetch('Carrito.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(() => {
    const mensaje = document.getElementById('mensaje');
    mensaje.style.display = 'block';
    setTimeout(() => mensaje.style.display = 'none', 2000);

    // Si hay contador de carrito, lo actualizamos
    const contador = document.getElementById('contadorCarrito');
    if (contador) {
      let num = parseInt(contador.textContent || '0');
      contador.textContent = num + parseInt(formData.get('cantidad'));
      contador.style.display = 'inline';
    }
  });
});
</script>

<?php include 'Footer.php'; ?>


