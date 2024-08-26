<?php
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Consultar la lista de profesores
$sql_profesores = "SELECT datos_personales.idDatos_Personales, datos_personales.Nombre_apellido, user_maestro.idUser_maestro
                   FROM datos_personales
                   JOIN user_maestro ON datos_personales.idUsuario = user_maestro.idUsuario";
$result_profesores = $conn->query($sql_profesores);


// Consultar los datos para los selectores
$sql_departamento = "SELECT idDepartamento, Descripcion FROM departamento";
$result_departamento = $conn->query($sql_departamento);

$sql_provincia = "SELECT idProvincia, Descripcion, idDepartamento FROM provincia";
$result_provincia = $conn->query($sql_provincia);

$sql_distrito = "SELECT idDistrito, Descripcion, idProvincia FROM distrito";
$result_distrito = $conn->query($sql_distrito);

$sql_dia = "SELECT idDia, Descripcion FROM dia";
$result_dia = $conn->query($sql_dia);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesores</title>
    <link rel="stylesheet" href="styles_teachers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="teachers-container">
        <div class="teachers-header">
            <h1>Listado de Profesores</h1>
        </div>
        
        <button id="register-btn" class="register-btn">Registrar nuevo profesor</button>

        <!-- Formulario para registrar un nuevo profesor -->
        <div id="register-form" class="register-form">
            <form action="register_teacher.php" method="post" enctype="multipart/form-data">
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
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
                
                <label for="sexo">Sexo:</label>
                <select id="sexo" name="sexo" required>
                    <option value="1">Masculino</option>
                    <option value="2">Femenino</option>
                </select>
                
                <label for="celular">Celular:</label>
                <input type="text" id="celular" name="celular" required>
                
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
            
                <!-- Formación Académica -->
                <h2 class="section-title">Formación Académica</h2>
                <label for="edu_secundaria">Educación Secundaria:</label>
                <input type="text" id="edu_secundaria" name="edu_secundaria" required>
                
                <label for="direccion_secundaria">Dirección:</label>
                <input type="text" id="direccion_secundaria" name="direccion_secundaria">
                
                <label for="edu_superior">Educación Superior:</label>
                <input type="text" id="edu_superior" name="edu_superior" required>
                
                <label for="direccion_superior">Dirección:</label>
                <input type="text" id="direccion_superior" name="direccion_superior">
                
                <!-- Perfil Educativo -->
                <h2 class="section-title">Perfil Educativo</h2>
                <label for="especializacion">Especialización:</label>
                <input type="text" id="especializacion" name="especializacion" required>

                <label for="titulo_profesional">Titulo Profesional:</label>
                <input type="text" id="titulo_profesional" name="titulo_profesional" required>
                
                <label for="hora_atencion">Horario de Atencion</label>
                <label for="dia_atencion">Día de la Semana:</label>
                <select id="dia_atencion" name="dia_atencion" required>
                    <?php
                    if ($result_dia->num_rows > 0) {
                        while ($row = $result_dia->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row["idDia"]) . '">' . htmlspecialchars($row["Descripcion"]) . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <label for="hora_inicio">Hora de Inicio:</label>
                <input type="time" id="hora_inicio" name="hora_inicio" required>
                
                <label for="hora_fin">Hora de Fin:</label>
                <input type="time" id="hora_fin" name="hora_fin" required>
                
                <!-- Botones al final en una fila -->
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Guardar</button>
                    <button type="button" class="close-btn" onclick="toggleForm()">Cerrar</button>
                </div>
            </form>
        </div>

        <table class="teachers-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_profesores->num_rows > 0) {
                    while ($row = $result_profesores->fetch_assoc()) {
                        $idUserMaestro = isset($row["idUser_maestro"]) ? $row["idUser_maestro"] : 'No disponible';
                        $codigo = 'DO' . str_pad($idUserMaestro, 2, '0', STR_PAD_LEFT); // Formatear el código
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($codigo) . '</td>';
                        echo '<td>' . htmlspecialchars($row["Nombre_apellido"]) . '</td>';
                        echo '<td>';
                        echo '<button class="edit-btn" onclick="location.href=\'edit_teacher.php?id=' . htmlspecialchars($idUserMaestro) . '\'"><i class="fas fa-edit"></i></button> ';
                        echo '<button class="delete-btn" onclick="confirmDelete(' . htmlspecialchars($idUserMaestro) . ')"><i class="fas fa-trash"></i></button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3">No se encontraron profesores.</td></tr>';
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

        function confirmDelete(teacherId) {
            if (confirm('¿Está seguro de que desea eliminar este profesor?')) {
                window.location.href = 'delete_teacher.php?id=' + teacherId;
            }
        }
    </script>
</body>
</html>
