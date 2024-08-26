<?php
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Verificar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $idHorario = intval($_POST['idHorario']);
    $idCurso = intval($_POST['idCurso']);
    $idDia = intval($_POST['idDia']);
    $Hora_inicio = $_POST['Hora_inicio'];
    $Hora_final = $_POST['Hora_final'];
    $Turno = intval($_POST['Turno']);
    $idGrado = intval($_POST['idGrado']);
    $idUser_maestro = intval($_POST['idUser_maestro']);

    // Actualizar el horario
    $sql = "UPDATE horario SET idCurso = ?, idDia = ?, Hora_inicio = ?, Hora_final = ?, Turno = ?, idGrado = ? WHERE idHorario = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iissiii', $idCurso, $idDia, $Hora_inicio, $Hora_final, $Turno, $idGrado, $idHorario);
        $stmt->execute();
        $stmt->close();
    } else {
        echo 'error=Error en la actualización del horario';
        exit();
    }

    // Actualizar el maestro relacionado con el horario
    $sqlMaestro = "UPDATE maestro_horario SET idUser_maestro = ? WHERE idHorario = ?";
    if ($stmtMaestro = $conn->prepare($sqlMaestro)) {
        $stmtMaestro->bind_param('ii', $idUser_maestro, $idHorario);
        $stmtMaestro->execute();
        $stmtMaestro->close();
    } else {
        echo 'error=Error en la actualización del maestro del horario';
        exit();
    }

    // Redirigir de vuelta a la página de horarios con un mensaje de éxito
    header("Location: dashboard.php?page=horarios_admin");
    exit();
}
?>
