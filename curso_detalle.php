<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$idCurso = isset($_GET['idCurso']) ? intval($_GET['idCurso']) : 0;
$NroGrado = isset($_GET['NroGrado']) ? intval($_GET['NroGrado']) : 0;
$Seccion = isset($_GET['Seccion']) ? $_GET['Seccion'] : '';
$idUsuario = $_SESSION['user_id'];

// Obtener detalles (Nombre) del curso
$sql = "SELECT Nombre FROM curso WHERE idCurso = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idCurso);
$stmt->execute();
$stmt->bind_result($cursoNombre);
$stmt->fetch();
$stmt->close();

// Obtener evaluaciones con fechas y notas de calificaciones
$sql = "SELECT e.idEvaluacion, te.Descripcion AS TipoEvaluacion, e.Descripcion AS EvaluacionDescripcion, e.Fecha AS Fecha
        FROM evaluacion e
        JOIN tipo_evaluacion te ON e.idTipo_evaluacion = te.idTipo_evaluacion
        JOIN calificacion c ON e.idEvaluacion = c.idEvaluacion
        JOIN user_estudiante ue ON c.idUser_estudiante = ue.idUser_estudiante
        JOIN grado g ON ue.idGrado = g.idGrado
        WHERE c.idCurso = ? AND g.NroGrado = ? AND g.Seccion = ?
        GROUP BY e.idEvaluacion";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $idCurso, $NroGrado, $Seccion);
$stmt->execute();
$result = $stmt->get_result();

$evaluaciones = [];
while ($row = $result->fetch_assoc()) {
    $evaluaciones[] = $row;
}

// Obtener tipos de evaluación para el formulario
$sql = "SELECT idTipo_evaluacion, Descripcion FROM tipo_evaluacion";
$result = $conn->query($sql);
$tiposEvaluacion = [];
while ($row = $result->fetch_assoc()) {
    $tiposEvaluacion[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Curso</title>
    <link rel="stylesheet" href="styles_courses_teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="courses-header">
        <h1>Curso: <?php echo htmlspecialchars($cursoNombre); ?></h1>
    </div>

    <main>
        <div class="cards-container">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-book"></i> <!-- Icono para recursos -->
                </div>
                <div class="card-content">
                    <h2>Recursos</h2>
                    <p>Aquí aparecerán los recursos del curso.</p>
                    <button id="btnRecursos" class="btn-view">Ver Recursos</button>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-clipboard-list"></i> <!-- Icono para calificaciones -->
                </div>
                <div class="card-content">
                    <h2>Calificaciones</h2>
                    <p>Aquí aparecerán las calificaciones del curso.</p>
                    <button id="btnCalificaciones" class="btn-view">Ver Calificaciones</button>
                </div>
            </div>
        </div>

        <!-- Botón para añadir nueva evaluación -->
        <button id="toggleFormButton" class="btn-add">Añadir Nueva Evaluación</button>

        <!-- Formulario para añadir nueva evaluación -->
        <div class="form-eval-container" id="evaluationForm" style="display: none;">
            <h2>Añadir Nueva Evaluación</h2>
            <form id="addEvaluationForm">
                <label for="descripcion">Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" required>
                
                <label for="tipoEvaluacion">Tipo de Evaluación:</label>
                <select id="tipoEvaluacion" name="tipoEvaluacion" required>
                    <?php foreach ($tiposEvaluacion as $tipo): ?>
                        <option value="<?php echo $tipo['idTipo_evaluacion']; ?>">
                            <?php echo htmlspecialchars($tipo['Descripcion']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-add">Añadir Evaluación</button>
            </form>
        </div>

        <div class="grades-table" id="tablaCalificaciones" style="display: none;">
            <h2>Calificaciones del Curso</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tipo de Evaluación</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluaciones as $evaluacion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($evaluacion['TipoEvaluacion']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($evaluacion['Fecha']))); ?></td>
                            <td><?php echo htmlspecialchars($evaluacion['EvaluacionDescripcion']); ?></td>
                            <td><button class="btn-view-list" data-id="<?php echo $evaluacion['idEvaluacion']; ?>">Ver lista</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <h2>Notas de la Evaluación</h2>
            <div id="data-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre de Estudiante</th>
                            <th>Archivo</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody id="notesTableBody">
                        <!-- Las notas se llenarán dinámicamente con JavaScript -->
                    </tbody>
                </table>
                <div class="form-container">
                    <button id="saveButton" class="btn-save" data-id-evaluacion="">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnCalificaciones = document.getElementById('btnCalificaciones');
            const tablaCalificaciones = document.getElementById('tablaCalificaciones');
            const modal = document.getElementById('modal');
            const closeModal = document.getElementById('closeModal');
            const notesTableBody = document.getElementById('notesTableBody');
            const saveBtn = document.getElementById('saveButton');
            const addEvaluationForm = document.getElementById('addEvaluationForm');
            const toggleFormButton = document.getElementById('toggleFormButton');
            const evaluationForm = document.getElementById('evaluationForm');

            //Para el boton "Ver calificaciones" y aparezca la "Tabla de evaluaciones"
            btnCalificaciones.addEventListener('click', function() {
                if (tablaCalificaciones.style.display === 'none' || tablaCalificaciones.style.display === '') {
                    tablaCalificaciones.style.display = 'block';
                    btnCalificaciones.textContent = 'Ocultar Calificaciones';
                } else {
                    tablaCalificaciones.style.display = 'none';
                    btnCalificaciones.textContent = 'Ver Calificaciones';
                }
            });
            
            //Para boton "Añadir nueva evaluacion" y aparezca "Formulario para nueva evaluacion"
            toggleFormButton.addEventListener('click', function() {
                if (evaluationForm.style.display === 'none' || evaluationForm.style.display === '') {
                    evaluationForm.style.display = 'block';
                    toggleFormButton.textContent = 'Ocultar Formulario';
                } else {
                    evaluationForm.style.display = 'none';
                    toggleFormButton.textContent = 'Añadir Nueva Evaluación';
                }
            });
            
            //Para el boton "Ver lista"
            document.querySelectorAll('.btn-view-list').forEach(button => {
                button.addEventListener('click', function() {
                    const idEvaluacion = this.getAttribute('data-id');

                    fetch(`get_notas.php?idEvaluacion=${idEvaluacion}`)
                        .then(response => response.text())
                        .then(data => {
                            notesTableBody.innerHTML = data;
                            modal.style.display = 'block';
                            saveBtn.dataset.idEvaluacion = idEvaluacion; // Establece el ID de la evaluación en el botón de guardar
                        });
                });
            });

            //Para cerrar el modal"
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            //Para obtener los datos del modal
            /*saveBtn.addEventListener('click', function() {
                // Recolectar todas las notas del modal
                const notas = [];
                document.querySelectorAll('#notesTableBody tr').forEach(row => {
                    const idEstudiante = row.querySelector('input.nota').getAttribute('data-id');
                    const nota = row.querySelector('input.nota').value;
                    notas.push({ idEstudiante, nota });
                });

                // Se envia a update_notas.php
                fetch('update_notas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notas })
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    modal.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });*/
            
            //Obtener nota del idUser_estudiante y actualizarla
            document.addEventListener('DOMContentLoaded', () => {
                const saveBtn = document.getElementById('saveButton');

                saveBtn.addEventListener('click', () => {
                    const notas = [];
                    
                    document.querySelectorAll('#notesTableBody .nota').forEach(input => {
                        const idUser_estudiante = input.dataset.id; // Verifica que el atributo sea 'data-id'
                        const nota = input.value;
                        
                        if (idUser_estudiante && nota) {
                            notas.push({ idUser_estudiante, nota });
                        } else {
                            console.warn('Datos incompletos para un estudiante:', idUser_estudiante, nota);
                        }
                    });

                    const idEvaluacion = saveBtn.dataset.idEvaluacion;
                    if (notas.length > 0 && idEvaluacion) {
                        actualizarNotas(notas, idEvaluacion);
                    } else {
                        console.warn('Datos incompletos para actualizar las notas');
                    }
                });

                function actualizarNotas(notas, idEvaluacion) {
                    fetch('update_notas.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ notas, idEvaluacion })
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data); // Verifica la respuesta del servidor
                        alert('Notas actualizadas con éxito');
                        modal.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
                        
            /*document.addEventListener('DOMContentLoaded', function() {
                const addEvaluationForm = document.getElementById('addEvaluationForm');

                addEvaluationForm.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevenir el envío estándar del formulario
                    
                    const formData = new FormData(this);

                    fetch('add_evaluation.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data); // Mostrar el mensaje de éxito o error
                        addEvaluationForm.reset(); // Limpiar el formulario
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });*/

            addEvaluationForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const descripcion = document.getElementById('descripcion').value;
                const tipoEvaluacion = document.getElementById('tipoEvaluacion').value;

                fetch('add_evaluation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `descripcion=${encodeURIComponent(descripcion)}&tipoEvaluacion=${encodeURIComponent(tipoEvaluacion)}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    addEvaluationForm.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>
