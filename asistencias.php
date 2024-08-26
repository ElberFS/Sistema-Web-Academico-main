<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$idUsuario = $_SESSION['user_id'];

// Obtener el idUser_maestro a partir del idUsuario
$queryMaestro = "SELECT idUser_maestro FROM user_maestro WHERE idUsuario = ?";
$stmt = $conn->prepare($queryMaestro);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$idUser_maestro = $result->fetch_assoc()['idUser_maestro'];
$stmt->close();

// Obtener los grados y secciones del docente
$queryGrados = "SELECT DISTINCT g.idGrado, g.NroGrado, g.Seccion 
                FROM grado g 
                JOIN horario h ON g.idGrado = h.idGrado
                JOIN maestro_horario mh ON h.idHorario = mh.idHorario
                WHERE mh.idUser_maestro = ?";
$stmt = $conn->prepare($queryGrados);
$stmt->bind_param("i", $idUser_maestro);
$stmt->execute();
$grados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener los estudiantes para un grado y sección específicos
$estudiantes = [];
$anoAcademicoSeleccionado = '';
$mostrarTablaEstudiantes = true;

if (isset($_POST['anoAcademico'])) {
    $anoAcademicoSeleccionado = $_POST['anoAcademico'];
    list($gradoId, $seccion) = explode('-', $anoAcademicoSeleccionado);
    
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
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && isset($_POST['asistencia'])) {
    $fecha = $_POST['fecha'];
    foreach ($_POST['asistencia'] as $idUser_estudiante => $asistencia) {
        $estado = isset($asistencia) ? 1 : 0;
        $queryInsertAsistencia = "INSERT INTO asistencia (Asist_estado, Fecha, idUser_estudiante) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($queryInsertAsistencia);
        $stmt->bind_param("isi", $estado, $fecha, $idUser_estudiante);
        $stmt->execute();
        $stmt->close();
    }
    
    // Ocultar la tabla de estudiantes después de registrar la asistencia
    $mostrarTablaEstudiantes = false;
}

// Obtener todas las fechas de asistencias registradas
$queryFechasAsistencias = "SELECT DISTINCT DATE(Fecha) as Fecha FROM asistencia ORDER BY Fecha DESC";
$stmt = $conn->prepare($queryFechasAsistencias);
$stmt->execute();
$fechasAsistencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencias</title>
    <link rel="stylesheet" href="styles_asistencias.css">
</head>
<body>
    <h1>Registro de Asistencias</h1>

    <form method="POST" action="">
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>
        
        <label for="anoAcademico">Año Académico:</label>
        <select id="anoAcademico" name="anoAcademico" required>
            <option value="">Seleccione un año académico</option>
            <?php foreach ($grados as $grado): ?>
                <option value="<?= htmlspecialchars($grado['idGrado']) . '-' . htmlspecialchars($grado['Seccion']) ?>"
                    <?= ($anoAcademicoSeleccionado == htmlspecialchars($grado['idGrado']) . '-' . htmlspecialchars($grado['Seccion'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($grado['NroGrado']) . ' - ' . htmlspecialchars($grado['Seccion']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar Estudiantes</button>
    </form>

    <?php if ($mostrarTablaEstudiantes && !empty($estudiantes)): ?>
        <h2>Lista de Estudiantes</h2>
        <form method="POST" action="">
            <input type="hidden" name="anoAcademico" value="<?= htmlspecialchars($anoAcademicoSeleccionado) ?>">
            <input type="hidden" name="fecha" value="<?= htmlspecialchars($_POST['fecha']) ?>">
            <table id="tablaEstudiantes">
                <thead>
                    <tr>
                        <th>Nombre del Estudiante</th>
                        <th>Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $estudiante): ?>
                        <tr>
                            <td><?= htmlspecialchars($estudiante['Nombre_apellido']) ?></td>
                            <td>
                                <input type="checkbox" name="asistencia[<?= $estudiante['idUser_estudiante'] ?>]">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Registrar Asistencia</button>
        </form>
    <?php endif; ?>

    <h2>Fechas de Asistencias Registradas</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ver Asistencias</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fechasAsistencias as $fecha): ?>
                <tr>
                    <td><?= htmlspecialchars($fecha['Fecha']) ?></td>
                    <td>
                        <button class="view-attendance-btn" data-fecha="<?= htmlspecialchars($fecha['Fecha']) ?>">Ver Asistencias</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal for viewing attendance -->
    <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ver Asistencias</h2>
            <form id="attendanceForm">
                <label for="anoAcademicoSelect">Año Académico:</label>
                <select id="anoAcademicoSelect" name="anoAcademicoSelect" required>
                    <option value="">Seleccione un año académico</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= htmlspecialchars($grado['idGrado']) . '-' . htmlspecialchars($grado['Seccion']) ?>">
                            <?= htmlspecialchars($grado['NroGrado']) . ' - ' . htmlspecialchars($grado['Seccion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Mostrar Asistencias</button>
            </form>
            <div id="attendanceList"></div>
        </div>
    </div>

    <!-- JavaScript para establecer la fecha actual -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha').value = today;
        });

        // Ocultar la tabla de estudiantes si se registró la asistencia
        <?php if (!$mostrarTablaEstudiantes): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const tablaEstudiantes = document.getElementById('tablaEstudiantes');
                if (tablaEstudiantes) {
                    tablaEstudiantes.style.display = 'none';
                }
            });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById("attendanceModal");
            var btns = document.querySelectorAll(".view-attendance-btn");
            var span = document.getElementsByClassName("close")[0];
            var attendanceForm = document.getElementById("attendanceForm");
            var attendanceList = document.getElementById("attendanceList");

            // Open the modal
            btns.forEach(function(btn) {
                btn.onclick = function() {
                    var fecha = this.getAttribute("data-fecha");
                    modal.style.display = "block";

                    // Fetch and display the attendance data
                    attendanceForm.onsubmit = function(e) {
                        e.preventDefault();
                        var anoAcademico = document.getElementById("anoAcademicoSelect").value;
                        fetch(`ver_asistencias.php?fecha=${fecha}&anoAcademico=${anoAcademico}`)
                            .then(response => response.text())
                            .then(data => {
                                attendanceList.innerHTML = data;
                            });
                    };
                };
            });

            // Close the modal
            span.onclick = function() {
                modal.style.display = "none";
            };

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };
        });
    </script>
</body>
</html>
