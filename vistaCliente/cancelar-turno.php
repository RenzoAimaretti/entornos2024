<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require_once '../shared/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && intval($_POST['id']) > 0) {
  $turno_id = intval($_POST['id']);

  $sqlInfo = "SELECT u.email, u.nombre as cliente_nombre, m.nombre as mascota_nombre, s.nombre as servicio_nombre, a.fecha
                FROM atenciones a
                INNER JOIN mascotas m ON a.id_mascota = m.id
                INNER JOIN usuarios u ON m.id_cliente = u.id
                INNER JOIN servicios s ON a.id_serv = s.id
                WHERE a.id = ? LIMIT 1";

  $stmtInfo = $conn->prepare($sqlInfo);
  $stmtInfo->bind_param('i', $turno_id);
  $stmtInfo->execute();
  $resInfo = $stmtInfo->get_result();
  $datosTurno = $resInfo->fetch_assoc();
  $stmtInfo->close();

  if ($datosTurno) {
    $sql = "DELETE FROM atenciones WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $turno_id);

    if ($stmt->execute()) {
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
        $mail->addAddress($datosTurno['email'], $datosTurno['cliente_nombre']);

        $mail->isHTML(true);
        $mail->Subject = 'Turno Cancelado con Éxito - San Antón';
        $mail->Body = "
                    <div style='font-family: Arial, sans-serif;'>
                        <h2>Hola {$datosTurno['cliente_nombre']},</h2>
                        <p>Te informamos que el turno para tu mascota ha sido <strong>cancelado con éxito</strong>.</p>
                        <hr>
                        <p><strong>Detalles del turno cancelado:</strong></p>
                        <ul>
                            <li><strong>Mascota:</strong> {$datosTurno['mascota_nombre']}</li>
                            <li><strong>Servicio:</strong> {$datosTurno['servicio_nombre']}</li>
                            <li><strong>Fecha original:</strong> " . date('d/m/Y H:i', strtotime($datosTurno['fecha'])) . "</li>
                        </ul>
                        <p>Si esto fue un error, puedes volver a solicitar un turno desde nuestra plataforma.</p>
                        <p>Saludos,<br>El equipo de San Antón.</p>
                    </div>
                ";

        $mail->send();
        $_SESSION['cancelacion_status'] = 'ok';
      } catch (Exception $e) {
        $_SESSION['cancelacion_status'] = 'error_mail';
      }

      header('Location: ../vistaCliente/mis-turnos.php');
      exit();
    } else {
      echo "Error al cancelar el turno: " . $stmt->error;
    }
    $stmt->close();
  }
} else {
  header('Location: ../vistaCliente/mis-turnos.php');
  exit();
}
$conn->close();
?>