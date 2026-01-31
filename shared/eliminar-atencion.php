<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $sqlInfo = "SELECT u.email, u.nombre as cliente_nombre, m.nombre as mascota_nombre, s.nombre as servicio_nombre, a.fecha
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

    $query = "DELETE FROM atenciones WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

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
                $mail->Subject = 'Cancelación de Turno - San Antón';
                $mail->Body = "
                    <h3>Hola {$datos['cliente_nombre']},</h3>
                    <p>Te informamos que el siguiente turno ha sido <strong>cancelado</strong>:</p>
                    <ul>
                        <li><strong>Mascota:</strong> {$datos['mascota_nombre']}</li>
                        <li><strong>Servicio:</strong> {$datos['servicio_nombre']}</li>
                        <li><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($datos['fecha'])) . "</li>
                    </ul>
                    <p>Si tienes dudas, por favor contáctanos.</p>
                    <p>Saludos,<br>San Antón.</p>";

                $mail->send();
            } catch (Exception $e) {
            }
        }
        header("Location: ../vistaAdmin/gestionar-atenciones.php?res=eliminado");
        exit();
    } else {
        echo "Error al eliminar: " . $stmt->error;
    }
    $stmt->close();
} else {
    header("Location: ../vistaAdmin/gestionar-atenciones.php");
    exit();
}
$conn->close();