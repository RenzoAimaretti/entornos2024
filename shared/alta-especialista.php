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
        $queryUser = "INSERT INTO usuarios (nombre,email,password,tipo) VALUES ('$nombre','$email','$password','especialista')";
        $resultUser = $conn->query($queryUser);
        if ($resultUser) {
            $id = $conn->insert_id;
            $queryProf = "INSERT INTO profesionales (id,telefono,id_esp) VALUES ('$id','$telefono','$especialidad')";
            $resultProf = $conn->query($queryProf);
            if ($resultProf) {
                foreach ($dias as $dia) {
                    $diaSem = $dia['dia'];
                    $horaIni = $dia['horaInicio'];
                    $horaFin = $dia['horaFin'];
                    $queryDia = "INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES ('$id', '$diaSem', '$horaIni', '$horaFin')";
                    $conn->query($queryDia);
                }
                header("Location: ../vistaAdmin/gestionar-especialistas.php?res=ok");
                exit();
            } else {
                echo "Error al registrar especialista";
            }
        } else {
            echo "Error al registrar especialista";
        }
    }
}
?>