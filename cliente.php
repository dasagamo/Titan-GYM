<?php
session_start();
include 'Conexion.php';

// Verificar si el cliente está logueado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: Inicio_Secion.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];

// —————— OBTENER NOMBRE DEL CLIENTE ——————
try {
    $stmtNombre = $conexion->prepare("
        SELECT Nombre, Apellido
        FROM cliente
        WHERE ID_Cliente = :idCliente
        LIMIT 1
    ");
    $stmtNombre->bindParam(':idCliente', $id_cliente, PDO::PARAM_INT);
    $stmtNombre->execute();
    $filaCliente = $stmtNombre->fetch(PDO::FETCH_ASSOC);
    if ($filaCliente) {
        $nombreCompleto = $filaCliente['Nombre'] . ' ' . $filaCliente['Apellido'];
    } else {
        $nombreCompleto = "Cliente #" . $id_cliente;
    }
} catch (PDOException $e) {
    $nombreCompleto = "Cliente #" . $id_cliente;
}
// —————————————————————————————————————————

// Función que verifica si la membresía del cliente está vigente y retorna Fecha_Fin
function obtenerFechaFin($idCliente, $conexion) {
    $stmt = $conexion->prepare("
        SELECT Fecha_Fin
        FROM membresia
        WHERE ID_Cliente = :idCliente
        ORDER BY Fecha_Fin DESC
        LIMIT 1
    ");
    $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['Fecha_Fin'] ?? null;
}

// Función que calcula recargo por semanas de demora (100 por semana). Retorna [semanas, monto].
function calcularRecargo($fechaFin) {
    if (!$fechaFin) return [0, 0];
    $hoy = new DateTime();
    $fin = new DateTime($fechaFin);
    if ($fin < $hoy) {
        $interval = $fin->diff($hoy);
        $diasDemora = $interval->days;
        $semanas = floor($diasDemora / 7);
        $recargo = $semanas * 100;
        return [$semanas, $recargo];
    }
    return [0, 0];
}

$fechaFin = obtenerFechaFin($id_cliente, $conexion);
$miMembresiaVigente = false;
if ($fechaFin) {
    $miMembresiaVigente = strtotime($fechaFin) >= time();
}

// 2) Obtener las clases disponibles (cupo < cupo máximo)
try {
    $stmtDisponibles = $conexion->prepare("
        SELECT c.ID_Clase, c.Nombre, c.Horario, c.Cupo_Maximo, 
               COUNT(cc.ID_Cliente) AS Inscritos
        FROM clase c
        LEFT JOIN cliente_clase cc ON c.ID_Clase = cc.ID_Clase
        GROUP BY c.ID_Clase
        HAVING Inscritos < c.Cupo_Maximo
    ");
    $stmtDisponibles->execute();
    $clasesDisponibles = $stmtDisponibles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la base de datos al obtener clases disponibles: " . $e->getMessage());
}

// 3) Obtener las clases en las que el cliente ya está inscrito
try {
    $stmtInscrito = $conexion->prepare("
        SELECT c.ID_Clase, c.Nombre, c.Horario
        FROM clase c
        INNER JOIN cliente_clase cc ON c.ID_Clase = cc.ID_Clase
        WHERE cc.ID_Cliente = :idCliente
    ");
    $stmtInscrito->bindParam(':idCliente', $id_cliente, PDO::PARAM_INT);
    $stmtInscrito->execute();
    $clasesInscritas = $stmtInscrito->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la base de datos al obtener clases inscritas: " . $e->getMessage());
}

// 4) Consultar el último código generado para este cliente
$stmtCod = $conexion->prepare("
    SELECT Codigo, Fecha_Generado
    FROM acceso
    WHERE ID_Cliente = :idCliente
    ORDER BY Fecha_Generado DESC
    LIMIT 1
");
$stmtCod->bindParam(':idCliente', $id_cliente, PDO::PARAM_INT);
$stmtCod->execute();
$ultimo = $stmtCod->fetch(PDO::FETCH_ASSOC);

// 5) Mensajes de sesión
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
        <form action="index.php" method="post">
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
            <div class="user-actions">
                <!-- Espacio para acciones adicionales del usuario -->
            </div>
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
                                            <input type="hidden" name="id_clase" value="<?= $clase['ID_Clase'] ?>">
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
                                            <input type="hidden" name="id_clase" value="<?= $clase['ID_Clase'] ?>">
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
                    </div>
                    <?php
                    $rutaQR = __DIR__ . '/qrcodes/' . $id_cliente . '.png';
                    if (file_exists($rutaQR)): ?>
                        <img
                            src="qrcodes/<?= htmlspecialchars($id_cliente) ?>.png"
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
                <p>Tu membresía venció el <strong><?= $fechaFin ? date('d-m-Y', strtotime($fechaFin)) : 'N/A' ?></strong>.</p>
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