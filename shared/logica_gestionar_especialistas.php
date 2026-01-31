<?php
session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
  die("Acceso denegado");
}

require_once 'db.php';

$query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
          FROM usuarios u 
          INNER JOIN profesionales p ON u.id = p.id
          INNER JOIN especialidad e on p.id_esp = e.id
          ORDER BY u.nombre ASC";

$result = $conn->query($query);
?>