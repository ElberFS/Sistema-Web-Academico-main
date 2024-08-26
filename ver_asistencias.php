<?php
include 'db.php';

if (!isset($_GET['fecha']) || !isset($_GET['anoAcademico'])) {
    exit('Fecha o Año Académico no proporcionado.');
}

$fecha = $_GET['fecha'];
$anoAcademico = $_GET['anoAcademico'];
list($gradoId, $seccion) = explode('-', $anoAcademico);

// Obtener todos los estudiantes para el grado y sección seleccionados
$queryEstudiantes = "SELECT ue.idUser_estudiante, dp.Nombre_apellido
                     FROM user_estudiante ue
                     JOIN datos_personales dp ON ue.idUsuario = dp.idUsuario
                     JOIN grado g ON ue.idGrado = g.idGrado
                     WHERE ue.idGrado = ? AND g.Seccion = ?";
$stmt = $conn->prepare($queryEstudiantes);
$stmt->bind_param("is", $gradoId, $seccion);
$stmt->execute();
$estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener la asistencia de los estudiantes para la fecha seleccionada
$queryAsistencias = "SELECT a.idUser_estudiante, a.Asist_estado
                     FROM asistencia a
                     WHERE a.Fecha = ?";
$stmt = $conn->prepare($queryAsistencias);
$stmt->bind_param("s", $fecha);
$stmt->execute();
$asistencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Crear un array para fácil acceso a la asistencia de cada estudiante
$asistenciaMap = [];
foreach ($asistencias as $asistencia) {
    $asistenciaMap[$asistencia['idUser_estudiante']] = $asistencia['Asist_estado'];
}

// Mostrar los estudiantes y su estado de asistencia
if (!empty($estudiantes)) {
    echo '<table>
            <thead>
                <tr>
                    <th>Nombre del Estudiante</th>
                    <th>Estado de Asistencia</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($estudiantes as $estudiante) {
        $estado = isset($asistenciaMap[$estudiante['idUser_estudiante']]) ? 
                  ($asistenciaMap[$estudiante['idUser_estudiante']] ? 'Asistió' : 'No Asistió') : 
                  'No Asistió';
        echo '<tr>
                <td>' . htmlspecialchars($estudiante['Nombre_apellido']) . '</td>
                <td>' . htmlspecialchars($estado) . '</td>
              </tr>';
    }
    echo '</tbody></table>';
} else {
    echo 'No se encontraron estudiantes para el grado y sección seleccionados.';
}
?>
