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

// Obtener descripciones de parentesco
$sql_parentesco = "SELECT idParentesco, Descripcion FROM parentesco";
$result_parentesco = $conn->query($sql_parentesco);

// Crear un array para mapear idParentesco a Descripcion
$parentesco_map = [];
while ($row_parentesco = $result_parentesco->fetch_assoc()) {
    $parentesco_map[$row_parentesco['idParentesco']] = $row_parentesco['Descripcion'];
}

// Obtener idUser_estudiante del usuario actual
$sql_user_estudiante = "
    SELECT idUser_estudiante, idGrado
    FROM user_estudiante
    WHERE idUsuario = ?
";
$stmt_user_estudiante = $conn->prepare($sql_user_estudiante);
$stmt_user_estudiante->bind_param("i", $idUsuario);
$stmt_user_estudiante->execute();
$result_user_estudiante = $stmt_user_estudiante->get_result();

if ($result_user_estudiante->num_rows > 0) {
    $row_user_estudiante = $result_user_estudiante->fetch_assoc();
    $idUserEstudiante = $row_user_estudiante['idUser_estudiante'];
    $idGrado = $row_user_estudiante['idGrado'];

    // Obtener datos de tutores
    $sql_tutores = "
        SELECT t.Nombre_apellido, t.Celular, t.idParentesco
        FROM datos_tutor t
        WHERE t.idUser_estudiante = ?
    ";
    $stmt_tutores = $conn->prepare($sql_tutores);
    $stmt_tutores->bind_param("i", $idUserEstudiante);
    $stmt_tutores->execute();
    $result_tutores = $stmt_tutores->get_result();
    
    // Obtener información del grado
    $sql_grado = "
        SELECT g.NroGrado, g.Seccion
        FROM grado g
        WHERE g.idGrado = ?
    ";
    $stmt_grado = $conn->prepare($sql_grado);
    $stmt_grado->bind_param("i", $idGrado);
    $stmt_grado->execute();
    $result_grado = $stmt_grado->get_result();

    if ($result_grado->num_rows > 0) {
        $row_grado = $result_grado->fetch_assoc();
        $grado = $row_grado['NroGrado'];
        $seccion = $row_grado['Seccion'];
    } else {
        $error_grado = "No se encontró el grado para el usuario actual.";
    }
} else {
    $error_user_estudiante = "No se encontró el idUser_estudiante para el usuario actual.";
}

// Convertir los resultados a arrays para pasarlos a JavaScript antes de cerrar el resultado
$departamentos = $result_departamentos->fetch_all(MYSQLI_ASSOC);
$provincias = $result_provincias->fetch_all(MYSQLI_ASSOC);
$distritos = $result_distritos->fetch_all(MYSQLI_ASSOC);

// Cerrar conexiones
$stmt_personales->close();
$stmt_domicilio->close();
$result_departamentos->close();
$result_provincias->close();
$result_distritos->close();
$result_user_estudiante->close();
$stmt_tutores->close();
$stmt_grado->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Estudiante</title>
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
                <?php echo htmlspecialchars($grado . ' - ' . $seccion); ?>
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
                    <input type="text" id="address" name="direccion" value="<?php echo htmlspecialchars($row_domicilio['Direccion']); ?>" readonly>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Departamento:</label>
                        <input type="text" id="department" value="<?php echo htmlspecialchars($row_domicilio['Departamento']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="province">Provincia:</label>
                        <input type="text" id="province" value="<?php echo htmlspecialchars($row_domicilio['Provincia']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="district">Distrito:</label>
                        <input type="text" id="district" value="<?php echo htmlspecialchars($row_domicilio['Distrito']); ?>" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="tutor-container">
        <div class="tutor-header">
            DATOS DE TUTORES
        </div>
        <div class="tutor-details">
            <form id="tutor-form">
                <?php if (isset($result_tutores) && $result_tutores->num_rows > 0): ?>
                    <?php while ($row_tutores = $result_tutores->fetch_assoc()): ?>
                        <div class="form-group">
                            <label for="tutor-name-<?php echo $row_tutores['idParentesco']; ?>">Nombres y Apellidos:</label>
                            <input type="text" id="tutor-name-<?php echo $row_tutores['idParentesco']; ?>" value="<?php echo htmlspecialchars($row_tutores['Nombre_apellido']); ?>" readonly>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tutor-phone-<?php echo $row_tutores['idParentesco']; ?>">Celular:</label>
                                <input type="text" id="tutor-phone-<?php echo $row_tutores['idParentesco']; ?>" value="<?php echo htmlspecialchars($row_tutores['Celular']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="tutor-parentesco-<?php echo $row_tutores['idParentesco']; ?>">Parentesco:</label>
                                <input type="text" id="tutor-parentesco-<?php echo $row_tutores['idParentesco']; ?>" value="<?php echo htmlspecialchars($parentesco_map[$row_tutores['idParentesco']]); ?>" readonly>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="form-group">
                        <p>No se encontraron datos de tutores.</p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="form-buttons">
        <button type="button" class="print-btn" onclick="printForm()">Imprimir Formulario</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar el clic en el botón "Imprimir Formulario"
            window.printForm = function() {
                window.print();
            };
        });
    </script>
</body>
</html>
