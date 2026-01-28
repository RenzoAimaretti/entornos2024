<?php
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SESSION['usuario_tipo'] !== 'admin') {
  die("Acceso denegado");
}

require_once '../shared/db.php';

$query = "SELECT u.id, u.nombre, u.email, c.direccion, c.telefono 
          FROM usuarios u 
          JOIN clientes c ON u.id = c.id
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;

$queryMascotas = "SELECT m.id, m.nombre AS mascota_nombre, m.raza, m.fecha_nac, m.fecha_mue, m.foto 
                  FROM mascotas m 
                  WHERE m.id_cliente = ?";
$stmtMascotas = $conn->prepare($queryMascotas);
$stmtMascotas->bind_param("i", $id);
$stmtMascotas->execute();
$resultMascotas = $stmtMascotas->get_result();
?>