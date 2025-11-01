<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda Titan GYM</title>
  <link rel="stylesheet" href="../Desing/estilo_tienda.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
  <h2 class="m-0">Titan GYM</h2>
  <form class="d-flex" action="Buscar.php" method="get">
    <input class="form-control me-2" type="search" name="q" placeholder="Buscar productos...">
    <button class="btn btn-warning" type="submit">Buscar</button>
  </form>
  <div>
    <a href="Carrito.php" class="btn btn-outline-light me-2">🛒 Carrito</a>
    <a href="../forms/login.php" class="btn btn-outline-light">Iniciar sesión</a>
  </div>
</header>
<div class="container mt-4">
