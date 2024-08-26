<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $sql = "UPDATE estudiantes SET nombre=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nombre, $id);
    if ($stmt->execute()) {
        header("Location: estudiantes.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT * FROM estudiantes WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$estudiante = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante</title>
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
        <h2>Editar Estudiante</h2>
        <form method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $estudiante['nombre']; ?>" required>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
