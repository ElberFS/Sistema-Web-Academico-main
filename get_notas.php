<?php
include 'db.php';

if (isset($_GET['idEvaluacion'])) {
    $idEvaluacion = intval($_GET['idEvaluacion']);

    // Obtener el grado y sección de la evaluación
    $sql_curso = "
        SELECT g.NroGrado, g.Seccion
        FROM evaluacion e
        JOIN calificacion c ON e.idEvaluacion = c.idEvaluacion
        JOIN user_estudiante ue ON c.idUser_estudiante = ue.idUser_estudiante
        JOIN grado g ON ue.idGrado = g.idGrado
        WHERE e.idEvaluacion = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql_curso);
    $stmt->bind_param("i", $idEvaluacion);
    $stmt->execute();
    $curso_result = $stmt->get_result();
    $curso = $curso_result->fetch_assoc();
    $stmt->close();

    $NroGrado = $curso['NroGrado'];
    $Seccion = $curso['Seccion'];

    // Obtener todos los estudiantes del grado y sección
    $sql_eval = "
        SELECT ue.idUser_estudiante, dp.Nombre_apellido, c.Nota, c.Archivo
        FROM user_estudiante ue
        JOIN datos_personales dp ON ue.idUsuario = dp.idUsuario
        LEFT JOIN calificacion c ON ue.idUser_estudiante = c.idUser_estudiante AND c.idEvaluacion = ?
        JOIN grado g ON ue.idGrado = g.idGrado
        WHERE g.NroGrado = ? AND g.Seccion = ?
    ";

    $stmt = $conn->prepare($sql_eval);
    $stmt->bind_param("iis", $idEvaluacion, $NroGrado, $Seccion);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = '';
    while ($row = $result->fetch_assoc()) {
        $archivo = !empty($row['Archivo']) ? 'data:application/octet-stream;base64,' . base64_encode($row['Archivo']) : '';
        $rows .= '<tr>
                    <td>' . htmlspecialchars($row['Nombre_apellido']) . '</td>
                    <td>' . ($archivo ? '<a href="' . $archivo . '" download>Descargar</a>' : 'No disponible') . '</td>
                    <td><input type="text" class="nota" value="' . htmlspecialchars($row['Nota']) . '" size="5"></td>
                  </tr>';
    }
    if (empty($rows)) {
        $rows = '<tr><td colspan="3">No se encontraron notas para esta evaluación.</td></tr>';
    }

    echo $rows;
    $stmt->close();
    $conn->close();
}
?>
