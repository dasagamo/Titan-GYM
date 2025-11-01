<?php
session_start();
include '../Conexion.php';
include 'Header.php';

if (empty($_SESSION['carrito'])) {
  echo "<h4 class='text-center mt-5'>Tu carrito está vacío 😕</h4>";
  include 'footer.php';
  exit;
}

if (!isset($_SESSION['usuario'])) {
  echo "<script>alert('Debes iniciar sesión antes de pagar'); window.location='../forms/login.php';</script>";
  exit;
}

$idCliente = $_SESSION['usuario']['Id_Cliente'];
$total = 0;

foreach ($_SESSION['carrito'] as $item) {
  $total += $item['precio'] * $item['cantidad'];
}

// Aquí podrías registrar el pedido y detalle_pedido
echo "<h3 class='text-center'>Pago realizado con éxito 💳</h3>";
echo "<p class='text-center'>Total pagado: $" . number_format($total, 2) . "</p>";

$_SESSION['carrito'] = [];
include 'Footer.php';
