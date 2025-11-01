<?php
session_start();
include '../Conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Detectar si viene desde fetch() o desde un form normal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si el usuario quiere vaciar el carrito
    if (isset($_POST['vaciar'])) {
        $_SESSION['carrito'] = [];
        exit("Carrito vaciado");
    }

    // Agregar producto al carrito
    if (isset($_POST['id_producto']) && isset($_POST['cantidad'])) {
        $id = (int)$_POST['id_producto'];
        $cantidad = (int)$_POST['cantidad'];

        $query = mysqli_query($conexion, "SELECT * FROM producto WHERE Id_Producto = $id");
        $producto = mysqli_fetch_assoc($query);

        if ($producto) {
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$id] = [
                    'nombre' => $producto['Nombre'],
                    'precio' => $producto['Precio'],
                    'cantidad' => $cantidad
                ];
            }
            echo "Producto agregado correctamente";
        } else {
            echo "Producto no encontrado";
        }
        exit; // detener ejecución (evita cargar HTML)
    }
}

// Si no es AJAX, mostrar carrito normalmente
include 'Header.php';

$total = 0;
?>

<link rel="stylesheet" href="../Desing/estilo_tienda.css">

<div class="container mt-4">
  <div class="mb-3">
    <a href="Index.php" class="btn btn-dark">⬅ Volver a la tienda</a>
  </div>

  <h3 class="text-center mb-4">🛍️ Tu Carrito</h3>

  <?php if (empty($_SESSION['carrito'])): ?>
    <div class="alert alert-secondary text-center">
      Tu carrito está vacío 🛒
    </div>
  <?php else: ?>
    <form method="post">
      <table class="table table-dark table-striped align-middle text-center">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($_SESSION['carrito'] as $id => $item): 
            $subtotal = $item['precio'] * $item['cantidad'];
            $total += $subtotal;
          ?>
            <tr>
              <td><?php echo htmlspecialchars($item['nombre']); ?></td>
              <td><?php echo (int)$item['cantidad']; ?></td>
              <td>$<?php echo number_format($item['precio'], 2); ?></td>
              <td>$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="text-end mt-3">
        <h4>Total: <span class="text-success">$<?php echo number_format($total, 2); ?></span></h4>
        <button type="submit" name="vaciar" class="btn btn-danger me-2">Vaciar carrito</button>
        <a href="Pagar.php" class="btn btn-success">Proceder al pago</a>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include 'Footer.php'; ?>



