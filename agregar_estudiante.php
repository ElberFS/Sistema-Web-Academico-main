<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $sql = "INSERT INTO estudiantes (nombre) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    if ($stmt->execute()) {
        header("Location: estudiantes.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Estudiante</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h2>I.E. 10022 Miguel Muro Zapata</h2>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="estudiantes.php">Estudiantes</a></li>
            <li><a href="#">Horarios</a></li>
            <li><a href="#">Profesores</a></li>
            <li><a href="#">Cursos</a></li>
            <li><a href="#">Expedientes Académicos</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>Agregar Estudiante</h2>
        <form method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <button type="submit">Agregar</button>
        </form>
    </div>
</body>
</html>
