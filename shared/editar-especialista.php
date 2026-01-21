<?php
// Conexión a la base de datos
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Crear conexión
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        die("Usuario no encontrado.");
    }
    $stmt->close();
} else {
    die("ID de usuario no proporcionado.");

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pro = intval($_POST['id']);
    $especialidad = $_POST['especialidad'];
    $telefono = $_POST['telefono'];
    $dias = isset($_POST['dias']) ? $_POST['dias'] : []; // Si no hay días, enviamos array vacío

    if (!empty($especialidad) && !empty($telefono)) {
        // A. Actualizamos datos básicos del profesional
        $sql = "UPDATE profesionales SET id_esp = ?, telefono = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $especialidad, $telefono, $id_pro);
        $stmt->execute();

        // B. Limpiamos TODOS los horarios viejos para este profesional
        $conn->query("DELETE FROM profesionales_horarios WHERE idPro = $id_pro");

        // C. Insertamos los que vienen del formulario (nuevos y editados)
        if (!empty($dias)) {
            $insertStmt = $conn->prepare("INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES (?, ?, ?, ?)");
            foreach ($dias as $dia) {
                $diaSem = $dia['dia'];
                $horaIni = $dia['horaInicio'];
                $horaFin = $dia['horaFin'];

                $insertStmt->bind_param("isss", $id_pro, $diaSem, $horaIni, $horaFin);
                $insertStmt->execute();
            }
            $insertStmt->close();
        }

        // Redirección POST (como ya tenías)
        echo '<form id="redirigir" action="' . htmlspecialchars($_SERVER['HTTP_REFERER']) . '" method="post">';
        echo '<input type="hidden" name="id" value="' . $id_pro . '">';
        echo '</form>';
        echo '<script>document.getElementById("redirigir").submit();</script>';
        exit();
    }
}
$conn->close();
?>