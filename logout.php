<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Eliminar las cookies de sesión
setcookie('usuario_id', '', time() - 3600, "/");
setcookie('usuario_nombre', '', time() - 3600, "/");

// Redirigir al usuario a la página de inicio de sesión
header("Location: index.php");
exit();
?>