<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    die("Acceso denegado");
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['tel'];
    $especialidad = $_POST['esp'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $dias = $_POST['dias'] ?? [];

    if ($password != $repassword) {
        die("Las contraseñas no coinciden");
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $queryUser = "INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, 'especialista')";
        $stmtUser = $conn->prepare($queryUser);
        $stmtUser->bind_param("sss", $nombre, $email, $hashed_password);

        if ($stmtUser->execute()) {
            $id = $conn->insert_id;

            $queryProf = "INSERT INTO profesionales (id, telefono, id_esp) VALUES (?, ?, ?)";
            $stmtProf = $conn->prepare($queryProf);
            $stmtProf->bind_param("isi", $id, $telefono, $especialidad);
            $resultProf = $stmtProf->execute();

            if ($resultProf) {
                if (!empty($dias)) {
                    $queryDia = "INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES (?, ?, ?, ?)";
                    $stmtDia = $conn->prepare($queryDia);

                    foreach ($dias as $dia) {
                        $diaSem = $dia['dia'];
                        $horaIni = $dia['horaInicio'];
                        $horaFin = $dia['horaFin'];

                        $stmtDia->bind_param("isss", $id, $diaSem, $horaIni, $horaFin);
                        $stmtDia->execute();
                    }
                    $stmtDia->close();
                }

                $stmtProf->close();
                $stmtUser->close();

                header("Location: ../vistaAdmin/gestionar-especialistas.php?res=ok");
                exit();
            } else {
                echo "Error al registrar datos profesionales";
            }
        } else {
            echo "Error al registrar usuario especialista";
        }
    }
}
?>