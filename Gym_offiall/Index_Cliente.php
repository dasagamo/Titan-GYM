<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Titan GYM</title>
  <link rel="stylesheet" href="Desing/Style_Index.css?v=<?php echo time(); ?>">

  <!-- Íconos de redes -->
  <script src="https://kit.fontawesome.com/6c5b55f7a3.js" crossorigin="anonymous"></script>
</head>
<body>

  <!-- Encabezado -->
  <header>
    <div class="menu-btn" id="menu-btn">
      <div></div>
      <div></div>
      <div></div>
    </div>
    <h1>Títan GYM</h1>
  </header>

  <!-- Menú lateral -->
  <nav id="menu">
    <a href="#"><i class="fas fa-tags"></i> Promociones</a>
    <a href="#"><i class="fas fa-store"></i> Tienda</a>
    <a href="#"><i class="fas fa-dumbbell"></i> Clases Promocionales</a>
    <a href="#"><i class="fas fa-user-friends"></i> Clase Personalizada</a>
    <a href="forms/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>

    <!-- Botón especial al final -->
    <a href="forms/Inscripcion.php" class="btn-inscribirse"><i class="fas fa-user-plus"></i> Inscríbete</a>
  </nav>

  <!-- Sección de planes -->
  <section class="planes">
    <div class="plan">
      <h2>Plan Básico</h2>
      <p>Acceso básico al gimnasio</p>
      <span class="precio">$50/mes</span>
    </div>
    <div class="plan destacado">
      <h2>Plan Premium</h2>
      <p>Clases grupales + zona de pesas</p>
      <span class="precio">$80/mes</span>
    </div>
    <div class="plan">
      <h2>Plan Elite</h2>
      <p>Plan premium con entrenador personal</p>
      <span class="precio">$120/mes</span>
    </div>
  </section>

  <!-- Pie de página -->
  <footer>
    <div class="links">
      <a href="#">Contacto</a> |
      <a href="#">Quiénes Somos</a> |
      <a href="#">Preguntas Frecuentes</a>
    </div>
    <p>Síguenos</p>
    <div class="social-icons">
      <a href="#"><i class="fab fa-facebook-f"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
    </div>
  </footer>

  <!-- Script dentro del HTML -->
  <script>
    const menuBtn = document.getElementById('menu-btn');
    const menu = document.getElementById('menu');

    // Alternar el menú
    menuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      menu.classList.toggle('active');
      menuBtn.classList.toggle('open');
    });

    // Cerrar el menú al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!menu.contains(e.target) && !menuBtn.contains(e.target)) {
        menu.classList.remove('active');
        menuBtn.classList.remove('open');
      }
    });

    // Cerrar el menú al hacer clic en un enlace
    menu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menu.classList.remove('active');
        menuBtn.classList.remove('open');
      });
    });
  </script>

</body>
</html>