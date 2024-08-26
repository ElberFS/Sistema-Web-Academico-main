<?php
session_start();
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Verificar que los datos necesarios estén presentes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los valores del formulario
    $dni = $_POST['dni'];
    $password = md5($_POST['password']);
    $nombre = $_POST['nombre'];
    $sexo = $_POST['sexo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $departamento = $_POST['departamento'];
    $provincia = $_POST['provincia'];
    $distrito = $_POST['distrito'];
    $nombre_tutor = $_POST['nombre_tutor'];
    $celular_tutor = $_POST['celular_tutor'];
    $id_parentesco = $_POST['parentesco'];
    $idGrado = $_POST['idGrado'];

    // Manejo de la foto del estudiante
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto_tmp_name = $_FILES['foto']['tmp_name'];
        $foto_name = $_FILES['foto']['name'];
        $foto_path = 'uploads/' . basename($foto_name); // Asume que la carpeta 'uploads' existe y es accesible

        if (!move_uploaded_file($foto_tmp_name, $foto_path)) {
            echo "Error al subir la foto.";
            exit();
        }
    }

    // Insertar datos en la tabla usuario
    $sql_usuario = "INSERT INTO usuario (User_dni, User_password, idPerfil) VALUES (?, ?, 3)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("ss", $dni, $password);
    if (!$stmt_usuario->execute()) {
        echo "Error al insertar en la tabla usuario: " . $stmt_usuario->error;
        exit();
    }
    $idUsuario = $stmt_usuario->insert_id;

    // Insertar datos en la tabla datos_personales
    $sql_datos_personales = "INSERT INTO datos_personales (Nombre_apellido, Fecha_Nacimiento, Sexo, Correo, Celular, Foto, idUsuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_datos_personales = $conn->prepare($sql_datos_personales);
    $stmt_datos_personales->bind_param("ssisssi", $nombre, $fecha_nacimiento, $sexo, $correo, $celular, $foto_path, $idUsuario);
    if (!$stmt_datos_personales->execute()) {
        echo "Error al insertar en la tabla datos_personales: " . $stmt_datos_personales->error;
        exit();
    }

    // Insertar datos en la tabla datos_domicilio
    $sql_datos_domicilio = "INSERT INTO datos_domicilio (Direccion, idDepartamento, idProvincia, idDistrito, idUsuario) VALUES (?, ?, ?, ?, ?)";
    $stmt_datos_domicilio = $conn->prepare($sql_datos_domicilio);
    $stmt_datos_domicilio->bind_param("siiii", $direccion, $departamento, $provincia, $distrito, $idUsuario);
    if (!$stmt_datos_domicilio->execute()) {
        echo "Error al insertar en la tabla datos_domicilio: " . $stmt_datos_domicilio->error;
        exit();
    }

    // Insertar datos en la tabla user_estudiante
    $sql_user_estudiante = "INSERT INTO user_estudiante (idGrado, idUsuario) VALUES (?,?)";
    $stmt_user_estudiante = $conn->prepare($sql_user_estudiante);
    $stmt_user_estudiante ->bind_param("ii",  $idGrado, $idUsuario);
    if (!$stmt_user_estudiante ->execute()) {
        echo "Error al insertar en la tabla user_estudiante: " . $stmt_user_estudiante->error;
        exit();
    }
    $idUser_estudiante = $stmt_user_estudiante->insert_id;

    // Insertar datos del tutor
    $sql_datos_tutor = "INSERT INTO datos_tutor (Nombre_apellido, Celular, idParentesco, idUser_estudiante) VALUES (?, ?, ?, ?)";
    $stmt_datos_tutor = $conn->prepare($sql_datos_tutor);
    $stmt_datos_tutor->bind_param("ssii", $nombre_tutor, $celular_tutor, $id_parentesco, $idUser_estudiante);
    if (!$stmt_datos_tutor->execute()) {
        echo "Error al insertar en la tabla datos_tutor: " . $stmt_datos_tutor->error;
        exit();
    }

    // Redirigir o mostrar mensaje de éxito
    header("Location: dashboard.php?page=estudiantes");
    exit();
}
?>
