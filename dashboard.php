<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
    include 'db.php';

    // Obtén el perfil del usuario desde la sesión
    $user_profile = isset($_SESSION['user_profile']) ? $_SESSION['user_profile'] : 2; // Valor predeterminado es 'Estudiante'
    $user_id = $_SESSION['user_id'];

    // Obtén el archivo a incluir según el parámetro 'page' en la URL
    $page = isset($_GET['page']) ? $_GET['page'] : '';

    if ($page === '') {
        // Asigna la página predeterminada según el perfil del usuario
        switch ($user_profile) {
            case 1:
                $page = 'inicio_admin'; // Administrador
                break;
            case 2:
                $page = 'inicio_estudiante'; // Estudiante
                break;
            case 3:
                $page = 'inicio_docente'; // Docente
                break;
            default:
                $page = 'inicio_estudiante'; // Valor predeterminado por si el perfil no está definido
                break;
        }
    }

    $valid_pages = [
        'inicio_estudiante', 'perfil_estudiante', 'horario_estudiante', 'profesores_x_estudiante', 'cursos_x_estudiante', 'record_academico',
        'inicio_docente', 'perfil_docente', 'horario_docente', 'asistencias', 'cursos_x_docente', 'curso_detalle',
        'inicio_admin', 'estudiantes', 'horarios_admin', 'profesores', 'cursos', 'expedientes'
    ];

    // Verifica que la página solicitada sea válida
    if (!in_array($page, $valid_pages)) {
        $page = 'inicio_estudiante'; // Carga la página por defecto si la solicitada no es válida
    }

    // Obtener el nombre del usuario
    $sql = "SELECT Nombre_apellido FROM datos_personales WHERE idUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_name);
    $stmt->fetch();
    $stmt->close();

    // Cerrar la conexión
    $conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Academico - MMZ</title>
    <link rel="stylesheet" href="styles_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-left">
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="header-title">
                I.E. 10022 MIGUEL MURO ZAPATA
            </div>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
        </div>
    </header>
    <nav class="main-menu">
        <ul>
            <?php if ($user_profile === 1): // Administrador ?>
                <li><a href="?page=inicio_admin"><i class="fas fa-home"></i><span>Inicio</span></a></li>
                <li><a href="?page=estudiantes"><i class="fas fa-users"></i><span>Estudiantes</span></a></li>
                <li><a href="?page=horarios_admin"><i class="fas fa-calendar-alt"></i><span>Horarios</span></a></li>
                <li><a href="?page=profesores"><i class="fas fa-chalkboard-teacher"></i><span>Profesores</span></a></li>
                <li><a href="?page=cursos"><i class="fas fa-book"></i><span>Cursos</span></a></li>
                <li><a href="?page=expedientes"><i class="fas fa-folder"></i><span>Expedientes</span></a></li>
            <?php elseif ($user_profile === 2): // Estudiante ?>
                <li><a href="?page=inicio_estudiante"><i class="fas fa-home"></i><span>Inicio</span></a></li>
                <li><a href="?page=perfil_estudiante"><i class="fas fa-user"></i><span>Datos Personales</span></a></li>
                <li><a href="?page=horario_estudiante"><i class="fas fa-calendar-alt"></i><span>Horario</span></a></li>
                <li><a href="?page=profesores_x_estudiante"><i class="fas fa-chalkboard-teacher"></i><span>Profesores</span></a></li>
                <li><a href="?page=cursos_x_estudiante"><i class="fas fa-book"></i><span>Cursos</span></a></li>
                <li><a href="?page=record_academico"><i class="fas fa-graduation-cap"></i><span>Récord Académico</span></a></li>
            <?php elseif ($user_profile === 3): // Docente ?>
                <li><a href="?page=inicio_docente"><i class="fas fa-home"></i><span>Inicio</span></a></li>
                <li><a href="?page=perfil_docente"><i class="fas fa-user"></i><span>Datos Personales</span></a></li>
                <li><a href="?page=horario_docente"><i class="fas fa-calendar-alt"></i><span>Horario</span></a></li>
                <li><a href="?page=asistencias"><i class="fas fa-calendar-check"></i><span>Asistencias</span></a></li>
                <li><a href="?page=cursos_x_docente"><i class="fas fa-book"></i><span>Cursos</span></a></li>
            <?php endif; ?>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a></li>
        </ul>
    </nav>
    <script src="script.js"></script>
    <div class="content">
        <?php
            // Incluye el archivo de contenido basado en el parámetro 'page'
            include($page . '.php');
        ?>
    </div>
</body>
</html>
