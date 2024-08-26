<?php
include 'db.php';

if (isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['idCurso'])) {
    $idCurso = intval($_GET['idCurso']);

    // Consulta para obtener detalles del curso
    $sql = "SELECT curso.Nombre AS NombreCurso, grado.NroGrado AS Grado, grado.Seccion, CONCAT_WS(' ', datos_personales.Nombre_apellido) AS Docente
            FROM grado
            JOIN horario ON grado.idGrado = horario.idGrado
            JOIN curso ON horario.idCurso = curso.idCurso
            JOIN maestro_horario ON horario.idHorario = maestro_horario.idHorario
            JOIN user_maestro ON maestro_horario.idUser_maestro = user_maestro.idUser_maestro
            JOIN datos_personales ON user_maestro.idUsuario = datos_personales.idUsuario
            WHERE curso.idCurso = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idCurso);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details['courseName'] = $row['NombreCurso'];
        $details['data'][] = [
            'Grado' => $row['Grado'],
            'Seccion' => $row['Seccion'],
            'Docente' => $row['Docente']
        ];
    }

    // Enviar datos JSON como respuesta
    header('Content-Type: application/json');
    echo json_encode($details);
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link rel="stylesheet" href="styles_cursos.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggle-form');
            const formContainer = document.getElementById('form-container');
            const cancelButton = document.getElementById('cancel-form');
            const closeButton = document.getElementById('modal-close-button');

            toggleButton.addEventListener('click', function() {
                if (formContainer.style.display === 'none' || formContainer.style.display === '') {
                    formContainer.style.display = 'block';
                } else {
                    formContainer.style.display = 'none';
                }
            });

            cancelButton.addEventListener('click', function() {
                formContainer.style.display = 'none';
            });

            closeButton.addEventListener('click', function() {
                document.getElementById('modal').style.display = 'none';
            });
        });

        function showModal(idCurso) {
            fetch('cursos.php?action=get_details&idCurso=' + idCurso)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const tbody = document.querySelector('#modal-table tbody');
                    const modalTitle = document.querySelector('#modal-title');

                    tbody.innerHTML = ''; // Limpiar contenido previo

                    // Usar un Set para rastrear secciones ya mostradas
                    const shownSections = new Set();

                    data.data.forEach(item => {
                        // Verificar si la sección ya ha sido mostrada
                        const sectionKey = `${item.Grado}-${item.Seccion}`;
                        if (!shownSections.has(sectionKey)) {
                            shownSections.add(sectionKey);
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `<td>${item.Grado}</td><td>${item.Seccion}</td><td>${item.Docente}</td>`;
                            tbody.appendChild(row);
                        }
                    });

                    // Actualizar el título del modal
                    modalTitle.textContent = `Detalles del Curso: ${data.courseName}`;

                    document.getElementById('modal').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <div class="course-container">
        <div class="course-header">
            <h1>Lista de Cursos</h1>
        </div> 
        <button id="toggle-form" class="btn-add-course">Agregar Nuevo Curso <i class="fas fa-plus"></i></button>
        <div id="form-container" class="form-container" style="display: none;">
            <form action="register_curso.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre del Curso:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Agregar Curso</button>
                <button type="button" id="cancel-form" class="btn-cancel">Cancelar</button>
            </form>
        </div>   
        <table>
            <thead>
                <tr>
                    <th>Nombre del Curso</th>
                    <th>Descripción</th>
                    <th>Docentes asignados</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consultar los cursos
                $sql = "SELECT idCurso, Nombre, Descripcion FROM curso";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row["Nombre"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["Descripcion"]) . '</td>';
                        echo '<td><button class="btn-view-list" onclick="showModal(' . $row["idCurso"] . ')">Ver Lista</button></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3">No hay cursos disponibles</td></tr>';
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 id="modal-title">Detalles del Curso</h2>
            <table id="modal-table">
                <thead>
                    <tr>
                        <th>Grado</th>
                        <th>Sección</th>
                        <th>Docente</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se llenarán con JavaScript -->
                </tbody>
            </table>
            <div class="modal-footer">
                <button id="modal-close-button" class="btn-close">Aceptar</button>
            </div>
        </div>
    </div>
</body>
</html>
