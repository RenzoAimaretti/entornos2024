<?php
session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
  die("Acceso denegado");
}

require_once 'db.php';

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          ORDER BY u.nombre ASC";

$result = $conn->query($query);
?>