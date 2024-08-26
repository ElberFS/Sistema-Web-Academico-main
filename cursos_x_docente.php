<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$idUsuario = $_SESSION['user_id'];

// Obtén el idUser_maestro correspondiente al usuario actual
$sql = "SELECT idUser_maestro FROM user_maestro WHERE idUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$user_maestro = $result->fetch_assoc();
$stmt->close();

if (!$user_maestro) {
    die("No se encontró el usuario maestro.");
}

$idUser_maestro = $user_maestro['idUser_maestro'];

// Obtén los cursos del docente sin duplicados
$sql = "
    SELECT DISTINCT c.idCurso, c.Nombre, g.NroGrado, g.Seccion
    FROM curso c
    JOIN horario h ON c.idCurso = h.idCurso
    JOIN grado g ON h.idGrado = g.idGrado
    JOIN maestro_horario mh ON h.idHorario = mh.idHorario
    WHERE mh.idUser_maestro = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUser_maestro);
$stmt->execute();
$result = $stmt->get_result();
$cursos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link rel="stylesheet" href="styles_courses_teacher.css">
</head>
<body>
    <div class="courses-header">
        <h1>Cursos que Imparte</h1>
    </div> 
    <main>
        <div class="course-container">
            <?php foreach ($cursos as $curso): ?>
                <div class="course-card">
                    <h2><?php echo htmlspecialchars($curso['Nombre']); ?></h2>
                    <p>Grado: <?php echo htmlspecialchars($curso['NroGrado']); ?></p>
                    <p>Sección: <?php echo htmlspecialchars($curso['Seccion']); ?></p>
                    <a href="dashboard.php?page=curso_detalle&idCurso=<?php echo htmlspecialchars($curso['idCurso']); ?>&NroGrado=<?php echo htmlspecialchars($curso['NroGrado']); ?>&Seccion=<?php echo htmlspecialchars($curso['Seccion']); ?>" class="view-details">Ver Detalles</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
