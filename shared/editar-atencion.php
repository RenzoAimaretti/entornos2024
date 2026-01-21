<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $fecha_nueva = $_POST['fecha'];
    $detalle_nuevo = $_POST['detalle'];

    if (!$id || !$fecha_nueva || !$detalle_nuevo) {
        die("Todos los campos son obligatorios.");
    }

    // 1. Obtener datos del cliente y de la mascota ANTES de actualizar
    $sqlInfo = "SELECT u.email, u.nombre as cliente_nombre, m.nombre as mascota_nombre, s.nombre as servicio_nombre
                FROM atenciones a
                INNER JOIN mascotas m ON a.id_mascota = m.id
                INNER JOIN usuarios u ON m.id_cliente = u.id
                INNER JOIN servicios s ON a.id_serv = s.id
                WHERE a.id = ?";
    $stmtInfo = $conn->prepare($sqlInfo);
    $stmtInfo->bind_param("i", $id);
    $stmtInfo->execute();
    $datos = $stmtInfo->get_result()->fetch_assoc();
    $stmtInfo->close();

    // 2. Realizar la actualización
    $query = "UPDATE atenciones SET fecha = ?, detalle = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $fecha_nueva, $detalle_nuevo, $id);

    if ($stmt->execute()) {
        // 3. Enviar Mail de Notificación
        if ($datos) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['MAIL_USERNAME'];
                $mail->Password = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom($_ENV['MAIL_USERNAME'], 'Veterinaria San Antón');
                $mail->addAddress($datos['email'], $datos['cliente_nombre']);

                $mail->isHTML(true);
                $mail->Subject = 'Actualización de Turno - San Antón';
                $mail->Body = "
                    <h3>Hola {$datos['cliente_nombre']},</h3>
                    <p>Te informamos que se han realizado cambios en el turno de <strong>{$datos['mascota_nombre']}</strong>.</p>
                    <p><strong>Nuevos detalles:</strong></p>
                    <ul>
                        <li><strong>Servicio:</strong> {$datos['servicio_nombre']}</li>
                        <li><strong>Nueva Fecha/Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_nueva)) . "</li>
                        <li><strong>Observaciones:</strong> $detalle_nuevo</li>
                    </ul>
                    <p>Saludos,<br>El equipo de San Antón.</p>";

                $mail->send();
            } catch (Exception $e) {
                // Error de mail silencioso para no interrumpir el flujo
            }
        }
        header("Location: detalle-atencionAP.php?id=$id&res=editado");
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();