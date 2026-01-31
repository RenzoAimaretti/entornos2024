<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'especialista') {
    die("Acceso denegado");
}

$profesionalId = $_SESSION['usuario_id'];

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];

    require_once '../shared/db.php';

    $sql = "SELECT a.id, a.fecha, a.hora, p.nombre AS paciente, s.nombre AS servicio
            FROM atenciones a
            INNER JOIN usuarios p ON a.id_cliente = p.id
            INNER JOIN servicios s ON a.id_servicio = s.id
            WHERE a.id_pro = ? AND DATE(a.fecha) = ?
            ORDER BY a.hora ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $profesionalId, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            echo "<li class='list-group-item'>";
            echo "<strong>Hora:</strong> " . date("H:i", strtotime($row['hora'])) . "<br>";
            echo "<strong>Paciente:</strong> " . htmlspecialchars($row['paciente']) . "<br>";
            echo "<strong>Servicio:</strong> " . htmlspecialchars($row['servicio']);
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay atenciones registradas para este día.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>