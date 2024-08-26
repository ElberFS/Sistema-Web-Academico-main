<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el id del maestro
$query = "SELECT idUser_maestro FROM user_maestro WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$maestro = $result->fetch_assoc();
$maestro_id = $maestro['idUser_maestro'];

// Obtener los horarios del maestro
$query = "SELECT h.Hora_inicio, h.Hora_final, d.Descripcion AS dia, c.Nombre AS curso, g.NroGrado, g.Seccion, h.Turno
          FROM horario h
          JOIN dia d ON h.idDia = d.idDia
          JOIN curso c ON h.idCurso = c.idCurso
          JOIN grado g ON h.idGrado = g.idGrado
          JOIN maestro_horario mh ON h.idHorario = mh.idHorario
          WHERE mh.idUser_maestro = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $maestro_id);
$stmt->execute();
$result = $stmt->get_result();
$horarios = [];
while ($row = $result->fetch_assoc()) {
    $turno = $row['Turno'] == 1 ? 'Mañana' : 'Tarde';
    $horarios[$turno][$row['dia']][] = [
        'hora_inicio' => $row['Hora_inicio'],
        'hora_final' => $row['Hora_final'],
        'curso' => $row['curso'],
        'grado' => $row['NroGrado'],
        'seccion' => $row['Seccion']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario del Docente</title>
    <link rel="stylesheet" href="styles_horario.css">
    <style>
        .recreo {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="schedule-container">
        <h1>Horario del Docente</h1>
        <div class="header">
            <button class="print-button" onclick="window.print()">Imprimir Horario</button>
        </div>
        <div class="schedule">
            <h2>Turno Mañana</h2>
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
                    $horarios_disponibles_mañana = ['08:00 - 08:40', '08:40 - 09:20', '09:20 - 10:00', '10:00 - 10:40', '10:40 - 11:20', '11:20 - 12:00', '12:00 - 12:40', '12:40 - 13:20'];

                    foreach ($horarios_disponibles_mañana as $hora) {
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
                                if (isset($horarios['Mañana'][$dia])) {
                                    foreach ($horarios['Mañana'][$dia] as $curso) {
                                        $curso_inicio = strtotime($curso['hora_inicio']);
                                        $curso_final = strtotime($curso['hora_final']);

                                        // Verifica si el curso debe aparecer en esta fila
                                        if ($hora_inicio < $curso_final && $hora_final > $curso_inicio) {
                                            // Si el curso cubre parte de esta hora, agregarlo
                                            $cursos[] = $curso['curso'] . ' (' . $curso['grado'] . '-' . $curso['seccion'] . ')';
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

            <h2>Turno Tarde</h2>
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
                    $horarios_disponibles_tarde = ['14:00 - 14:40', '14:40 - 15:20', '15:20 - 16:00', '16:00 - 16:40', '16:40 - 17:20', '17:20 - 18:00', '18:00 - 18:40', '18:40 - 19:20'];

                    foreach ($horarios_disponibles_tarde as $hora) {
                        list($hora_inicio, $hora_final) = explode(' - ', $hora);
                        $hora_inicio = strtotime($hora_inicio);
                        $hora_final = strtotime($hora_final);

                        // Agregar fila para recreo
                        if ($hora_inicio === strtotime('16:00') && $hora_final === strtotime('16:40')) {
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
                                if (isset($horarios['Tarde'][$dia])) {
                                    foreach ($horarios['Tarde'][$dia] as $curso) {
                                        $curso_inicio = strtotime($curso['hora_inicio']);
                                        $curso_final = strtotime($curso['hora_final']);

                                        // Verifica si el curso debe aparecer en esta fila
                                        if ($hora_inicio < $curso_final && $hora_final > $curso_inicio) {
                                            // Si el curso cubre parte de esta hora, agregarlo
                                            $cursos[] = $curso['curso'] . ' (' . $curso['grado'] . '-' . $curso['seccion'] . ')';
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
