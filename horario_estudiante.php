<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el grado del estudiante
$query = "SELECT idGrado FROM user_estudiante WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$grado_id = $student['idGrado'];

// Obtener el grado y sección del estudiante
$query = "SELECT g.NroGrado, g.Seccion FROM grado g WHERE g.idGrado = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $grado_id);
$stmt->execute();
$result = $stmt->get_result();
$grado = $result->fetch_assoc();
$nro_grado = $grado['NroGrado'];
$seccion = $grado['Seccion'];

// Obtener el turno (mañana o tarde) del grado
$query = "SELECT Turno FROM horario WHERE idGrado = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $grado_id);
$stmt->execute();
$result = $stmt->get_result();
$horario = $result->fetch_assoc();
$turno = $horario['Turno'];

// Obtener los horarios del estudiante
$query = "SELECT h.Hora_inicio, h.Hora_final, d.Descripcion AS dia, c.Nombre AS curso
          FROM horario h
          JOIN dia d ON h.idDia = d.idDia
          JOIN curso c ON h.idCurso = c.idCurso
          WHERE h.idGrado = ? AND h.Turno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $grado_id, $turno);
$stmt->execute();
$result = $stmt->get_result();
$horarios = [];
while ($row = $result->fetch_assoc()) {
    $horarios[$row['dia']][] = [
        'hora_inicio' => $row['Hora_inicio'],
        'hora_final' => $row['Hora_final'],
        'curso' => $row['curso']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario</title>
    <link rel="stylesheet" href="styles_horario.css">
</head>
<body>
    <div class="schedule-container">
        <h1>Horario</h1>
        <div class="header">
            <div class="year-select">
                <label for="year">Año escolar:</label>
            </div>
            <div class="student-info">
                <span>Grado: <?php echo htmlspecialchars($nro_grado); ?></span>
                <span>Sección: <?php echo htmlspecialchars($seccion); ?></span>
            </div>
            <button class="print-button" onclick="window.print()">Imprimir Horario</button>
        </div>
        <div class="schedule">
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Horarios disponibles según el turno
                        $horarios_disponibles = $turno == 1
                            ? ['08:00 - 08:40', '08:40 - 09:20', '09:20 - 10:00', '10:00 - 10:40', '10:40 - 11:20', '11:20 - 12:00', '12:00 - 12:40', '12:40 - 13:20']
                            : ['14:00 - 14:40', '14:40 - 15:20', '15:20 - 16:00', '16:00 - 16:40', '16:40 - 17:20', '17:20 - 18:00', '18:00 - 18:40', '18:40 - 19:20'];

                        foreach ($horarios_disponibles as $hora) {
                            list($hora_inicio, $hora_final) = explode(' - ', $hora);
                            $hora_inicio = strtotime($hora_inicio);
                            $hora_final = strtotime($hora_final);

                            // Agregar fila para recreo
                            if ($hora_inicio === strtotime('10:00') && $hora_final === strtotime('10:40')) {
                                echo '<tr class="recreo">';
                                echo "<td>{$hora}</td>";

                                foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia) {
                                    echo '<td>Recreo</td>';
                                }
                                echo '</tr>';
                            } else {
                                // Mostrar horarios de clases
                                echo '<tr>';
                                echo "<td>{$hora}</td>";

                                foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia) {
                                    $cursos = [];
                                    if (isset($horarios[$dia])) {
                                        foreach ($horarios[$dia] as $curso) {
                                            $curso_inicio = strtotime($curso['hora_inicio']);
                                            $curso_final = strtotime($curso['hora_final']);

                                            // Verifica si el curso debe aparecer en esta fila
                                            if ($hora_inicio < $curso_final && $hora_final > $curso_inicio) {
                                                // Si el curso cubre parte de esta hora, agregarlo
                                                $cursos[] = $curso['curso'];
                                            }
                                        }
                                    }
                                    echo '<td>' . (!empty($cursos) ? implode(', ', $cursos) : '') . '</td>';
                                }
                                echo '</tr>';
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
