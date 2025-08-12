<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$hora = isset($_GET['hora']) ? $_GET['hora'] : '';
$resultados = [];

if (!empty($q) && !empty($fecha) && !empty($hora)) {
    // Extraer día de la semana de la fecha (1: Lunes, ..., 7: Domingo)
    $diaSemana = date('N', strtotime($fecha)); // 1 para Lunes, 7 para Domingo
    
    // Convertir al formato usado en la base de datos
    $dias = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
    $dia = $dias[$diaSemana];
    
    // Consulta para buscar especialistas disponibles en ese día y hora
    $fechaHora = $fecha . ' ' . $hora . ':00';
    $sql = "SELECT u.id, u.nombre 
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
    $stmt->bind_param("ssss", $q, $dia, $hora,$fechaHora);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $resultados[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($resultados);