<?php
session_start();
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos del formulario
$dni = $_POST['dni'];
$password = md5($_POST['password']); // Encriptar la contraseña con MD5
$nombre = $_POST['nombre'];
$sexo = $_POST['sexo'];
$celular = $_POST['celular'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$correo = $_POST['correo'];
$direccion = $_POST['direccion'];
$departamento = $_POST['departamento'];
$provincia = $_POST['provincia'];
$distrito = $_POST['distrito'];
$edu_secundaria = $_POST['edu_secundaria'];
$direccion_secundaria = $_POST['direccion_secundaria'];
$edu_superior = $_POST['edu_superior'];
$direccion_superior = $_POST['direccion_superior'];
$especializacion = $_POST['especializacion'];
$titulo_profesional = $_POST['titulo_profesional'];
$dia_atencion = $_POST['dia_atencion'];
$hora_inicio = $_POST['hora_inicio'];
$hora_fin = $_POST['hora_fin'];

// Configura el directorio de uploads y verifica su existencia
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        die("Error al crear el directorio de uploads.");
    }
}

// Subir la foto
$foto = $_FILES['foto'];
$foto_path = $upload_dir . basename($foto['name']);
if (!move_uploaded_file($foto['tmp_name'], $foto_path)) {
    echo "Error al subir la foto.";
    exit();
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

// Insertar datos en la tabla user_maestro
$sql_user_maestro = "INSERT INTO user_maestro (idUsuario) VALUES (?)";
$stmt_user_maestro = $conn->prepare($sql_user_maestro);
$stmt_user_maestro->bind_param("i", $idUsuario);
if (!$stmt_user_maestro->execute()) {
    echo "Error al insertar en la tabla user_maestro: " . $stmt_user_maestro->error;
    exit();
}
$idUser_maestro = $stmt_user_maestro->insert_id; // Obtener el ID generado para usar en la siguiente inserción

// Insertar datos en la tabla formacion_academica
$sql_formacion_academica = "INSERT INTO formacion_academica (Edu_secundaria, Direccion_secundaria, Edu_superior, Direccion_superior, idUser_maestro) VALUES (?, ?, ?, ?, ?)";
$stmt_formacion_academica = $conn->prepare($sql_formacion_academica);
$stmt_formacion_academica->bind_param("ssssi", $edu_secundaria, $direccion_secundaria, $edu_superior, $direccion_superior, $idUser_maestro);
if (!$stmt_formacion_academica->execute()) {
    echo "Error al insertar en la tabla formacion_academica: " . $stmt_formacion_academica->error;
    exit();
}

// Insertar datos en la tabla perfil_educativo
$sql_perfil_educativo = "INSERT INTO perfil_educativo (Especializacion, Titulo_profesional, idDia, hora_inicio_atencion, hora_fin_atencion, idUser_maestro) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_perfil_educativo = $conn->prepare($sql_perfil_educativo);
$stmt_perfil_educativo->bind_param("ssissi", $especializacion, $titulo_profesional, $dia_atencion, $hora_inicio, $hora_fin, $idUser_maestro);
if (!$stmt_perfil_educativo->execute()) {
    echo "Error al insertar en la tabla perfil_educativo: " . $stmt_perfil_educativo->error;
    exit();
}

// Cerrar conexión
$stmt_usuario->close();
$stmt_datos_personales->close();
$stmt_datos_domicilio->close();
$stmt_formacion_academica->close();
$stmt_perfil_educativo->close();
$stmt_user_maestro->close();
$conn->close();

// Redirigir a la página de éxito o a la lista de profesores
header("Location: dashboard.php?page=profesores");
exit();
?>
