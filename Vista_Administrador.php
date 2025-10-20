<?php
session_start();

if (!isset($_SESSION['id_administrador'])) {
    header("Location: forms/login.php");
    exit();
}

require_once "conexion.php";

if (!$conexion) {
    die("Error: No se pudo conectar a la base de datos");
}

// Procesar acciones de QR
if (isset($_POST['accion_qr']) && isset($_POST['id_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $accion = $_POST['accion_qr'];
    $id_admin = $_SESSION['id_administrador'];
    
    if ($accion === 'generar') {
        // Generar código único
        $codigo = uniqid('GYM', true);
        
        // Insertar en la tabla acceso (adaptada a tu estructura)
        $stmt = mysqli_prepare($conexion, "INSERT INTO acceso (Id_Cliente, Id_Administrador, Codigo, Fecha_Generado, Fecha, Estado_Acceso) VALUES (?, ?, ?, NOW(), NOW(), 'activo')");
        mysqli_stmt_bind_param($stmt, "iis", $id_cliente, $id_admin, $codigo);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['mensaje_exito'] = "Código QR generado exitosamente para el cliente ID: $id_cliente";
            
            // Generar imagen QR
            generarQR($codigo, $id_cliente);
            
        } else {
            $_SESSION['mensaje_error'] = "Error al generar código QR: " . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
        
    } elseif ($accion === 'inhabilitar') {
        // Inhabilitar código actual
        $stmt = mysqli_prepare($conexion, "UPDATE acceso SET Estado_Acceso = 'inactivo' WHERE Id_Cliente = ? AND Estado_Acceso = 'activo'");
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['mensaje_exito'] = "Código QR inhabilitado para el cliente ID: $id_cliente";
        } else {
            $_SESSION['mensaje_error'] = "Error al inhabilitar código QR: " . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
        
    } elseif ($accion === 'borrar') {
        // Eliminar todos los códigos del cliente
        $stmt = mysqli_prepare($conexion, "DELETE FROM acceso WHERE Id_Cliente = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['mensaje_exito'] = "Todos los códigos QR eliminados para el cliente ID: $id_cliente";
            
            // Eliminar archivo QR si existe
            $rutaQR = 'qrcodes/' . $id_cliente . '.png';
            if (file_exists($rutaQR)) {
                unlink($rutaQR);
            }
        } else {
            $_SESSION['mensaje_error'] = "Error al eliminar códigos QR: " . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?modulo=clientes&accion=leer");
    exit();
}

// Función para generar QR usando la librería phpqrcode
function generarQR($codigo, $id_cliente) {
    $rutaQR = 'qrcodes/';
    
    // Crear directorio si no existe
    if (!file_exists($rutaQR)) {
        mkdir($rutaQR, 0777, true);
    }
    
    $archivoQR = $rutaQR . $id_cliente . '.png';
    
    // Incluir y usar la librería phpqrcode
    include 'librerias/phpqrcode/qrlib.php';
    QRcode::png($codigo, $archivoQR, 'L', 8, 2);
}

// Procesar CRUD de clientes
if (isset($_GET['modulo']) && $_GET['modulo'] === 'clientes') {
    $accion = $_GET['accion'] ?? 'leer';
    
    // CREAR CLIENTE
    if ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $id_membrecia = $_POST['id_membrecia'] ?: NULL;
        
        $tipo_query = mysqli_query($conexion, "SELECT Id_Tipo FROM tipo_usuario WHERE Tipo LIKE '%cliente%' LIMIT 1");
        if ($tipo_query && mysqli_num_rows($tipo_query) > 0) {
            $tipo_row = mysqli_fetch_assoc($tipo_query);
            $id_tipo = $tipo_row['Id_Tipo'];
        } else {
            $id_tipo = 3; // Valor por defecto según tu base de datos
        }

        $stmt = mysqli_prepare($conexion, "INSERT INTO cliente (Nombre, Apellido, Telefono, Correo, Contrasena, Id_Tipo, Id_Membrecia) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssii", $nombre, $apellido, $telefono, $correo, $contrasena, $id_tipo, $id_membrecia);
        
        if(mysqli_stmt_execute($stmt)){
            $_SESSION['mensaje_exito'] = "Cliente creado exitosamente";
        } else {
            $_SESSION['mensaje_error'] = "Error al crear cliente: ".mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
        header("Location: ?modulo=clientes&accion=leer");
        exit();
    }
    
    // ELIMINAR CLIENTE
    if ($accion === 'eliminar' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = mysqli_prepare($conexion, "DELETE FROM cliente WHERE Id_Cliente = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if(mysqli_stmt_execute($stmt)){
            $_SESSION['mensaje_exito'] = "Cliente eliminado exitosamente";
        } else {
            $_SESSION['mensaje_error'] = "Error al eliminar cliente: ".mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
        header("Location: ?modulo=clientes&accion=leer");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Titán GYM</title>
    <link rel="icon" href="Imagenes/favicon_1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Desing/admin.css">
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
        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>

        <section class="hero">
            <h2>Bienvenido al Dashboard</h2>
            <p>Gestiona todos los aspectos de tu gimnasio desde un solo lugar</p>
        </section>

        <section class="plans">
            <!-- CLIENTES -->
            <article class="plan-card">
                <h3><i class="fas fa-users"></i> Clientes</h3>
                <?php
                try {
                    $res = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM cliente");
                    if ($res) {
                        $row = mysqli_fetch_assoc($res);
                        echo "<p>Total registrados: <strong>".$row['total']."</strong></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>Total registrados: <strong>Error</strong></p>";
                }
                ?>
                <button class="cta" onclick="handleGestionar('clientes')">Gestionar</button>
                <details data-modulo="clientes" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'clientes') ? 'open' : ''; ?>>
                    <summary style="display:none;">Gestionar Clientes</summary>
                    <div class="crud-menu">
                        <a href="?modulo=clientes&accion=crear"><button>Crear Cliente</button></a>
                        <a href="?modulo=clientes&accion=leer"><button>Ver Clientes</button></a>
                        <a href="?modulo=clientes&accion=gestionar_qr"><button>Gestionar QR</button></a>
                    </div>
                </details>
            </article>

            <!-- ENTRENADORES -->
            <article class="plan-card">
                <h3><i class="fas fa-dumbbell"></i> Entrenadores</h3>
                <?php
                try {
                    $res = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM entrenador");
                    if ($res) {
                        $row = mysqli_fetch_assoc($res);
                        echo "<p>Total registrados: <strong>".$row['total']."</strong></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>Total registrados: <strong>Error</strong></p>";
                }
                ?>
                <button class="cta" onclick="handleGestionar('entrenadores')">Gestionar</button>
                <details data-modulo="entrenadores" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'entrenadores') ? 'open' : ''; ?>>
                    <summary style="display:none;">Gestionar Entrenadores</summary>
                    <div class="crud-menu">
                        <a href="?modulo=entrenadores&accion=crear"><button>Crear Entrenador</button></a>
                        <a href="?modulo=entrenadores&accion=leer"><button>Ver Entrenadores</button></a>
                    </div>
                </details>
            </article>

            <!-- INVENTARIO -->
            <article class="plan-card">
                <h3><i class="fas fa-boxes"></i> Inventario</h3>
                <?php
                try {
                    $res = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM producto");
                    if ($res) {
                        $row = mysqli_fetch_assoc($res);
                        echo "<p>Productos: <strong>".$row['total']."</strong></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>Productos: <strong>Error</strong></p>";
                }
                ?>
                <button class="cta" onclick="handleGestionar('inventario')">Gestionar</button>
                <details data-modulo="inventario" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'inventario') ? 'open' : ''; ?>>
                    <summary style="display:none;">Gestionar Inventario</summary>
                    <div class="crud-menu">
                        <a href="?modulo=inventario&accion=crear"><button>Agregar Producto</button></a>
                        <a href="?modulo=inventario&accion=leer"><button>Ver Productos</button></a>
                    </div>
                </details>
            </article>

            <!-- PROVEEDORES -->
            <article class="plan-card">
                <h3><i class="fas fa-truck"></i> Proveedores</h3>
                <?php
                try {
                    $res = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM proveedor");
                    if ($res) {
                        $row = mysqli_fetch_assoc($res);
                        echo "<p>Registrados: <strong>".$row['total']."</strong></p>";
                    }
                } catch (Exception $e) {
                    echo "<p>Registrados: <strong>Error</strong></p>";
                }
                ?>
                <button class="cta" onclick="handleGestionar('proveedores')">Gestionar</button>
                <details data-modulo="proveedores" <?php echo (isset($_GET['modulo']) && $_GET['modulo'] === 'proveedores') ? 'open' : ''; ?>>
                    <summary style="display:none;">Gestionar Proveedores</summary>
                    <div class="crud-menu">
                        <a href="?modulo=proveedores&accion=crear"><button>Crear Proveedor</button></a>
                        <a href="?modulo=proveedores&accion=leer"><button>Ver Proveedores</button></a>
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

                if (!$conexion) {
                    echo "<div class='alert alert-error'>Error: No hay conexión a la base de datos</div>";
                } else {
                    /* ------------------ CLIENTES ------------------ */
                    if($modulo === "clientes"){
                        
                        if($accion === 'gestionar_qr') {
                            echo "<h3><i class='fas fa-qrcode'></i> Gestión de Códigos QR</h3>";
                            
                            // Obtener clientes con información de sus códigos QR
                            $query = "
                                SELECT c.Id_Cliente, c.Nombre, c.Apellido, 
                                       a.Codigo, a.Fecha_Generado, a.Estado_Acceso,
                                       (SELECT COUNT(*) FROM acceso WHERE Id_Cliente = c.Id_Cliente) as total_codigos
                                FROM cliente c
                                LEFT JOIN acceso a ON c.Id_Cliente = a.Id_Cliente AND a.Estado_Acceso = 'activo'
                                ORDER BY c.Nombre, c.Apellido
                            ";
                            
                            $clientes = mysqli_query($conexion, $query);
                            
                            if($clientes && mysqli_num_rows($clientes) > 0){
                                echo '<table>';
                                echo '<tr><th>ID</th><th>Cliente</th><th>Código QR Actual</th><th>Estado</th><th>Fecha Generación</th><th>Acciones QR</th></tr>';
                                
                                while($cliente = mysqli_fetch_assoc($clientes)){
                                    echo '<tr>';
                                    echo '<td>'.$cliente['Id_Cliente'].'</td>';
                                    echo '<td>'.$cliente['Nombre'].' '.$cliente['Apellido'].'</td>';
                                    echo '<td>';
                                    
                                    if($cliente['Codigo']) {
                                        echo '<div class="qr-info">';
                                        echo '<strong>'.substr($cliente['Codigo'], 0, 15).'...</strong><br>';
                                        echo '<small>Total códigos: '.$cliente['total_codigos'].'</small>';
                                        echo '</div>';
                                        
                                        // Mostrar imagen QR si existe
                                        $rutaQR = 'qrcodes/' . $cliente['Id_Cliente'] . '.png';
                                        if (file_exists($rutaQR)) {
                                            echo '<img src="'.$rutaQR.'" alt="QR Code" style="width: 50px; height: 50px; cursor: pointer;" onclick="verQR('.$cliente['Id_Cliente'].')">';
                                        }
                                    } else {
                                        echo 'Sin código';
                                    }
                                    
                                    echo '</td>';
                                    echo '<td>';
                                    if($cliente['Codigo']) {
                                        echo '<span class="qr-status '.($cliente['Estado_Acceso'] == 'activo' ? 'status-activo' : 'status-inactivo').'">'.strtoupper($cliente['Estado_Acceso']).'</span>';
                                    } else {
                                        echo 'N/A';
                                    }
                                    echo '</td>';
                                    echo '<td>'.($cliente['Fecha_Generado'] ? $cliente['Fecha_Generado'] : 'N/A').'</td>';
                                    echo '<td class="actions">';
                                    echo '<div class="qr-actions">';
                                    
                                    if($cliente['Codigo']) {
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="inhabilitar">
                                                <button type="submit" class="btn-qr btn-inhabilitar" title="Inhabilitar QR"><i class="fas fa-pause"></i></button>
                                              </form>';
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="borrar">
                                                <button type="submit" class="btn-qr btn-borrar" title="Borrar QR" onclick="return confirm(\'¿Estás seguro de eliminar TODOS los códigos QR de este cliente?\')"><i class="fas fa-trash"></i></button>
                                              </form>';
                                    } else {
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="generar">
                                                <button type="submit" class="btn-qr btn-generar" title="Generar QR"><i class="fas fa-plus"></i> Generar</button>
                                              </form>';
                                    }
                                    
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } else {
                                echo '<p>No hay clientes registrados.</p>';
                            }
                            
                        } elseif($accion === 'leer') {
                            echo "<h3><i class='fas fa-users'></i> Gestión de Clientes</h3>";
                            echo '<a href="?modulo=clientes&accion=crear" class="btn btn-primary">Crear Nuevo Cliente</a>';
                            echo '<a href="?modulo=clientes&accion=gestionar_qr" class="btn btn-primary"><i class="fas fa-qrcode"></i> Gestionar QR</a>';
                            
                            // Consulta corregida para obtener clientes
                            $query = "SELECT 
                                        c.Id_Cliente, 
                                        c.Nombre, 
                                        c.Apellido, 
                                        c.Telefono, 
                                        c.Correo,
                                        tm.Nombre_Tipo as Membresia,
                                        a.Codigo, 
                                        a.Estado_Acceso as QR_Estado
                                      FROM cliente c 
                                      LEFT JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia 
                                      LEFT JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
                                      LEFT JOIN acceso a ON c.Id_Cliente = a.Id_Cliente AND a.Estado_Acceso = 'activo'
                                      ORDER BY c.Nombre, c.Apellido";
                            
                            $clientes = mysqli_query($conexion, $query);
                            
                            if($clientes && mysqli_num_rows($clientes) > 0){
                                echo '<table>';
                                echo '<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Correo</th><th>Membresía</th><th>QR</th><th>Acciones</th></tr>';
                                while($cliente = mysqli_fetch_assoc($clientes)){
                                    echo '<tr>';
                                    echo '<td>'.$cliente['Id_Cliente'].'</td>';
                                    echo '<td>'.$cliente['Nombre'].'</td>';
                                    echo '<td>'.$cliente['Apellido'].'</td>';
                                    echo '<td>'.($cliente['Telefono'] ? $cliente['Telefono'] : 'N/A').'</td>';
                                    echo '<td>'.($cliente['Correo'] ? $cliente['Correo'] : 'N/A').'</td>';
                                    echo '<td>'.($cliente['Membresia'] ? $cliente['Membresia'] : 'Sin membresía').'</td>';
                                    echo '<td>';
                                    if($cliente['Codigo']) {
                                        echo '<span class="qr-status status-activo" title="'.$cliente['Codigo'].'">ACTIVO</span>';
                                    } else {
                                        echo '<span class="qr-status status-inactivo">SIN QR</span>';
                                    }
                                    echo '</td>';
                                    echo '<td class="actions">';
                                    echo '<div class="qr-actions">';
                                    
                                    if($cliente['Codigo']) {
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="inhabilitar">
                                                <button type="submit" class="btn-qr btn-inhabilitar" title="Inhabilitar QR"><i class="fas fa-pause"></i></button>
                                              </form>';
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="borrar">
                                                <button type="submit" class="btn-qr btn-borrar" title="Borrar QR" onclick="return confirm(\'¿Estás seguro?\')"><i class="fas fa-trash"></i></button>
                                              </form>';
                                    } else {
                                        echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_cliente" value="'.$cliente['Id_Cliente'].'">
                                                <input type="hidden" name="accion_qr" value="generar">
                                                <button type="submit" class="btn-qr btn-generar" title="Generar QR"><i class="fas fa-plus"></i></button>
                                              </form>';
                                    }
                                    
                                    echo '<a href="?modulo=clientes&accion=editar&id='.$cliente['Id_Cliente'].'" class="btn btn-warning">Editar</a>';
                                    echo '<a href="?modulo=clientes&accion=eliminar&id='.$cliente['Id_Cliente'].'" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro?\')">Eliminar</a>';
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } else {
                                echo '<p>No hay clientes registrados.</p>';
                                // Para debugging, muestra el error SQL si existe
                                if (!$clientes) {
                                    echo '<p>Error en la consulta: ' . mysqli_error($conexion) . '</p>';
                                }
                            }
                            
                        } elseif($accion === 'crear') {
                            echo "<h3><i class='fas fa-user-plus'></i> Crear Nuevo Cliente</h3>";
                            echo '<form method="POST" action="?modulo=clientes&accion=crear">';
                            echo '<div class="form-group"><label>Nombre:</label><input type="text" name="nombre" required></div>';
                            echo '<div class="form-group"><label>Apellido:</label><input type="text" name="apellido" required></div>';
                            echo '<div class="form-group"><label>Teléfono:</label><input type="text" name="telefono"></div>';
                            echo '<div class="form-group"><label>Correo:</label><input type="email" name="correo"></div>';
                            echo '<div class="form-group"><label>Contraseña:</label><input type="password" name="contrasena" required></div>';
                            echo '<div class="form-group"><label>Membresía:</label>';
                            echo '<select name="id_membrecia">';
                            echo '<option value="">Sin membresía</option>';
                            
                            $membresias = mysqli_query($conexion, "
                                SELECT m.Id_Membrecia, tm.Nombre_Tipo, tm.Precio, m.Duexion 
                                FROM membrecia m 
                                JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
                                ORDER BY tm.Nombre_Tipo, m.Duexion
                            ");
                            if($membresias && mysqli_num_rows($membresias) > 0){
                                while($memb = mysqli_fetch_assoc($membresias)){
                                    echo '<option value="'.$memb['Id_Membrecia'].'">'.
                                         $memb['Nombre_Tipo'].' - $'.number_format($memb['Precio'], 2).
                                         ' ('.$memb['Duexion'].' días)</option>';
                                }
                            } else {
                                echo '<option value="">No hay membresías disponibles</option>';
                            }
                            echo '</select></div>';
                            echo '<button type="submit" class="btn btn-primary">Guardar Cliente</button>';
                            echo '<a href="?modulo=clientes&accion=leer" class="btn btn-danger">Cancelar</a>';
                            echo '</form>';
                        }
                    }
                }
            }
            ?>
        </div>
    </main>

    <!-- Modal para ver QR -->
    <div id="modalQR" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: rgba(30, 30, 30, 0.95); padding: 20px; border-radius: 10px; text-align: center; border: 2px solid #ff1e1e;">
            <img id="qrImage" src="" alt="QR Code" style="max-width: 300px; max-height: 300px;">
            <br>
            <button onclick="document.getElementById('modalQR').style.display = 'none'" class="btn btn-danger">Cerrar</button>
        </div>
    </div>

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
        function verQR(idCliente) {
            const modal = document.getElementById('modalQR');
            const img = document.getElementById('qrImage');
            img.src = 'qrcodes/' + idCliente + '.png?' + new Date().getTime();
            modal.style.display = 'flex';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalQR').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        function handleGestionar(modulo) {
            window.location.href = `?modulo=${modulo}&accion=leer`;
        }

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