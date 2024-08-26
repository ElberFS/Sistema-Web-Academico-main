<?php
include 'db.php';

// Validar y sanear los datos del formulario
$idCurso = intval($_POST['idCurso']);
$idDia = intval($_POST['idDia']);
$Hora_inicio = $_POST['Hora_inicio'];
$Hora_final = $_POST['Hora_final'];
$Turno = intval($_POST['Turno']);
$idGrado = intval($_POST['idGrado']);
$idUser_maestro = intval($_POST['idUser_maestro']);

// Consulta para insertar un nuevo horario
$sql = "INSERT INTO horario (idCurso, idDia, Hora_inicio, Hora_final, Turno, idGrado) VALUES (?, ?, ?, ?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iissii", $idCurso, $idDia, $Hora_inicio, $Hora_final, $Turno, $idGrado);
    if ($stmt->execute()) {
        $idHorario = $conn->insert_id;
        
        // Insertar relación entre horario y maestro
        $sqlMaestro = "INSERT INTO maestro_horario (idUser_maestro, idHorario) VALUES (?, ?)";
        if ($stmtMaestro = $conn->prepare($sqlMaestro)) {
            $stmtMaestro->bind_param("ii", $idUser_maestro, $idHorario);
            $stmtMaestro->execute();
        }

        header("Location: dashboard.php?page=horarios_admin");
        exit();
    } else {
        echo "Error al agregar horario: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Error en la consulta: " . $conn->error;
}

$conn->close();
?>
