<?php
require_once 'db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$hora = isset($_GET['hora']) ? $_GET['hora'] : '';
$resultados = [];

if (!empty($q) && !empty($fecha) && !empty($hora)) {

    $diaSemana = date('N', strtotime($fecha));

    $dias = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
    $dia = $dias[$diaSemana];

    $fechaHora = $fecha . ' ' . $hora . ':00';
    $sql = "SELECT DISTINCT u.id, u.nombre 
            FROM usuarios u
            INNER JOIN profesionales p ON u.id = p.id
            INNER JOIN profesionales_horarios h ON p.id = h.idPro
            WHERE u.nombre LIKE CONCAT('%', ?, '%')
            AND h.diaSem = ?
            AND ? BETWEEN h.horaIni AND h.horaFin
            AND NOT EXISTS (
                SELECT 1 FROM atenciones a 
                WHERE a.id_pro = u.id 
                AND a.fecha = ?
            )
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $q, $dia, $hora, $fechaHora);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $resultados[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($resultados);