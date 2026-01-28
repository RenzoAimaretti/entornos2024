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
      $userId = $_SESSION['usuario_id'];
      $stmtInfo->bind_param("iiii", $id_pro, $id_serv, $id_mascota, $userId);
      $stmtInfo->execute();
      $infoMail = $stmtInfo->get_result()->fetch_assoc();

      if ($infoMail) {
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
                        <h2 style='color: #00897b;'>¡Turno Confirmado!</h2>
                        <p>Hola <strong>{$infoMail['nombre_cliente']}</strong>,</p>
                        <p>Se ha registrado un nuevo turno para tu mascota:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #00897b;'>
                            <p><strong>Mascota:</strong> {$infoMail['nombre_mascota']}</p>
                            <p><strong>Servicio:</strong> {$infoMail['nombre_serv']}</p>
                            <p><strong>Profesional:</strong> {$infoMail['nombre_pro']}</p>
                            <p><strong>Fecha y Hora:</strong> " . date('d/m/Y H:i', strtotime($fecha_datetime)) . "</p>
                            <p><strong>Modalidad:</strong> $modalidad</p>
                        </div>
                        <p>¡Te esperamos!</p>
                    </div>
                ";
        $mail->send();
      }

      $conn->commit();
      $_SESSION['turno_exitoso'] = true;
      header("Location: solicitar-turno-servicio.php?service_id=" . $id_serv);
      exit();

    } catch (Exception $e) {
      $conn->rollback();
    } catch (mysqli_sql_exception $e) {
      $conn->rollback();
    }
  }
  $stmtCheck->close();
}

if (isset($_SESSION['turno_exitoso']) && $_SESSION['turno_exitoso']) {
  $turnoExitoso = true;
  unset($_SESSION['turno_exitoso']);
}

$service_id_selected = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;
$servicios = [];
$profesionales = [];
$horariosPorProfesional = [];
$mascotas = [];
$servicio_seleccionado = null;

if ($service_id_selected) {
  $sqlServicio = "SELECT id, nombre, precio, id_esp FROM servicios WHERE id = ?";
  $stmtServ = $conn->prepare($sqlServicio);
  $stmtServ->bind_param("i", $service_id_selected);
  $stmtServ->execute();
  $servicio_seleccionado = $stmtServ->get_result()->fetch_assoc();
  $stmtServ->close();

  $sqlProfesionales = "SELECT p.id, u.nombre, e.nombre AS especialidad, e.id AS id_esp
                         FROM profesionales p
                         INNER JOIN usuarios u ON p.id = u.id
                         INNER JOIN especialidad e ON p.id_esp = e.id
                         WHERE e.id = ?";
  $stmtProf = $conn->prepare($sqlProfesionales);
  $stmtProf->bind_param("i", $servicio_seleccionado['id_esp']);
  $stmtProf->execute();
  $profesionales = $stmtProf->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtProf->close();

  $profesionales_ids = array_column($profesionales, 'id');
  if (!empty($profesionales_ids)) {
    $prof_ids_str = implode(',', $profesionales_ids);
    $sqlHorarios = "SELECT idPro, diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro IN ($prof_ids_str)";
    $resultHorarios = $conn->query($sqlHorarios);
    while ($row = $resultHorarios->fetch_assoc()) {
      $horariosPorProfesional[$row['idPro']][] = $row;
    }
  }

  $sqlMascotas = "SELECT id, nombre FROM mascotas WHERE id_cliente = ?";
  $stmtMasc = $conn->prepare($sqlMascotas);
  $stmtMasc->bind_param("i", $_SESSION['usuario_id']);
  $stmtMasc->execute();
  $mascotas = $stmtMasc->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtMasc->close();

} else {
  $resultServ = $conn->query("SELECT id, nombre, precio FROM servicios");
  $servicios = $resultServ->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>