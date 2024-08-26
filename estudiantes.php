<?php
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Consultar los datos de los tutores
$sql_datos_tutor = "SELECT datos_tutor.idDatos_Tutor, datos_tutor.Nombre_apellido AS tutor_nombre, datos_tutor.Celular AS tutor_celular, datos_tutor.idParentesco AS parentesco
                    FROM datos_tutor
                    JOIN datos_personales ON datos_tutor.idUser_estudiante = datos_personales.idUsuario
                    JOIN parentesco ON datos_tutor.idParentesco = parentesco.idParentesco";
$result_datos_tutor = $conn->query($sql_datos_tutor);

// Consultar la lista de estudiantes
$sql_estudiantes = "SELECT datos_personales.idDatos_Personales, datos_personales.Nombre_apellido, user_estudiante.idUser_estudiante
                   FROM datos_personales
                   JOIN user_estudiante ON datos_personales.idUsuario = user_estudiante.idUsuario";
$result_estudiantes = $conn->query($sql_estudiantes);

// Consultar los datos para los selectores
$sql_departamento = "SELECT idDepartamento, Descripcion FROM departamento";
$result_departamento = $conn->query($sql_departamento);

$sql_provincia = "SELECT idProvincia, Descripcion, idDepartamento FROM provincia";
$result_provincia = $conn->query($sql_provincia);

$sql_distrito = "SELECT idDistrito, Descripcion, idProvincia FROM distrito";
$result_distrito = $conn->query($sql_distrito);

// Consultar los datos para los selectores de parentesco
$sql_parentesco = "SELECT idParentesco, Descripcion FROM parentesco";
$result_parentesco = $conn->query($sql_parentesco);

// Consultar los grados y secciones
$sql_grado = "SELECT idGrado, NroGrado, Seccion FROM grado";
$result_grado = $conn->query($sql_grado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes</title>
    <link rel="stylesheet" href="styles_students.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="students-container">
        <div class="students-header">
            <h1>Listado de Estudiantes</h1>
        </div>
        
        <button id="register-btn" class="register-btn">Registrar nuevo estudiante</button>

        <!-- Formulario para registrar un nuevo estudiante -->
        <div id="register-form" class="register-form">
            <form action="register_estudiante.php" method="post" enctype="multipart/form-data">
                <!-- Inicio de Sesión -->
                <h2 class="section-title">Inicio de Sesión</h2>
                <!-- DNI y Contraseña en una fila -->
                <div class="form-row dni-password-row">
                    <div class="form-group">
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>

                <!-- Datos Generales -->
                <h2 class="section-title">Datos Generales</h2>
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="idGrado">Grado y Sección:</label>
                <select id="idGrado" name="idGrado" required>
                    <option value="">Seleccionar Grado y Sección</option>
                    <?php
                    if ($result_grado->num_rows > 0) {
                        while ($row = $result_grado->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idGrado"]) . '">' . htmlspecialchars($row["NroGrado"]) . ' - ' . htmlspecialchars($row["Seccion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <label for="sexo">Sexo:</label>
                <select id="sexo" name="sexo" required>
                    <option value="1">Masculino</option>
                    <option value="2">Femenino</option>
                </select>
                
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" required>
                
                <!-- Botón para seleccionar archivo a la derecha -->
                <div class="file-upload">
                    <label for="foto">Foto:</label>
                    <input type="file" id="foto" name="foto" accept="image/*" required>
                </div>

                <!-- Domicilio Actual -->
                <h2 class="section-title">Domicilio Actual</h2>
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" required>
                <label for="departamento">Departamento:</label>
                <select id="departamento" name="departamento" required>
                    <option value="">Seleccionar Departamento</option>
                    <?php
                    if ($result_departamento->num_rows > 0) {
                        while ($row = $result_departamento->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idDepartamento"]) . '">' . htmlspecialchars($row["Descripcion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="provincia">Provincia:</label>
                <select id="provincia" name="provincia" required>
                    <option value="">Seleccionar Provincia</option>
                    <?php
                    if ($result_provincia->num_rows > 0) {
                        while ($row = $result_provincia->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idProvincia"]) . '">' . htmlspecialchars($row["Descripcion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="distrito">Distrito:</label>
                <select id="distrito" name="distrito" required>
                    <option value="">Seleccionar Distrito</option>
                    <?php
                    if ($result_distrito->num_rows > 0) {
                        while ($row = $result_distrito->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idDistrito"]) . '">' . htmlspecialchars($row["Descripcion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <!-- Tutor -->
                <h2 class="section-title">Tutor</h2>
                <label for="nombre_tutor">Nombre del Tutor:</label>
                <input type="text" id="nombre_tutor" name="nombre_tutor" required>

                <label for="celular_tutor">Celular del Tutor:</label>
                <input type="text" id="celular_tutor" name="celular_tutor" required>

                <label for="parentesco">Parentesco:</label>
                <select id="parentesco" name="parentesco" required>
                    <option value="">Seleccionar Parentesco</option>
                    <?php
                    if ($result_parentesco->num_rows > 0) {
                        while ($row = $result_parentesco->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idParentesco"]) . '">' . htmlspecialchars($row["Descripcion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <!-- Botones al final en una fila -->
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Guardar</button>
                    <button type="button" class="close-btn" onclick="toggleForm()">Cerrar</button>
                </div>
            </form>
        </div>

        <table class="students-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_estudiantes->num_rows > 0) {
                    while ($row = $result_estudiantes->fetch_assoc()) {
                        $idUserEstudiante = isset($row["idUser_estudiante"]) ? $row["idUser_estudiante"] : 'No disponible';
                        echo '<tr>'; // Añadido para completar la estructura de fila
                        echo '<td>' . htmlspecialchars($row["Nombre_apellido"]) . '</td>';
                        echo '<td>';
                        echo '<button class="edit-btn" onclick="location.href=\'edit_student.php?id=' . htmlspecialchars($idUserEstudiante) . '\'"><i class="fas fa-edit"></i></button> ';
                        echo '<button class="delete-btn" onclick="confirmDelete(' . htmlspecialchars($idUserEstudiante) . ')"><i class="fas fa-trash"></i></button>';
                        echo '</td>';
                        echo '</tr>'; // Cerrado la fila
                    }
                } else {
                    echo '<tr><td colspan="2">No se encontraron estudiantes.</td></tr>'; // Corregido el colspan
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById('register-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Inicialmente ocultar el formulario
        document.getElementById('register-form').style.display = 'none';

        // Asignar evento al botón
        document.getElementById('register-btn').addEventListener('click', toggleForm);

        function confirmDelete(studentId) {
            if (confirm('¿Está seguro de que desea eliminar este estudiante?')) {
                window.location.href = 'delete_student.php?id=' + studentId;
            }
        }
    </script>
</body>
</html>
