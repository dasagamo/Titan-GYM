<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../Conexion.php';

// ———— VALIDAR SESIÓN ————
if (!isset($_SESSION['id_cliente'])) {
    $_SESSION = [];
    header("Location: forms/Login.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];

// ———— OBTENER NOMBRE CLIENTE ————
$stmt = mysqli_prepare($conexion, "SELECT Nombre, Apellido FROM cliente WHERE Id_Cliente = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
$nombreCompleto = $cliente ? $cliente['Nombre'] . ' ' . $cliente['Apellido'] : 'Cliente #' . $id_cliente;

// ———— OBTENER ESTADO MEMBRESÍA ————
function obtenerEstadoMembresia($idCliente, $conexion) {
    $stmt = mysqli_prepare($conexion, "
        SELECT m.Fecha_Fin, tm.Nombre_Tipo, m.Duracion, tm.Id_Tipo_Membrecia
        FROM cliente c
        JOIN membrecia m ON c.Id_Membrecia = m.Id_Membrecia
        JOIN tipo_membrecia tm ON m.Id_Tipo_Membrecia = tm.Id_Tipo_Membrecia
        WHERE c.Id_Cliente = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "i", $idCliente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($fila && $fila['Fecha_Fin']) {
        $vigente = strtotime($fila['Fecha_Fin']) >= strtotime(date('Y-m-d'));
        return [
            'vigente' => $vigente,
            'fecha_fin' => $fila['Fecha_Fin'],
            'tipo' => $fila['Nombre_Tipo'],
            'duracion' => $fila['Duracion'],
            'id_tipo_membrecia' => $fila['Id_Tipo_Membrecia']
        ];
    }
    return ['vigente' => false, 'fecha_fin' => null, 'tipo' => null, 'duracion' => null, 'id_tipo_membrecia' => null];
}

// ———— CALCULAR RECARGO ————
function calcularRecargo($fechaFin) {
    if (!$fechaFin) return [0, 0];
    $hoy = new DateTime();
    $fin = new DateTime($fechaFin);
    if ($fin < $hoy) {
        $dias = $fin->diff($hoy)->days;
        $semanas = ceil($dias / 7);
        return [$semanas, $semanas * 100];
    }
    return [0, 0];
}

// ———— ESTADO MEMBRESÍA ————
$estado = obtenerEstadoMembresia($id_cliente, $conexion);
$vigente = $estado['vigente'];
$fechaFin = $estado['fecha_fin'];
$tipoMembresia = $estado['tipo'];
$duracion = $estado['duracion'];
$id_tipo_membrecia = $estado['id_tipo_membrecia'];

// ———— PROCESAR SOLICITUD PERSONALIZADA ————
if (isset($_POST['solicitar_clase'])) {
    if ($vigente && $tipoMembresia == 'Premium') {
        $id_entrenador = $_POST['id_entrenador'];
        $fecha_clase = $_POST['fecha_clase'];
        $descripcion = trim($_POST['descripcion']);

        // CORREGIDO: Usar la estructura correcta de tu tabla clase_personalizada
        $query = "INSERT INTO clase_personalizada (Id_Cliente, Id_Entrenador, Fecha, Observaciones, Estado) 
                  VALUES (?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "iiss", $id_cliente, $id_entrenador, $fecha_clase, $descripcion);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['mensaje_exito'] = "Solicitud enviada correctamente al entrenador.";
        } else {
            $_SESSION['mensaje_error'] = "Error al enviar la solicitud: " . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
    } else if (!$vigente) {
        $_SESSION['mensaje_error'] = "Tu membresía está vencida. Renueva para solicitar clases personalizadas.";
    } else {
        $_SESSION['mensaje_error'] = "Solo los miembros Premium pueden solicitar clases personalizadas.";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ———— CLASES DISPONIBLES ————
$q1 = "
    SELECT c.Id_Clase, c.Nombre, c.Horario, c.Cupo_Maximo, COUNT(cc.Id_Cliente) AS Inscritos
    FROM clase c
    LEFT JOIN cliente_clase cc ON c.Id_Clase = cc.Id_Clase
    GROUP BY c.Id_Clase
    HAVING Inscritos < c.Cupo_Maximo
";
$res1 = mysqli_query($conexion, $q1);
$clasesDisponibles = $res1 ? mysqli_fetch_all($res1, MYSQLI_ASSOC) : [];

// ———— CLASES INSCRITAS ————
$q2 = "
    SELECT c.Id_Clase, c.Nombre, c.Horario
    FROM clase c
    INNER JOIN cliente_clase cc ON c.Id_Clase = cc.Id_Clase
    WHERE cc.Id_Cliente = $id_cliente
";
$res2 = mysqli_query($conexion, $q2);
$clasesInscritas = $res2 ? mysqli_fetch_all($res2, MYSQLI_ASSOC) : [];

// ———— ENTRENADORES DISPONIBLES ————
$q3 = "
    SELECT e.Id_Entrenador, e.Nombre, e.Apellido, esp.Nombre_Especialidad AS Especialidad
    FROM entrenador e
    JOIN especialidad esp ON e.Id_Especialidad = esp.Id_Especialidad
";
$res3 = mysqli_query($conexion, $q3);
$entrenadores = $res3 ? mysqli_fetch_all($res3, MYSQLI_ASSOC) : [];

// ———— SOLICITUDES PERSONALIZADAS ————
// CORREGIDO: Usar la estructura correcta de tu tabla
$q4 = "
    SELECT s.Id_Solicitud, e.Nombre AS NombreEntrenador, e.Apellido AS ApellidoEntrenador,
           s.Fecha AS Fecha_Clase, s.Estado, s.Observaciones AS Descripcion
    FROM clase_personalizada s
    JOIN entrenador e ON s.Id_Entrenador = e.Id_Entrenador
    WHERE s.Id_Cliente = $id_cliente
    ORDER BY s.Fecha DESC
";
$res4 = mysqli_query($conexion, $q4);
$solicitudes = $res4 ? mysqli_fetch_all($res4, MYSQLI_ASSOC) : [];

// ———— PROGRESO DEL CLIENTE ————
$q5 = "
    SELECT p.Fecha, p.Peso, p.Medidas_Cintura, p.Medidas_Pecho, p.Medidas_Brazo, 
           p.Peso_Sentadilla, p.Peso_PressBanca, p.Peso_Deadlift, p.Observaciones,
           e.Nombre AS EntrenadorNombre, e.Apellido AS EntrenadorApellido
    FROM progreso_cliente p
    JOIN clase_personalizada cp ON p.Id_Clase_Personalizada = cp.Id_Solicitud
    JOIN entrenador e ON cp.Id_Entrenador = e.Id_Entrenador
    WHERE cp.Id_Cliente = $id_cliente
    ORDER BY p.Fecha DESC
";
$res5 = mysqli_query($conexion, $q5);
$progresos = $res5 ? mysqli_fetch_all($res5, MYSQLI_ASSOC) : [];

// ———— QR ACTIVO ————
$ultimoQR = mysqli_fetch_assoc(mysqli_query($conexion, "
    SELECT Codigo, Fecha_Generado, Estado_Acceso
    FROM acceso
    WHERE Id_Cliente = $id_cliente AND Estado_Acceso = 'activo'
    ORDER BY Fecha_Generado DESC LIMIT 1
"));

$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Cliente - Titán GYM</title>
    <link rel="icon" href="Imagenes/favicon_1.png">
    <link rel="stylesheet" href="../Desing/cliente.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="site-header">
    <h1><i class="fas fa-dumbbell"></i> Panel Cliente - Titán GYM</h1>
    <form action="../forms/cerrar_sesion.php" method="post">
        <button type="submit" class="btn btn-cerrar"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
    </form>
</header>

<main class="main-content">
<?php if ($mensaje_exito): ?><div class="alert alert-success"><?= $mensaje_exito ?></div><?php endif; ?>
<?php if ($mensaje_error): ?><div class="alert alert-error"><?= $mensaje_error ?></div><?php endif; ?>

<section class="hero">
    <h2>Bienvenido, <?= htmlspecialchars($nombreCompleto) ?> 
        <span class="status-badge <?= $vigente ? 'status-active' : 'status-inactive' ?>">
            <?= $vigente ? 'ACTIVO' : 'INACTIVO' ?>
        </span>
    </h2>
    <?php if ($vigente && $tipoMembresia): ?>
        <div class="user-info">
            <p><b>Membresía:</b> <?= $tipoMembresia ?></p>
            <p><b>Duración:</b> <?= $duracion ?> días</p>
            <p><b>Vence:</b> <?= date('d-m-Y', strtotime($fechaFin)) ?></p>
        </div>
    <?php endif; ?>
</section>

<?php if ($vigente): ?>

<!-- 🔹 Clases inscritas -->
<section class="section-card">
    <h3><i class="fas fa-clipboard-list"></i> Mis Clases Inscritas</h3>
    <?php if ($clasesInscritas): ?>
    <table><thead><tr><th>Nombre</th><th>Horario</th><th>Acción</th></tr></thead><tbody>
    <?php foreach ($clasesInscritas as $cl): ?>
        <tr>
            <td><?= htmlspecialchars($cl['Nombre']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($cl['Horario'])) ?></td>
            <td>
                <form action="cancelar_clase.php" method="POST">
                    <input type="hidden" name="id_clase" value="<?= $cl['Id_Clase'] ?>">
                    <button class="btn btn-cancelar"><i class="fas fa-times"></i> Cancelar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?></tbody></table>
    <?php else: ?><p>No estás inscrito en ninguna clase.</p><?php endif; ?>
</section>

<!-- 🔹 Clases disponibles -->
<section class="section-card">
    <h3><i class="fas fa-dumbbell"></i> Clases Disponibles</h3>
    <?php if ($clasesDisponibles): ?>
    <table><thead><tr><th>Nombre</th><th>Horario</th><th>Cupo</th><th>Acción</th></tr></thead><tbody>
    <?php foreach ($clasesDisponibles as $cd): ?>
        <tr><td><?= htmlspecialchars($cd['Nombre']) ?></td>
        <td><?= date('d-m-Y H:i', strtotime($cd['Horario'])) ?></td>
        <td><?= $cd['Cupo_Maximo'] - $cd['Inscritos'] ?>/<?= $cd['Cupo_Maximo'] ?></td>
        <td>
            <form action="inscribirse_clase.php" method="POST">
                <input type="hidden" name="id_clase" value="<?= $cd['Id_Clase'] ?>">
                <button class="btn btn-primary"><i class="fas fa-plus"></i> Inscribirse</button>
            </form>
        </td></tr>
    <?php endforeach; ?></tbody></table>
    <?php else: ?><p>No hay clases disponibles.</p><?php endif; ?>
</section>

<?php if ($vigente && $tipoMembresia == 'Premium'): ?>

<!-- 🔹 Clases personalizadas SOLO PARA PREMIUM -->
<section class="section-card">
    <h3><i class="fas fa-user-friends"></i> Solicitar Clase Personalizada</h3>
    <form method="POST" class="form-solicitud">
        <input type="hidden" name="solicitar_clase" value="1">
        <label>Entrenador:</label>
        <select name="id_entrenador" required>
            <option value="">Seleccione un entrenador</option>
            <?php foreach($entrenadores as $ent): ?>
                <option value="<?= $ent['Id_Entrenador'] ?>">
                    <?= htmlspecialchars($ent['Nombre'].' '.$ent['Apellido'].' — '.$ent['Especialidad']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Fecha deseada:</label>
        <input type="datetime-local" name="fecha_clase" required>
        <label>Descripción / Objetivo:</label>
        <textarea name="descripcion" rows="3" placeholder="Ej: mejorar resistencia o técnica de peso muerto" required></textarea>
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar Solicitud</button>
    </form>
</section>

<!-- 🔹 Solicitudes -->
<section class="section-card">
    <h3><i class="fas fa-history"></i> Mis Solicitudes de Clases Personalizadas</h3>
    <?php if($solicitudes): ?>
    <table>
        <thead><tr><th>Entrenador</th><th>Fecha Clase</th><th>Descripción</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach($solicitudes as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['NombreEntrenador'].' '.$s['ApellidoEntrenador']) ?></td>
                <td><?= $s['Fecha_Clase'] ? date('d-m-Y H:i', strtotime($s['Fecha_Clase'])) : '—' ?></td>
                <td><?= htmlspecialchars($s['Descripcion']) ?></td>
                <td><span class="status-badge <?= $s['Estado']==1?'status-inactive':($s['Estado']==2?'status-active':'status-error') ?>">
                    <?= $s['Estado']==1?'PENDIENTE':($s['Estado']==2?'APROBADA':'RECHAZADA') ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><p>No has solicitado clases personalizadas aún.</p><?php endif; ?>
</section>

<!-- 🔹 Mi Progreso -->
<section class="section-card">
    <h3><i class="fas fa-chart-line"></i> Mi Progreso</h3>
    <?php if($progresos): ?>
    <div class="progreso-container">
        <?php foreach($progresos as $progreso): ?>
        <div class="progreso-card">
            <h4>Informe del <?= date('d-m-Y', strtotime($progreso['Fecha'])) ?></h4>
            <p><strong>Entrenador:</strong> <?= htmlspecialchars($progreso['EntrenadorNombre'].' '.$progreso['EntrenadorApellido']) ?></p>
            <div class="progreso-datos">
                <?php if($progreso['Peso']): ?><p><strong>Peso:</strong> <?= $progreso['Peso'] ?> kg</p><?php endif; ?>
                <?php if($progreso['Medidas_Cintura']): ?><p><strong>Cintura:</strong> <?= $progreso['Medidas_Cintura'] ?> cm</p><?php endif; ?>
                <?php if($progreso['Medidas_Pecho']): ?><p><strong>Pecho:</strong> <?= $progreso['Medidas_Pecho'] ?> cm</p><?php endif; ?>
                <?php if($progreso['Medidas_Brazo']): ?><p><strong>Brazo:</strong> <?= $progreso['Medidas_Brazo'] ?> cm</p><?php endif; ?>
                <?php if($progreso['Peso_Sentadilla']): ?><p><strong>Sentadilla:</strong> <?= $progreso['Peso_Sentadilla'] ?> kg</p><?php endif; ?>
                <?php if($progreso['Peso_PressBanca']): ?><p><strong>Press Banca:</strong> <?= $progreso['Peso_PressBanca'] ?> kg</p><?php endif; ?>
                <?php if($progreso['Peso_Deadlift']): ?><p><strong>Deadlift:</strong> <?= $progreso['Peso_Deadlift'] ?> kg</p><?php endif; ?>
            </div>
            <?php if($progreso['Observaciones']): ?>
                <p><strong>Observaciones:</strong> <?= htmlspecialchars($progreso['Observaciones']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?><p>No tienes informes de progreso aún.</p><?php endif; ?>
</section>

<?php endif; ?>

<!-- 🔹 Código QR -->
<section class="section-card">
    <h3><i class="fas fa-qrcode"></i> Mi Código QR</h3>
    <?php if ($ultimoQR): ?>
        <p><b>Código:</b> <?= $ultimoQR['Codigo'] ?><br><b>Estado:</b> <?= strtoupper($ultimoQR['Estado_Acceso']) ?></p>
        <?php $rutaQR = 'qrcodes/'.$id_cliente.'.png'; if(file_exists($rutaQR)): ?>
            <img src="<?= $rutaQR ?>" alt="QR" class="codigo-qr">
        <?php endif; ?>
    <?php else: ?><p>No tienes código activo.</p><?php endif; ?>
</section>

<?php else: 
    list($semanas, $recargo) = calcularRecargo($fechaFin); ?>
<section class="section-card aviso">
    <h3><i class="fas fa-exclamation-triangle"></i> Suscripción Caducada</h3>
    <p>Venció el <b><?= date('d-m-Y', strtotime($fechaFin)) ?></b>. Recargo: <b>$<?= $recargo ?></b></p>
    <a href="renovar_membresia.php" class="btn btn-primary"><i class="fas fa-sync"></i> Renovar</a>
</section>
<?php endif; ?>
</main>
</body>
</html>