<?php
session_start();
include 'conexion.php';

// ———— VALIDAR SESIÓN ————
if (!isset($_SESSION['id_entrenador'])) {
    $_SESSION = [];
    header("Location: forms/Login.php");
    exit();
}

$id_entrenador = $_SESSION['id_entrenador'];

// ———— OBTENER NOMBRE ENTRENADOR ————
$stmt = mysqli_prepare($conexion, "SELECT Nombre, Apellido FROM entrenador WHERE Id_Entrenador = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_entrenador);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entrenador = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
$nombreCompleto = $entrenador ? $entrenador['Nombre'] . ' ' . $entrenador['Apellido'] : 'Entrenador #' . $id_entrenador;

// ———— PROCESAR CREACIÓN DE CLASE ————
if (isset($_POST['crear_clase'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $horario = $_POST['horario'];
    $cupo_maximo = (int)$_POST['cupo_maximo'];

    $query = "INSERT INTO clase (Nombre, Horario, Cupo_Maximo, Id_Entrenador) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $nombre, $horario, $cupo_maximo, $id_entrenador);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['mensaje_exito'] = "Clase creada correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "Error al crear la clase: " . mysqli_error($conexion);
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ———— PROCESAR SOLICITUDES PERSONALIZADAS ————
if (isset($_POST['accion_solicitud'])) {
    $accion = $_POST['accion_solicitud'];
    $id_solicitud = (int)$_POST['id_solicitud'];
    
    if ($accion === 'aprobar') {
        $nuevo_estado = 2; // Aprobada
    } else {
        $nuevo_estado = 0; // Rechazada
    }

    $query = "UPDATE clase_personalizada SET Estado = ? WHERE Id_Solicitud = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ii", $nuevo_estado, $id_solicitud);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['mensaje_exito'] = "Solicitud $accion correctamente.";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ———— PROCESAR INFORME DE PROGRESO ————
if (isset($_POST['enviar_informe'])) {
    $id_clase_personalizada = (int)$_POST['id_clase_personalizada'];
    $peso = $_POST['peso'] ? (float)$_POST['peso'] : NULL;
    $medidas_cintura = $_POST['medidas_cintura'] ? (float)$_POST['medidas_cintura'] : NULL;
    $medidas_pecho = $_POST['medidas_pecho'] ? (float)$_POST['medidas_pecho'] : NULL;
    $medidas_brazo = $_POST['medidas_brazo'] ? (float)$_POST['medidas_brazo'] : NULL;
    $peso_sentadilla = $_POST['peso_sentadilla'] ? (float)$_POST['peso_sentadilla'] : NULL;
    $peso_press_banca = $_POST['peso_press_banca'] ? (float)$_POST['peso_press_banca'] : NULL;
    $peso_deadlift = $_POST['peso_deadlift'] ? (float)$_POST['peso_deadlift'] : NULL;
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);

    $query = "INSERT INTO progreso_cliente (Id_Clase_Personalizada, Fecha, Peso, Medidas_Cintura, Medidas_Pecho, Medidas_Brazo, Peso_Sentadilla, Peso_PressBanca, Peso_Deadlift, Observaciones) 
              VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "iddddddds", $id_clase_personalizada, $peso, $medidas_cintura, $medidas_pecho, $medidas_brazo, $peso_sentadilla, $peso_press_banca, $peso_deadlift, $observaciones);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['mensaje_exito'] = "Informe de progreso enviado correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "Error al enviar el informe: " . mysqli_error($conexion);
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ———— OBTENER CLASES DEL ENTRENADOR ————
$clases = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT Id_Clase, Nombre, Horario, Cupo_Maximo
    FROM clase 
    WHERE Id_Entrenador = $id_entrenador
    ORDER BY Horario DESC
"), MYSQLI_ASSOC);

// ———— OBTENER SOLICITUDES DE CLASES PERSONALIZADAS ————
$solicitudes = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT s.Id_Solicitud, c.Nombre AS NombreCliente, c.Apellido AS ApellidoCliente,
           s.Fecha AS Fecha_Clase, s.Observaciones, s.Estado
    FROM clase_personalizada s
    JOIN cliente c ON s.Id_Cliente = c.Id_Cliente
    WHERE s.Id_Entrenador = $id_entrenador
    ORDER BY s.Fecha DESC
"), MYSQLI_ASSOC);

// ———— OBTENER CLASES PERSONALIZADAS APROBADAS PARA REGISTRAR PROGRESO ————
$clases_aprobadas = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT s.Id_Solicitud, c.Nombre AS NombreCliente, c.Apellido AS ApellidoCliente
    FROM clase_personalizada s
    JOIN cliente c ON s.Id_Cliente = c.Id_Cliente
    WHERE s.Id_Entrenador = $id_entrenador AND s.Estado = 2
    ORDER BY s.Fecha DESC
"), MYSQLI_ASSOC);

$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Entrenador - Titán GYM</title>
    <link rel="icon" href="Imagenes/favicon_1.png">
    <link rel="stylesheet" href="Desing/entrenador.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="site-header">
    <h1><i class="fas fa-dumbbell"></i> Panel Entrenador - Titán GYM</h1>
    <form action="cerrar_sesion.php" method="post">
        <button type="submit" class="btn btn-cerrar"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
    </form>
</header>

<main class="main-content">
<?php if ($mensaje_exito): ?><div class="alert alert-success"><?= $mensaje_exito ?></div><?php endif; ?>
<?php if ($mensaje_error): ?><div class="alert alert-error"><?= $mensaje_error ?></div><?php endif; ?>

<section class="hero">
    <h2>Bienvenido, <?= htmlspecialchars($nombreCompleto) ?></h2>
</section>

<!-- 🔹 Crear Clases -->
<section class="section-card">
    <h3><i class="fas fa-plus-circle"></i> Crear Clase Grupal</h3>
    <form method="POST" class="form-solicitud">
        <input type="hidden" name="crear_clase" value="1">
        <label>Nombre de la clase:</label>
        <input type="text" name="nombre" required placeholder="Ej: Yoga, Crossfit, Spinning">
        <label>Horario:</label>
        <input type="datetime-local" name="horario" required>
        <label>Cupo máximo:</label>
        <input type="number" name="cupo_maximo" required min="1" max="50">
        <button class="btn btn-primary"><i class="fas fa-save"></i> Crear Clase</button>
    </form>
</section>

<!-- 🔹 Mis Clases -->
<section class="section-card">
    <h3><i class="fas fa-clipboard-list"></i> Mis Clases Grupales</h3>
    <?php if ($clases): ?>
    <table><thead><tr><th>Nombre</th><th>Horario</th><th>Cupo</th></tr></thead><tbody>
    <?php foreach ($clases as $cl): ?>
        <tr>
            <td><?= htmlspecialchars($cl['Nombre']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($cl['Horario'])) ?></td>
            <td><?= $cl['Cupo_Maximo'] ?></td>
        </tr>
    <?php endforeach; ?></tbody></table>
    <?php else: ?><p>No has creado ninguna clase.</p><?php endif; ?>
</section>

<!-- 🔹 Solicitudes de Clases Personalizadas -->
<section class="section-card">
    <h3><i class="fas fa-user-check"></i> Solicitudes de Clases Personalizadas</h3>
    <?php if ($solicitudes): ?>
    <table><thead><tr><th>Cliente</th><th>Fecha</th><th>Observaciones</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
    <?php foreach ($solicitudes as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['NombreCliente'] . ' ' . $s['ApellidoCliente']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($s['Fecha_Clase'])) ?></td>
            <td><?= htmlspecialchars($s['Observaciones']) ?></td>
            <td>
                <span class="status-badge <?= $s['Estado']==1?'status-inactive':($s['Estado']==2?'status-active':'status-error') ?>">
                    <?= $s['Estado']==1?'PENDIENTE':($s['Estado']==2?'APROBADA':'RECHAZADA') ?>
                </span>
            </td>
            <td>
                <?php if ($s['Estado'] == 1): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="id_solicitud" value="<?= $s['Id_Solicitud'] ?>">
                    <button name="accion_solicitud" value="aprobar" class="btn btn-success"><i class="fas fa-check"></i> Aprobar</button>
                    <button name="accion_solicitud" value="rechazar" class="btn btn-danger"><i class="fas fa-times"></i> Rechazar</button>
                </form>
                <?php else: ?>
                    <span class="text-muted">Procesada</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?></tbody></table>
    <?php else: ?><p>No hay solicitudes de clases personalizadas.</p><?php endif; ?>
</section>

<!-- 🔹 Registrar Progreso de Cliente -->
<section class="section-card">
    <h3><i class="fas fa-chart-line"></i> Registrar Progreso de Cliente</h3>
    <form method="POST" class="form-solicitud">
        <input type="hidden" name="enviar_informe" value="1">
        <label>Clase personalizada (cliente):</label>
        <select name="id_clase_personalizada" required>
            <option value="">Seleccione una clase aprobada</option>
            <?php foreach($clases_aprobadas as $ca): ?>
                <option value="<?= $ca['Id_Solicitud'] ?>">
                    <?= htmlspecialchars($ca['NombreCliente'] . ' ' . $ca['ApellidoCliente']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <div class="form-grid">
            <div>
                <label>Peso (kg):</label>
                <input type="number" step="0.1" name="peso">
            </div>
            <div>
                <label>Medidas Cintura (cm):</label>
                <input type="number" step="0.1" name="medidas_cintura">
            </div>
            <div>
                <label>Medidas Pecho (cm):</label>
                <input type="number" step="0.1" name="medidas_pecho">
            </div>
            <div>
                <label>Medidas Brazo (cm):</label>
                <input type="number" step="0.1" name="medidas_brazo">
            </div>
            <div>
                <label>Peso Sentadilla (kg):</label>
                <input type="number" step="0.1" name="peso_sentadilla">
            </div>
            <div>
                <label>Peso Press Banca (kg):</label>
                <input type="number" step="0.1" name="peso_press_banca">
            </div>
            <div>
                <label>Peso Deadlift (kg):</label>
                <input type="number" step="0.1" name="peso_deadlift">
            </div>
        </div>
        
        <label>Observaciones:</label>
        <textarea name="observaciones" rows="3" placeholder="Observaciones sobre el progreso del cliente..."></textarea>
        
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar Informe</button>
    </form>
</section>

</main>
</body>
</html>