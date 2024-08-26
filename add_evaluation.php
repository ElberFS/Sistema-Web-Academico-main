<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $tipoEvaluacion = intval($_POST['tipoEvaluacion']);
    $fecha = date('Y-m-d H:i:s'); // Establece la fecha actual

    // Validar campos
    if (empty($descripcion) || empty($tipoEvaluacion)) {
        echo "Todos los campos son requeridos.";
        exit();
    }

    // Insertar la nueva evaluación en la base de datos
    $sql = "INSERT INTO evaluacion (Descripcion, Fecha, idTipo_evaluacion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $descripcion, $fecha, $tipoEvaluacion);

    if ($stmt->execute()) {
        echo "Evaluación añadida exitosamente.";
    } else {
        echo "Error al añadir la evaluación: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
