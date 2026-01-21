<?php
session_start();

// Importar clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $turno_id = $_POST['id'];

  // 1. OBTENER INFORMACIÓN DEL TURNO ANTES DE BORRARLO
  $sqlInfo = "SELECT u.email, u.nombre as cliente_nombre, m.nombre as mascota_nombre, s.nombre as servicio_nombre, a.fecha
                FROM atenciones a
                INNER JOIN mascotas m ON a.id_mascota = m.id
                INNER JOIN usuarios u ON m.id_cliente = u.id
                INNER JOIN servicios s ON a.id_serv = s.id
                WHERE a.id = ?";

  $stmtInfo = $conn->prepare($sqlInfo);
  $stmtInfo->bind_param('i', $turno_id);
  $stmtInfo->execute();
  $resInfo = $stmtInfo->get_result();
  $datosTurno = $resInfo->fetch_assoc();
  $stmtInfo->close();

  if ($datosTurno) {
    // 2. BORRAR EL TURNO
    $sql = "DELETE FROM atenciones WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $turno_id);

    if ($stmt->execute()) {
      // 3. ENVIAR EL MAIL DE CANCELACIÓN
      $mail = new PHPMailer(true);

      try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Destinatario
        $mail->setFrom($_ENV['MAIL_USERNAME'], 'Veterinaria San Antón');
        $mail->addAddress($datosTurno['email'], $datosTurno['cliente_nombre']);

        // Contenido
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
        // El turno se borró pero el mail falló, podrías loguear el error aquí
        $_SESSION['cancelacion_status'] = 'error_mail';
      }

      header('Location: ../vistaCliente/mis-turnos.php');
      exit();
    } else {
      echo "Error al cancelar el turno: " . $stmt->error;
    }
    $stmt->close();
  }
}

$conn->close();
?>