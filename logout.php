<?php
/**
 * CERRAR SESIÓN - "SOLO COMPU"
 * Limpia y destruye la sesión de PHP de manera limpia.
 */
session_start();

// Desarmar todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también se puede hacer
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al inicio de la tienda
header("Location: index.php");
exit;
?>
