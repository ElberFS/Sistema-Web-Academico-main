<?php
include 'db.php';

if (isset($_GET['id'])) {
    $idHorario = $_GET['id'];

    // Eliminar las relaciones en maestro_horario
    $sql_maestro = "DELETE FROM maestro_horario WHERE idHorario = '$idHorario'";
    $conn->query($sql_maestro);

    // Eliminar el horario
    $sql_horario = "DELETE FROM horario WHERE idHorario = '$idHorario'";
    if ($conn->query($sql_horario) === TRUE) {
        header("Location: dashboard.php?page=horarios_admin");
        exit();
    } else {
        echo "Error: " . $sql_horario . "<br>" . $conn->error;
    }
} else {
    header("Location: dashboard.php?page=horarios_admin");
    exit();
}
?>
