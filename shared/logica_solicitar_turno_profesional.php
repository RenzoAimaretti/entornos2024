<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: iniciar-sesion.php');
  exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$turnoExitoso = false;
$errorMascotaOcupada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profesional_id'])) {
  $id_pro = $_POST['profesional_id'];
  $fecha_turno = $_POST['fecha_turno'];
  $hora_turno = $_POST['hora_turno'];
  $id_mascota = $_POST['id_mascota'];
  $id_serv = $_POST['id_serv'];
  $modalidad = $_POST['modalidad'];

  $fecha_datetime = $fecha_turno . ' ' . $hora_turno;

  $sqlCheck = "SELECT id FROM atenciones WHERE id_mascota = ? AND fecha = ?";
  $stmtCheck = $conn->prepare($sqlCheck);
  $stmtCheck->bind_param("is", $id_mascota, $fecha_datetime);
  $stmtCheck->execute();
  $resultCheck = $stmtCheck->get_result();

  if ($resultCheck->num_rows > 0) {
    $errorMascotaOcupada = true;
  } else {
    $conn->begin_transaction();
    try {
      $sqlInsert = "INSERT INTO atenciones (id_mascota, id_serv, id_pro, fecha, detalle) VALUES (?, ?, ?, ?, ?)";
      $stmtInsert = $conn->prepare($sqlInsert);
      $stmtInsert->bind_param("iiiss", $id_mascota, $id_serv, $id_pro, $fecha_datetime, $modalidad);
      $stmtInsert->execute();

      $sqlInfo = "SELECT u_cli.email as mail_cliente, u_cli.nombre as nombre_cliente, 
                               m.nombre as nombre_mascota, u_pro.nombre as nombre_pro, s.nombre as nombre_serv
                        FROM usuarios u_cli
                        INNER JOIN mascotas m ON m.id_cliente = u_cli.id
                        INNER JOIN usuarios u_pro ON u_pro.id = ?
                        INNER JOIN servicios s ON s.id = ?
                        WHERE m.id = ? AND u_cli.id = ?";
      $stmtInfo = $conn->prepare($sqlInfo);
      $stmtInfo->bind_param("iiii", $id_pro, $id_serv, $id_mascota, $_SESSION['usuario_id']);
      $stmtInfo->execute();
      $infoMail = $stmtInfo->get_result()->fetch_assoc();

      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV['MAIL_USERNAME'];
      $mail->Password = $_ENV['MAIL_PASSWORD'];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail->CharSet = 'UTF-8';
      $mail->setFrom($_ENV['MAIL_USERNAME'], 'Veterinaria San Antón');
      $mail->addAddress($infoMail['mail_cliente'], $infoMail['nombre_cliente']);
      $mail->isHTML(true);
      $mail->Subject = 'Confirmación de Turno - San Antón';
      $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: #00897b;'>¡Hola {$infoMail['nombre_cliente']}!</h2>
                    <p>Tu turno ha sido confirmado con éxito.</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #00897b;'>
                        <p><strong>Mascota:</strong> {$infoMail['nombre_mascota']}</p>
                        <p><strong>Servicio:</strong> {$infoMail['nombre_serv']}</p>
                        <p><strong>Profesional:</strong> {$infoMail['nombre_pro']}</p>
                        <p><strong>Fecha y Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_datetime)) . "</p>
                        <p><strong>Modalidad:</strong> $modalidad</p>
                    </div>
                    <p>Gracias por confiar en San Antón.</p>
                </div>
            ";
      $mail->send();

      $conn->commit();
      $_SESSION['turno_exitoso'] = true;
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();

    } catch (Exception $e) {
      $conn->rollback();
      echo "<div class='alert alert-danger'>El turno se agendó pero no se pudo enviar el mail: {$mail->ErrorInfo}</div>";
    } catch (mysqli_sql_exception $e) {
      $conn->rollback();
      echo "<div class='alert alert-danger'>Error en la base de datos: {$e->getMessage()}</div>";
    }
  }
  $stmtCheck->close();
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
  $turnoExitoso = true;
  unset($_SESSION['turno_exitoso']);
}

$sql = "SELECT profesionales.id, usuarios.nombre, especialidad.nombre AS especialidad, especialidad.id AS id_esp
        FROM profesionales
        INNER JOIN usuarios ON profesionales.id = usuarios.id
        INNER JOIN especialidad ON profesionales.id_esp = especialidad.id";
$result = $conn->query($sql);
$profesionales = $result->fetch_all(MYSQLI_ASSOC);

$horariosPorProfesional = [];
$sqlHorarios = "SELECT idPro, diaSem, horaIni, horaFin FROM profesionales_horarios";
$resultHorarios = $conn->query($sqlHorarios);
while ($row = $resultHorarios->fetch_assoc()) {
  $horariosPorProfesional[$row['idPro']][] = $row;
}

$sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
$stmtMasc = $conn->prepare($sqlMascotas);
$stmtMasc->bind_param("i", $_SESSION['usuario_id']);
$stmtMasc->execute();
$resMasc = $stmtMasc->get_result();
$mascotas = $resMasc->fetch_all(MYSQLI_ASSOC);
$stmtMasc->close();

$sqlServicios = "SELECT id, nombre, precio, id_esp FROM servicios";
$resServ = $conn->query($sqlServicios);
$servicios = $resServ->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>