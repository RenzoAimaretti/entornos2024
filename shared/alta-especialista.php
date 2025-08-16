<?php
require '../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); // Cambiar la ruta al directorio raíz
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    die("Error: No se pudo cargar el archivo .env. Verifica su existencia.");
}
// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nombre=$_POST['nombre'];
    $email=$_POST['email'];
    $telefono=$_POST['tel'];
    $especialidad=$_POST['esp'];
    $password=$_POST['password'];
    $repassword=$_POST['repassword'];
    $dias = $_POST['dias'];
    // Validar que las contraseñas coincidan
    if($password!=$repassword){
        echo "Las contraseñas no coinciden";
        echo $password;
        echo $repassword;
    }else{
        $queryUser="INSERT INTO usuarios (nombre,email,password,tipo) VALUES ('$nombre','$email','$password','especialista')";
        $resultUser=$conn->query($queryUser);
        if($resultUser){
            $id=$conn->insert_id;
            $queryProf="INSERT INTO profesionales (id,telefono,id_esp) VALUES ('$id','$telefono','$especialidad')";
            $resultProf=$conn->query($queryProf);
            if($resultProf){
                echo "Especialista registrado con éxito";
                // Registrar los días de atención
                foreach ($dias as $dia) {
                    $diaSem = $dia['dia'];
                    $horaIni = $dia['horaInicio'];
                    $horaFin = $dia['horaFin'];
                    $queryDia = "INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES ('$id', '$diaSem', '$horaIni', '$horaFin')";
                    $conn->query($queryDia);
                }
                // Guardar en la sesión
                $_SESSION['usuario_id'] = $id;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_tipo'] = 'especialista';
                header("Location: ../vistaAdmin/gestionar-especialistas.php");
            }else{
                echo "Error al registrar especialista";
            }
        }else{
                echo "Error al registrar especialista";
            }
    }

}
?>