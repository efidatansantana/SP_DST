<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión, también se debe borrar la cookie de sesión
// Esto asegurará que el usuario se desconecte por completo
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio o a donde desees después del cierre de sesión
header("Location: index.php"); // Cambia "index.php" por la página que desees
exit();
?>
