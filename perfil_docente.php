<?php
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Obtener el idUsuario de la sesión
$idUsuario = $_SESSION['user_id'];

// Obtener los datos de los departamentos
$sql_departamentos = "SELECT idDepartamento, Descripcion FROM departamento";
$result_departamentos = $conn->query($sql_departamentos);

// Obtener los datos de las provincias
$sql_provincias = "SELECT idProvincia, Descripcion, idDepartamento FROM provincia";
$result_provincias = $conn->query($sql_provincias);

// Obtener los datos de los distritos
$sql_distritos = "SELECT idDistrito, Descripcion, idProvincia FROM distrito";
$result_distritos = $conn->query($sql_distritos);

// Obtener los datos de los días
$sql_dias = "SELECT idDia, Descripcion FROM dia";
$result_dias = $conn->query($sql_dias);

// Convertir los resultados a un array para usar en JavaScript
$dias = $result_dias->fetch_all(MYSQLI_ASSOC);

// Obtener datos personales y perfil
$sql_personales = "
    SELECT dp.Nombre_apellido, dp.Fecha_Nacimiento, dp.Sexo, dp.Correo, dp.Celular, dp.Foto, p.Descripcion as Perfil
    FROM datos_personales dp
    JOIN perfil p ON p.idPerfil = (SELECT idPerfil FROM usuario WHERE idUsuario = ?)
    WHERE dp.idUsuario = ?
";
$stmt_personales = $conn->prepare($sql_personales);
$stmt_personales->bind_param("ii", $idUsuario, $idUsuario);
$stmt_personales->execute();
$result_personales = $stmt_personales->get_result();

if ($result_personales->num_rows > 0) {
    $row_personales = $result_personales->fetch_assoc();
} else {
    $error_personales = "No se encontraron datos personales.";
}

// Obtener datos de domicilio
$sql_domicilio = "
    SELECT d.Direccion, d.idDepartamento, d.idProvincia, d.idDistrito, de.Descripcion as Departamento, pr.Descripcion as Provincia, di.Descripcion as Distrito
    FROM datos_domicilio d
    JOIN departamento de ON d.idDepartamento = de.idDepartamento
    JOIN provincia pr ON d.idProvincia = pr.idProvincia
    JOIN distrito di ON d.idDistrito = di.idDistrito
    WHERE d.idUsuario = ?
";
$stmt_domicilio = $conn->prepare($sql_domicilio);
$stmt_domicilio->bind_param("i", $idUsuario);
$stmt_domicilio->execute();
$result_domicilio = $stmt_domicilio->get_result();

$row_domicilio = $result_domicilio->fetch_assoc();

// Obtener datos de formación académica
$sql_formacion = "
    SELECT fa.Edu_secundaria, fa.Direccion_secundaria, fa.Edu_superior, fa.Direccion_superior
    FROM formacion_academica fa
    JOIN user_maestro um ON fa.idUser_maestro = um.idUser_maestro
    WHERE um.idUsuario = ?
";
$stmt_formacion = $conn->prepare($sql_formacion);
$stmt_formacion->bind_param("i", $idUsuario);
$stmt_formacion->execute();
$result_formacion = $stmt_formacion->get_result();

$row_formacion = $result_formacion->fetch_assoc();

// Obtener datos de perfil educativo
$sql_educativo = "
    SELECT pe.Especializacion, pe.Titulo_profesional, pe.idDia, pe.hora_inicio_atencion, pe.hora_fin_atencion, d.Descripcion as Dia
    FROM perfil_educativo pe
    JOIN user_maestro um ON pe.idUser_maestro = um.idUser_maestro
    JOIN dia d ON pe.idDia = d.idDia
    WHERE um.idUsuario = ?
";
$stmt_educativo = $conn->prepare($sql_educativo);
$stmt_educativo->bind_param("i", $idUsuario);
$stmt_educativo->execute();
$result_educativo = $stmt_educativo->get_result();

if ($result_educativo->num_rows > 0) {
    $row_educativo = $result_educativo->fetch_assoc();
} else {
    $error_educativo = "No se encontraron datos educativos.";
}

$stmt_educativo->close();
$stmt_personales->close();
$stmt_domicilio->close();
$stmt_formacion->close();
$conn->close();

// Convertir los resultados a arrays para pasarlos a JavaScript
$departamentos = $result_departamentos->fetch_all(MYSQLI_ASSOC);
$provincias = $result_provincias->fetch_all(MYSQLI_ASSOC);
$distritos = $result_distritos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Docente</title>
    <link rel="stylesheet" href="styles_perfil.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-photo">
            <?php
            if (isset($row_personales['Foto'])) {
                $foto_base64 = base64_encode($row_personales['Foto']);
                $mime_type = 'image/jpeg';
                echo '<img src="data:' . $mime_type . ';base64,' . $foto_base64 . '" alt="Foto de perfil">';
            } else {
                echo 'No hay foto disponible';
            }
            ?>
            <div class="profile-type">
                <?php echo htmlspecialchars($row_personales['Perfil']); ?>
            </div>
            <div class="profile-type">
                <?php echo htmlspecialchars($row_educativo['Titulo_profesional'] ?? 'No disponible'); ?>
            </div>
        </div>
        <div class="details-container">
            <div class="details-header">
                DATOS PERSONALES
            </div>
            <div class="profile-details">
                <form id="profile-form">
                    <div class="form-group">
                        <label for="name">Nombre y Apellido:</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($row_personales['Nombre_apellido']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="dob">Fecha de Nacimiento:</label>
                        <input type="text" id="dob" value="<?php echo date('d-m-Y', strtotime($row_personales['Fecha_Nacimiento'])); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="gender">Sexo:</label>
                        <input type="text" id="gender" value="<?php echo $row_personales['Sexo'] == 1 ? 'Masculino' : 'Femenino'; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo:</label>
                        <input type="text" id="email" value="<?php echo htmlspecialchars($row_personales['Correo']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phone">Celular:</label>
                        <input type="text" id="phone" name="celular" value="<?php echo htmlspecialchars($row_personales['Celular']); ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="address-container">
        <div class="address-header">
            DATOS DE DOMICILIO
        </div>
        <div class="address-details">
            <form>
                <div class="form-group">
                    <label for="address">Dirección:</label>
                    <input type="text" id="address" name="direccion" value="<?php echo htmlspecialchars($row_domicilio['Direccion']); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Departamento:</label>
                        <select id="department" name="departamento">
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo $departamento['idDepartamento']; ?>" <?php echo $departamento['idDepartamento'] == $row_domicilio['idDepartamento'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($departamento['Descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="province">Provincia:</label>
                        <select id="province" name="provincia">
                            <?php foreach ($provincias as $provincia): ?>
                                <option value="<?php echo $provincia['idProvincia']; ?>" <?php echo $provincia['idProvincia'] == $row_domicilio['idProvincia'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($provincia['Descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="district">Distrito:</label>
                        <select id="district" name="distrito">
                            <?php foreach ($distritos as $distrito): ?>
                                <option value="<?php echo $distrito['idDistrito']; ?>" <?php echo $distrito['idDistrito'] == $row_domicilio['idDistrito'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($distrito['Descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="education-container">
        <div class="education-header">
            FORMACIÓN ACADÉMICA
        </div>
        <div class="education-details">
            <form>
                <div class="form-group">
                    <label for="secondary-education">Educación Secundaria:</label>
                    <input type="text" id="secondary-education" value="<?php echo htmlspecialchars($row_formacion['Edu_secundaria']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="secondary-address">Dirección:</label>
                    <input type="text" id="secondary-address" value="<?php echo htmlspecialchars($row_formacion['Direccion_secundaria']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="higher-education">Educación Superior:</label>
                    <input type="text" id="higher-education" value="<?php echo htmlspecialchars($row_formacion['Edu_superior']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="higher-address">Dirección:</label>
                    <input type="text" id="higher-address" value="<?php echo htmlspecialchars($row_formacion['Direccion_superior']); ?>" readonly>
                </div>
            </form>
        </div>
    </div>

    <div class="educational-profile-container">
        <div class="educational-profile-header">
            PERFIL EDUCATIVO
        </div>
        <div class="educational-profile-details">
            <form id="educational-profile-form">
                <div class="form-group">
                    <label for="specialization">Especialización:</label>
                    <input type="text" id="specialization" value="<?php echo htmlspecialchars($row_educativo['Especializacion']); ?>" readonly>
                </div>
                <label for="atention-time">Horario de Atencion</label>
                <div class="form-row">
                    <div class="form-group">
                        <label for="day">Día:</label>
                        <select id="day" name="day">
                            <?php foreach ($dias as $dia): ?>
                                <option value="<?php echo $dia['idDia']; ?>" <?php echo $dia['idDia'] == $row_educativo['idDia'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dia['Descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start-time">Hora Inicio Atención:</label>
                        <input type="time" id="start-time" value="<?php echo htmlspecialchars($row_educativo['hora_inicio_atencion']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="end-time">Hora Fin Atención:</label>
                        <input type="time" id="end-time" value="<?php echo htmlspecialchars($row_educativo['hora_fin_atencion']); ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="form-buttons">
        <button type="button" class="save-btn" onclick="saveForm()">Guardar</button>
        <button type="button" class="print-btn" onclick="printForm()">Imprimir Formulario</button>
    </div>

    <script>
            document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department');
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const daySelect = document.getElementById('day'); // Selección del día
            const saveButton = document.querySelector('.save-btn');

            const provincias = <?php echo json_encode($provincias); ?>;
            const distritos = <?php echo json_encode($distritos); ?>;
            const dias = <?php echo json_encode($dias); ?>; // Agregar los días

            // Cargar provincias basadas en el departamento seleccionado
            departmentSelect.addEventListener('change', function() {
                const departamentoId = this.value;
                provinceSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                districtSelect.innerHTML = '<option value="">Seleccione un distrito</option>';

                provincias.forEach(provincia => {
                    if (provincia.idDepartamento == departamentoId) {
                        const option = document.createElement('option');
                        option.value = provincia.idProvincia;
                        option.textContent = provincia.Descripcion;
                        provinceSelect.appendChild(option);
                    }
                });

                // Trigger a change event on province select to update districts
                provinceSelect.dispatchEvent(new Event('change'));
            });

            // Cargar distritos basados en la provincia seleccionada
            provinceSelect.addEventListener('change', function() {
                const provinciaId = this.value;
                districtSelect.innerHTML = '<option value="">Seleccione un distrito</option>';

                distritos.forEach(distrito => {
                    if (distrito.idProvincia == provinciaId) {
                        const option = document.createElement('option');
                        option.value = distrito.idDistrito;
                        option.textContent = distrito.Descripcion;
                        districtSelect.appendChild(option);
                    }
                });
            });

            // Inicializar la selección de departamentos, provincias y distritos
            departmentSelect.dispatchEvent(new Event('change'));
            provinceSelect.value = '<?php echo $row_domicilio["idProvincia"]; ?>';
            provinceSelect.dispatchEvent(new Event('change'));
            districtSelect.value = '<?php echo $row_domicilio["idDistrito"]; ?>';

            // Inicializar el select de días
            daySelect.innerHTML = ''; // Limpiar opciones existentes
            dias.forEach(dia => {
                const option = document.createElement('option');
                option.value = dia.idDia;
                option.textContent = dia.Descripcion;
                if (dia.idDia == '<?php echo $row_educativo["idDia"]; ?>') {
                    option.selected = true;
                }
                daySelect.appendChild(option);
            });

            // Manejar el clic en el botón "Guardar"
            window.saveForm = function() {
                const celular = document.getElementById('phone').value;
                const direccion = document.getElementById('address').value;
                const departamento = departmentSelect.value;
                const provincia = provinceSelect.value;
                const distrito = districtSelect.value;
                
                const specialization = document.getElementById('specialization').value;
                const day = daySelect.value;
                const startTime = document.getElementById('start-time').value;
                const endTime = document.getElementById('end-time').value;

                // Validar campos requeridos
                if (!celular || !direccion || !departamento || !provincia || !distrito || !specialization || !day || !startTime || !endTime) {
                    alert('Por favor, complete todos los campos.');
                    return;
                }

                // Mostrar datos enviados en la consola para depuración
                console.log('Enviando datos:', {
                    celular: celular,
                    direccion: direccion,
                    departamento: departamento,
                    provincia: provincia,
                    distrito: distrito,
                    specialization: specialization,
                    day: day,
                    startTime: startTime,
                    endTime: endTime
                });

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_profile.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        console.log('Respuesta del servidor:', xhr.responseText); // Para depuración
                        if (xhr.status === 200) {
                            location.reload();
                        } else {
                            alert('Error al actualizar los datos: ' + xhr.statusText);
                        }
                    }
                };
                xhr.send(`celular=${encodeURIComponent(celular)}&direccion=${encodeURIComponent(direccion)}&departamento=${encodeURIComponent(departamento)}&provincia=${encodeURIComponent(provincia)}&distrito=${encodeURIComponent(distrito)}&specialization=${encodeURIComponent(specialization)}&day=${encodeURIComponent(day)}&startTime=${encodeURIComponent(startTime)}&endTime=${encodeURIComponent(endTime)}`);
            };

            // Manejar el clic en el botón "Imprimir Formulario"
            window.printForm = function() {
                window.print();
            };
        });
    </script>
</body>
</html>
