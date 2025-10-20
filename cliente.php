<?php
session_start();
include 'conexion.php';

// Verificar si el cliente está logueado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: forms/Inicio_Secion.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];

// —————— OBTENER NOMBRE DEL CLIENTE ——————
try {
    $stmt = mysqli_prepare($conexion, "SELECT Nombre, Apellido FROM cliente WHERE Id_Cliente = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filaCliente = mysqli_fetch_assoc($result);
        
        if ($filaCliente) {
            $nombreCompleto = $filaCliente['Nombre'] . ' ' . $filaCliente['Apellido'];
        } else {
            $nombreCompleto = "Cliente #" . $id_cliente;
        }
        mysqli_stmt_close($stmt);
    } else {
        $nombreCompleto = "Cliente #" . $id_cliente;
    }
} catch (Exception $e) {
    $nombreCompleto = "Cliente #" . $id_cliente;
}

// —————— OBTENER ESTADO DE MEMBRESÍA ——————
function obtenerEstadoMembresia($idCliente, $conexion) {
    $stmt = mysqli_prepare($conexion, "
        SELECT m.Fecha_Fin, tm.Nombre_Tipo, m.Duexion as Duracion
        FROM membrecia m 
        JOIN cliente c ON m.Id_Membrecia = c.Id_Membrecia 
        JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia 
        WHERE c.Id_Cliente = ? 
        LIMIT 1
    ");
    
    if (!$stmt) return ['vigente' => false, 'fecha_fin' => null, 'tipo' => null, 'duracion' => null];
    
    mysqli_stmt_bind_param($stmt, "i", $idCliente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($fila && $fila['Fecha_Fin']) {
        $vigente = strtotime($fila['Fecha_Fin']) >= time();
        return [
            'vigente' => $vigente,
            'fecha_fin' => $fila['Fecha_Fin'],
            'tipo' => $fila['Nombre_Tipo'],
            'duracion' => $fila['Duracion']
        ];
    }
    
    return ['vigente' => false, 'fecha_fin' => null, 'tipo' => null, 'duracion' => null];
}

// —————— CALCULAR RECARGO ——————
function calcularRecargo($fechaFin) {
    if (!$fechaFin) return [0, 0];
    
    $hoy = new DateTime();
    $fin = new DateTime($fechaFin);
    
    if ($fin < $hoy) {
        $interval = $fin->diff($hoy);
        $diasDemora = $interval->days;
        $semanas = ceil($diasDemora / 7);
        $recargo = $semanas * 100;
        return [$semanas, $recargo];
    }
    
    return [0, 0];
}

// Obtener estado de membresía
$estadoMembresia = obtenerEstadoMembresia($id_cliente, $conexion);
$miMembresiaVigente = $estadoMembresia['vigente'];
$fechaFin = $estadoMembresia['fecha_fin'];
$tipoMembresia = $estadoMembresia['tipo'];
$duracionMembresia = $estadoMembresia['duracion'];

// —————— OBTENER CLASES DISPONIBLES ——————
$clasesDisponibles = [];
try {
    $query = "
        SELECT c.Id_Clase, c.Nombre, c.Horario, c.Cupo_Maximo, 
               COUNT(cc.Id_Cliente) AS Inscritos
        FROM clase c
        LEFT JOIN cliente_clase cc ON c.Id_Clase = cc.Id_Clase
        GROUP BY c.Id_Clase
        HAVING Inscritos < c.Cupo_Maximo
    ";
    $result = mysqli_query($conexion, $query);
    if ($result) {
        $clasesDisponibles = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error al obtener clases disponibles: " . $e->getMessage());
}

// —————— OBTENER CLASES INSCRITAS ——————
$clasesInscritas = [];
try {
    $stmt = mysqli_prepare($conexion, "
        SELECT c.Id_Clase, c.Nombre, c.Horario
        FROM clase c
        INNER JOIN cliente_clase cc ON c.Id_Clase = cc.Id_Clase
        WHERE cc.Id_Cliente = ?
    ");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $clasesInscritas = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    error_log("Error al obtener clases inscritas: " . $e->getMessage());
}

// —————— OBTENER CÓDIGO QR ——————
$ultimo = null;
try {
    $stmt = mysqli_prepare($conexion, "
        SELECT Codigo, Fecha_Generado, Estado_Accesso
        FROM accesso 
        WHERE Id_Cliente = ? AND Estado_Accesso = 'activo'
        ORDER BY Fecha_Generado DESC 
        LIMIT 1
    ");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ultimo = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    error_log("Error al obtener código de acceso: " . $e->getMessage());
}

// —————— MENSAJES DE SESIÓN ——————
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Cliente - Gimnasio GYM</title>
    <link rel="icon" href="Imagenes/favicon_1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Desing/cliente.css">
</head>
<body>
    <!-- === Encabezado === -->
    <header>
        <div class="header-left">
            <div class="menu-btn" id="menuBtn">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <h1>GYM PANEL</h1>
        </div>
        <form action="cerrar_sesion.php" method="post">
            <button type="submit" class="btn btn-cerrar">
                <i class="fas fa-sign-out-alt"></i> Salir
            </button>
        </form>
    </header>

    <!-- === Menú Lateral === -->
    <nav id="sidebar">
        <a href="#"><i class="fas fa-home"></i> Inicio</a>
        <a href="#"><i class="fas fa-calendar-alt"></i> Mis Clases</a>
        <a href="#"><i class="fas fa-qrcode"></i> Mi Código QR</a>
        <a href="#"><i class="fas fa-id-card"></i> Mi Membresía</a>
        <a href="#"><i class="fas fa-cog"></i> Configuración</a>
        <a href="#"><i class="fas fa-question-circle"></i> Ayuda</a>
    </nav>

    <!-- === Contenido Principal === -->
    <div class="main-content">
        <!-- Mensajes -->
        <?php if ($mensaje_exito): ?>
            <div class="mensaje-exito">
                <i class="fas fa-check-circle"></i> <?= $mensaje_exito ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="mensaje-error">
                <i class="fas fa-exclamation-triangle"></i> <?= $mensaje_error ?>
            </div>
        <?php endif; ?>

        <!-- Sección de Bienvenida -->
        <div class="welcome-section">
            <h2>
                Bienvenido, <?= htmlspecialchars($nombreCompleto) ?>
                <span class="status-badge <?= $miMembresiaVigente ? 'status-active' : 'status-inactive' ?>">
                    <?= $miMembresiaVigente ? 'ACTIVO' : 'INACTIVO' ?>
                </span>
            </h2>
            <?php if ($miMembresiaVigente && $tipoMembresia): ?>
                <div class="user-actions">
                    <p><strong>Membresía:</strong> <?= htmlspecialchars($tipoMembresia) ?></p>
                    <p><strong>Duración:</strong> <?= $duracionMembresia ?> días</p>
                    <p><strong>Vence:</strong> <?= date('d-m-Y', strtotime($fechaFin)) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($miMembresiaVigente): ?>
            <!-- ========== SECCIÓN: Mis Clases Inscritas ========== -->
            <div class="section-card">
                <h3><i class="fas fa-clipboard-list"></i> Mis Clases Inscritas</h3>
                <?php if (count($clasesInscritas) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Horario</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clasesInscritas as $clase): ?>
                                <tr>
                                    <td><?= htmlspecialchars($clase['Nombre']) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($clase['Horario'])) ?></td>
                                    <td>
                                        <form action="cancelar_clase.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id_clase" value="<?= $clase['Id_Clase'] ?>">
                                            <button class="btn btn-cancelar" type="submit">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 20px; color: var(--gris);">
                        <i class="fas fa-info-circle"></i> No estás inscrito en ninguna clase.
                    </p>
                <?php endif; ?>
            </div>

            <!-- ========== SECCIÓN: Clases Disponibles ========== -->
            <div class="section-card">
                <h3><i class="fas fa-dumbbell"></i> Clases Disponibles</h3>
                <?php if (count($clasesDisponibles) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Horario</th>
                                <th>Cupo Disponible</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clasesDisponibles as $clase): ?>
                                <tr>
                                    <td><?= htmlspecialchars($clase['Nombre']) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($clase['Horario'])) ?></td>
                                    <td><?= ($clase['Cupo_Maximo'] - $clase['Inscritos']) . ' / ' . $clase['Cupo_Maximo'] ?></td>
                                    <td>
                                        <form action="inscribirse_clase.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id_clase" value="<?= $clase['Id_Clase'] ?>">
                                            <button class="btn" type="submit">
                                                <i class="fas fa-plus"></i> Inscribirse
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 20px; color: var(--gris);">
                        <i class="fas fa-info-circle"></i> No hay clases disponibles en este momento.
                    </p>
                <?php endif; ?>
            </div>

            <!-- ========== SECCIÓN: Código QR ========== -->
            <div class="section-card qr-section">
                <h3><i class="fas fa-qrcode"></i> Tu Código de Acceso</h3>
                <?php if ($ultimo): ?>
                    <div style="margin-bottom: 20px;">
                        <p><strong>Código:</strong> <?= htmlspecialchars($ultimo['Codigo']) ?></p>
                        <p><strong>Fecha de generación:</strong> <?= htmlspecialchars($ultimo['Fecha_Generado']) ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="status-badge <?= $ultimo['Estado_Accesso'] == 'activo' ? 'status-active' : 'status-inactive' ?>">
                                <?= strtoupper($ultimo['Estado_Accesso']) ?>
                            </span>
                        </p>
                    </div>
                    <?php
                    $rutaQR = 'qrcodes/' . $id_cliente . '.png';
                    if (file_exists($rutaQR)): ?>
                        <img
                            src="<?= $rutaQR ?>"
                            alt="Código QR"
                            class="codigo-qr"
                        >
                    <?php else: ?>
                        <p style="color: var(--gris);">
                            <i class="fas fa-exclamation-triangle"></i> No se ha generado QR todavía.
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: var(--gris);">
                        <i class="fas fa-info-circle"></i> No has generado ningún código de acceso aún.
                    </p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <?php
                // Calcular recargo y semanas
                list($semanasAtraso, $montoRecargo) = calcularRecargo($fechaFin);
                $hoyFormateado = date('d-m-Y');
            ?>
            <!-- ========== SECCIÓN: MEMBRESÍA VENCIDA ========== -->
            <div class="aviso">
                <h3><i class="fas fa-exclamation-triangle"></i> Suscripción Caducada</h3>
                <?php if ($fechaFin): ?>
                    <p>Tu membresía venció el <strong><?= date('d-m-Y', strtotime($fechaFin)) ?></strong>.</p>
                <?php else: ?>
                    <p>No tienes una membresía activa.</p>
                <?php endif; ?>
                <p>Hoy es <strong><?= $hoyFormateado ?></strong>.</p>
                <?php if ($montoRecargo > 0): ?>
                    <p>Multa acumulada: <strong>$<?= number_format($montoRecargo, 2) ?></strong> (<?= $semanasAtraso ?> semana<?= $semanasAtraso != 1 ? 's' : '' ?>).</p>
                <?php else: ?>
                    <p>No hay multas acumuladas aún.</p>
                <?php endif; ?>
                <p style="margin-top: 15px;">
                    <i class="fas fa-info-circle"></i> Debes renovar tu membresía para poder inscribirte a clases y acceder a tu código QR.
                </p>
                <a href="renovar_membresia.php" class="btn" style="margin-top: 15px;">
                    <i class="fas fa-sync-alt"></i> Renovar Membresía
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Menú hamburguesa
        document.getElementById('menuBtn').addEventListener('click', function() {
            this.classList.toggle('open');
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Cerrar menú al hacer clic fuera de él
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('menuBtn');
            
            if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('active');
                menuBtn.classList.remove('open');
            }
        });
    </script>
</body>
</html>