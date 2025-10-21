<?php
// cerrar_sesion.php
session_start();

// Limpiar toda la sesión
$_SESSION = [];

// Destruir la sesión en servidor
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirigir al inicio de sesión
header("Location: forms/Login.php");
exit();
