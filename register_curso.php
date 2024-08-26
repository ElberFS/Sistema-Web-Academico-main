<?php
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    // Validar los datos (básico)
    if (!empty($nombre) && !empty($descripcion)) {
        // Preparar la consulta SQL
        $sql = "INSERT INTO curso (Nombre, Descripcion) VALUES (?, ?)";

        // Preparar y ejecutar la consulta
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $nombre, $descripcion);
            
            if ($stmt->execute()) {
                // Redirigir a la página de cursos con un mensaje de éxito
                header("Location: dashboard.php?page=cursos");
                exit;
            } else {
                echo "Error al registrar el curso: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, complete todos los campos.";
    }

    $conn->close();
}
?>
