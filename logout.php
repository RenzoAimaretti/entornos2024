<?php
session_start();

$_SESSION = array();

session_destroy();

setcookie('usuario_id', '', time() - 3600, "/");
setcookie('usuario_nombre', '', time() - 3600, "/");

header("Location: index.php");
exit();
?>