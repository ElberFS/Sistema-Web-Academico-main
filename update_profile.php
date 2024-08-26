<?php
include 'db.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

// Obtener el idUsuario de la sesión
$idUsuario = $_SESSION['user_id'];

// Obtener datos del POST
$celular = $_POST['celular'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$distrito = $_POST['distrito'] ?? '';
$especializacion = $_POST['specialization'] ?? '';
$dia = $_POST['day'] ?? '';
$hora_inicio_atencion = $_POST['startTime'] ?? '';
$hora_fin_atencion = $_POST['endTime'] ?? '';

// Validar los datos (puedes agregar más validaciones según sea necesario)
if (empty($celular) || empty($direccion) || empty($departamento) || empty($provincia) || empty($distrito) || empty($especializacion) || empty($dia) || empty($hora_inicio_atencion) || empty($hora_fin_atencion)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, complete todos los campos.']);
    exit();
}

// Preparar la consulta de actualización para datos de domicilio
$sql_update_domicilio = "
    UPDATE datos_domicilio
    SET Direccion = ?, idDepartamento = ?, idProvincia = ?, idDistrito = ?
    WHERE idUsuario = ?
";
$stmt_update_domicilio = $conn->prepare($sql_update_domicilio);
if ($stmt_update_domicilio === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta de domicilio']);
    exit();
}
$stmt_update_domicilio->bind_param("siiii", $direccion, $departamento, $provincia, $distrito, $idUsuario);

$domicilio_actualizado = $stmt_update_domicilio->execute();
if ($domicilio_actualizado === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar los datos de domicilio']);
    exit();
}

// Preparar la consulta de actualización para datos personales
$sql_update_personales = "
    UPDATE datos_personales
    SET Celular = ?
    WHERE idUsuario = ?
";
$stmt_update_personales = $conn->prepare($sql_update_personales);
if ($stmt_update_personales === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta de datos personales']);
    exit();
}
$stmt_update_personales->bind_param("si", $celular, $idUsuario);

$personales_actualizado = $stmt_update_personales->execute();
if ($personales_actualizado === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar los datos personales']);
    exit();
}

// Preparar la consulta de actualización para perfil educativo
$sql_update_educativo = "
    UPDATE perfil_educativo
    SET Especializacion = ?, idDia = ?, hora_inicio_atencion = ?, hora_fin_atencion = ?
    WHERE idUser_maestro = (SELECT idUser_maestro FROM user_maestro WHERE idUsuario = ?)
";
$stmt_update_educativo = $conn->prepare($sql_update_educativo);
if ($stmt_update_educativo === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta de perfil educativo']);
    exit();
}
$stmt_update_educativo->bind_param("sissi", $especializacion, $dia, $hora_inicio_atencion, $hora_fin_atencion, $idUsuario);

$educativo_actualizado = $stmt_update_educativo->execute();
if ($educativo_actualizado === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar los datos educativos']);
    exit();
}

echo json_encode(['status' => 'success', 'message' => 'Datos actualizados correctamente']);

$stmt_update_domicilio->close();
$stmt_update_personales->close();
$stmt_update_educativo->close();
$conn->close();
?>
