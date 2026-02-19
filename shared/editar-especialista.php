<?php
require_once 'db.php';

if (isset($_POST['id']) && intval($_POST['id']) > 0) {
    $id_usuario_validar = intval($_POST['id']);
    $sql = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario_validar);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        die("Usuario no encontrado.");
    }
    $stmt->close();
} else {
    die("ID de usuario no proporcionado o no válido.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pro = intval($_POST['id']);
    $especialidad = $_POST['especialidad'];
    $telefono = $_POST['telefono'];
    $dias = isset($_POST['dias']) ? $_POST['dias'] : [];

    if ($id_pro > 0 && !empty($especialidad) && !empty($telefono)) {

        $sql = "UPDATE profesionales SET id_esp = ?, telefono = ? WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $especialidad, $telefono, $id_pro);
        $stmt->execute();

        $stmt_del = $conn->prepare("DELETE FROM profesionales_horarios WHERE idPro = ?");
        $stmt_del->bind_param("i", $id_pro);
        $stmt_del->execute();
        $stmt_del->close();

        if (!empty($dias)) {
            $insertStmt = $conn->prepare("INSERT INTO profesionales_horarios (idPro, diaSem, horaIni, horaFin) VALUES (?, ?, ?, ?)");
            foreach ($dias as $dia) {
                $diaSem = $dia['dia'];
                $horaIni = $dia['horaInicio'];
                $horaFin = $dia['horaFin'];

                if (!empty($diaSem) && !empty($horaIni) && !empty($horaFin)) {
                    $insertStmt->bind_param("isss", $id_pro, $diaSem, $horaIni, $horaFin);
                    $insertStmt->execute();
                }
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