<?php
function obtenerDetalleAtencion($conn, $idAtencion)
{
  $query = "SELECT a.id, 
                     m.nombre as nombreMascota,
                     m.id as idMascota,
                     m.raza,
                     s.nombre as nombreServicio,
                     a.fecha,
                     a.detalle,
                     u.nombre as nombrePro,
                     u.id as idPro
              FROM atenciones a
              INNER JOIN mascotas m on a.id_mascota = m.id
              INNER JOIN servicios s on a.id_serv = s.id
              INNER JOIN usuarios u on a.id_pro = u.id
              WHERE a.id = ?";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $idAtencion);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc();
}
?>