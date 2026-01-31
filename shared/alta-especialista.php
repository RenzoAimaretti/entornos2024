<?php
session_start();

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['tel'];
    $especialidad = $_POST['esp'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $dias = $_POST['dias'];

    if ($password != $repassword) {
        echo "Las contraseñas no coinciden";
        echo $password;
        echo $repassword;
    } else {
        $queryUser = "INSERT INTO usuarios (nombre,email,password,tipo) VALUES ('$nombre','$email','$password','especialista')";
        $resultUser = $conn->query($queryUser);
        if ($resultUser) {
            $id = $conn->insert_id;
            $queryProf = "INSERT INTO profesionales (id,telefono,id_esp) VALUES ('$id','$telefono','$especialidad')";
            $resultProf = $conn->query($queryProf);
            if ($resultProf) {
                echo "Especialista registrado con éxito";

                foreach ($dias as $dia) {
                    $diaSem = $dia['dia'];
                    $horaIni = $dia['horaInicio'];
                    $horaFin = $dia['horaFin'];
                    $queryDia = "INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES ('$id', '$diaSem', '$horaIni', '$horaFin')";
                    $conn->query($queryDia);
                }

                $_SESSION['usuario_id'] = $id;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_tipo'] = 'especialista';
                header("Location: ../vistaAdmin/gestionar-especialistas.php");
            } else {
                echo "Error al registrar especialista";
            }
        } else {
            echo "Error al registrar especialista";
        }
    }
}
?>