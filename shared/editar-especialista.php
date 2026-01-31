<?php
require_once 'db.php';

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
    $dias = isset($_POST['dias']) ? $_POST['dias'] : [];

    if (!empty($especialidad) && !empty($telefono)) {

        $sql = "UPDATE profesionales SET id_esp = ?, telefono = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $especialidad, $telefono, $id_pro);
        $stmt->execute();

        $conn->query("DELETE FROM profesionales_horarios WHERE idPro = $id_pro");

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

        echo '<form id="redirigir" action="' . htmlspecialchars($_SERVER['HTTP_REFERER']) . '" method="post">';
        echo '<input type="hidden" name="id" value="' . $id_pro . '">';
        echo '</form>';
        echo '<script>document.getElementById("redirigir").submit();</script>';
        exit();
    }
}
$conn->close();
?>