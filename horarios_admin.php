<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$idUsuario = $_SESSION['user_id'];

// Consulta para obtener los horarios
$sql = "SELECT h.idHorario, c.Nombre AS Curso, d.Descripcion AS Dia, h.Hora_inicio, h.Hora_final, dp.Nombre_apellido AS Maestro, g.NroGrado AS Grado, g.Seccion AS Seccion, h.Turno
        FROM horario h
        JOIN curso c ON h.idCurso = c.idCurso
        JOIN dia d ON h.idDia = d.idDia
        JOIN maestro_horario m ON h.idHorario = m.idHorario
        JOIN user_maestro um ON m.idUser_maestro = um.idUser_maestro
        JOIN datos_personales dp ON um.idUsuario = dp.idUsuario
        JOIN grado g ON h.idGrado = g.idGrado";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['idHorario'])) {
        $idHorario = intval($_POST['idHorario']);
        
        $sql = "SELECT * FROM horario WHERE idHorario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idHorario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Imprimir datos en formato HTML
            echo "<input type='hidden' name='idHorario' id='edit-idHorario' value='" . htmlspecialchars($row['idHorario']) . "'>";
            echo "<label for='edit-idCurso'>Curso:</label>";
            echo "<select name='idCurso' id='edit-idCurso'>";
            $cursos = $conn->query("SELECT idCurso, Nombre FROM curso");
            while ($curso = $cursos->fetch_assoc()) {
                $selected = ($curso['idCurso'] == $row['idCurso']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($curso['idCurso']) . "' $selected>" . htmlspecialchars($curso['Nombre']) . "</option>";
            }
            echo "</select>";
            echo "<label for='edit-idDia'>Día:</label>";
            echo "<select name='idDia' id='edit-idDia'>";
            $dias = $conn->query("SELECT idDia, Descripcion FROM dia");
            while ($dia = $dias->fetch_assoc()) {
                $selected = ($dia['idDia'] == $row['idDia']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($dia['idDia']) . "' $selected>" . htmlspecialchars($dia['Descripcion']) . "</option>";
            }
            echo "</select>";
            echo "<label for='edit-Hora_inicio'>Hora Inicio:</label>";
            echo "<input type='time' name='Hora_inicio' id='edit-Hora_inicio' value='" . htmlspecialchars($row['Hora_inicio']) . "'>";
            echo "<label for='edit-Hora_final'>Hora Final:</label>";
            echo "<input type='time' name='Hora_final' id='edit-Hora_final' value='" . htmlspecialchars($row['Hora_final']) . "'>";
            echo "<label for='edit-Turno'>Turno:</label>";
            echo "<select name='Turno' id='edit-Turno'>";
            echo "<option value='1'" . ($row['Turno'] == 1 ? ' selected' : '') . ">Mañana</option>";
            echo "<option value='2'" . ($row['Turno'] == 2 ? ' selected' : '') . ">Tarde</option>";
            echo "</select>";
            echo "<label for='edit-idGrado'>Grado:</label>";
            echo "<select name='idGrado' id='edit-idGrado'>";
            $grados = $conn->query("SELECT idGrado, CONCAT(NroGrado, ' - ', Seccion) AS Grado FROM grado");
            while ($grado = $grados->fetch_assoc()) {
                $selected = ($grado['idGrado'] == $row['idGrado']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($grado['idGrado']) . "' $selected>" . htmlspecialchars($grado['Grado']) . "</option>";
            }
            echo "</select>";
            echo "<label for='edit-idUser_maestro'>Maestro:</label>";
            echo "<select name='idUser_maestro' id='edit-idUser_maestro'>";
            $maestros = $conn->query("SELECT um.idUser_maestro, dp.Nombre_apellido FROM user_maestro um JOIN datos_personales dp ON um.idUsuario = dp.idUsuario");
            while ($maestro = $maestros->fetch_assoc()) {
                $selected = ($maestro['idUser_maestro'] == $row['idUser_maestro']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($maestro['idUser_maestro']) . "' $selected>" . htmlspecialchars($maestro['Nombre_apellido']) . "</option>";
            }
            echo "</select>";
        }
        $stmt->close();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Horarios</title>
    <link rel="stylesheet" href="styles_horario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> 
        /* Estilos para el modal */
        .modal {
            display: none; /* Ocultamos el modal por defecto */
            position: fixed; /* Posición fija en la pantalla */
            z-index: 1000; /* Aseguramos que esté encima de otros elementos */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Agregamos desplazamiento si es necesario */
            background-color: rgba(0,0,0,0.4); /* Fondo semitransparente */
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto; /* Centrar el modal verticalmente */
            padding: 20px;
            border-radius: 8px;
            width: 80%; /* Ancho del modal */
            max-width: 600px; /* Máximo ancho del modal */
            position: relative;
        }
        /* Estilos para el botón de actualización */
        .update-btn {
            background-color: #4CAF50; /* Verde */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
        .update-btn:hover {
            background-color: #45a049; /* Verde más oscuro */
        }
        /* Estilo para el botón de cerrar */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style> 
</head>
<body>
    <div class="schedule-container">
        <div class="schedule-header">
            <h1>Horarios</h1>
        </div>
        <button id="agregar-btn" onclick="mostrarFormulario()">Agregar nuevo horario <i class="fas fa-plus"></i></button>
        
        <!-- Formulario para agregar un nuevo horario -->
        <div id="formulario-horario" style="display:none;">
            <form action="register_horario.php" method="POST">
                <!-- Aquí irían los campos del formulario -->
                <label for="idCurso">Curso:</label>
                <select name="idCurso" id="idCurso">
                    <?php
                    $cursos = $conn->query("SELECT idCurso, Nombre FROM curso");
                    while ($curso = $cursos->fetch_assoc()) {
                        echo "<option value='".htmlspecialchars($curso['idCurso'])."'>".htmlspecialchars($curso['Nombre'])."</option>";
                    }
                    ?>
                </select>
                <label for="idDia">Día:</label>
                <select name="idDia" id="idDia">
                    <?php
                    $dias = $conn->query("SELECT idDia, Descripcion FROM dia");
                    while ($dia = $dias->fetch_assoc()) {
                        echo "<option value='".htmlspecialchars($dia['idDia'])."'>".htmlspecialchars($dia['Descripcion'])."</option>";
                    }
                    ?>
                </select>
                <label for="Hora_inicio">Hora Inicio:</label>
                <input type="time" name="Hora_inicio" id="Hora_inicio">
                <label for="Hora_final">Hora Final:</label>
                <input type="time" name="Hora_final" id="Hora_final">
                <label for="Turno">Turno:</label>
                <select name="Turno" id="Turno">
                    <option value="1">Mañana</option>
                    <option value="2">Tarde</option>
                </select>
                <label for="idGrado">Grado:</label>
                <select name="idGrado" id="idGrado">
                    <?php
                    $grados = $conn->query("SELECT idGrado, CONCAT(NroGrado, ' - ', Seccion) AS Grado FROM grado");
                    while ($grado = $grados->fetch_assoc()) {
                        echo "<option value='".htmlspecialchars($grado['idGrado'])."'>".htmlspecialchars($grado['Grado'])."</option>";
                    }
                    ?>
                </select>
                <label for="idUser_maestro">Maestro:</label>
                <select name="idUser_maestro" id="idUser_maestro">
                    <?php
                    $maestros = $conn->query("SELECT um.idUser_maestro, dp.Nombre_apellido FROM user_maestro um JOIN datos_personales dp ON um.idUsuario = dp.idUsuario");
                    while ($maestro = $maestros->fetch_assoc()) {
                        echo "<option value='".htmlspecialchars($maestro['idUser_maestro'])."'>".htmlspecialchars($maestro['Nombre_apellido'])."</option>";
                    }
                    ?>
                </select>
                <button type="submit"><i class="fas fa-save"></i> Guardar </button>
            </form>
        </div>

        <div class="schedule">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Curso</th>
                        <th>Día</th>
                        <th>Hora Inicio</th>
                        <th>Hora Final</th>
                        <th>Maestro</th>
                        <th>Grado</th>
                        <th>Sección</th>
                        <th>Turno</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $num = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($num++); ?></td>
                                <td><?php echo htmlspecialchars($row['Curso']); ?></td>
                                <td><?php echo htmlspecialchars($row['Dia']); ?></td>
                                <td><?php echo htmlspecialchars($row['Hora_inicio']); ?></td>
                                <td><?php echo htmlspecialchars($row['Hora_final']); ?></td>
                                <td><?php echo htmlspecialchars($row['Maestro']); ?></td>
                                <td><?php echo htmlspecialchars($row['Grado']); ?></td>
                                <td><?php echo htmlspecialchars($row['Seccion']); ?></td>
                                <td><?php echo $row['Turno'] == 1 ? 'Mañana' : 'Tarde'; ?></td>
                                <td>
                                    <button class="edit-btn" onclick="editarHorario(<?php echo htmlspecialchars($row['idHorario']); ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="delete_horario.php?id=<?php echo htmlspecialchars($row['idHorario']); ?>" class="delete-btn" onclick="return confirm('¿Estás seguro de que deseas eliminar este horario?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">No hay horarios registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para editar horario -->
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <form id="form-editar" action="update_horarios.php" method="POST">
                <!-- Los campos del formulario se llenarán con la respuesta del servidor -->
                <button type="submit" class="update-btn">Actualizar</button>
            </form>
        </div>
    </div>

    <script>
        // Función para alternar la visibilidad del formulario
        function mostrarFormulario() {
            const formulario = document.getElementById('formulario-horario');
            const display = formulario.style.display === 'block' ? 'none' : 'block';
            formulario.style.display = display;
        }

        function editarHorario(idHorario) {
            const modal = document.getElementById('modal-editar');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'dashboard.php?page=horarios_admin', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const form = document.querySelector('#modal-editar .modal-content form');
                    form.innerHTML = xhr.responseText;
                    
                    // Agregar el botón de actualización al final del formulario
                    const updateButton = document.createElement('button');
                    updateButton.type = 'submit';
                    updateButton.className = 'update-btn';
                    updateButton.innerHTML = '<i class="fas fa-save"></i>Actualizar';
                    form.appendChild(updateButton);
                    
                    modal.style.display = 'block';
                }
            };
            xhr.send('idHorario=' + idHorario);
        }

        function cerrarModal() {
            document.getElementById('modal-editar').style.display = 'none';
        }
    </script>
</body>
</html>
