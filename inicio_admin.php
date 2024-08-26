<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Administrador</title>
    <link rel="stylesheet" href="styles_inicio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card i {
            font-size: 3em; /* Ajusta el tamaño del icono aquí */
            color: #333; /* Puedes cambiar el color si lo deseas */
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="welcome">
            <h1>Bienvenido, <?php echo htmlspecialchars($user_name); ?></h1>
            <p>Esta es tu página de inicio donde puedes acceder a las herramientas y recursos administrativos más importantes.</p>
        </div>

        <div class="grid-container">
            <div class="card">
                <h3>Gestión de Estudiantes</h3>
                <i class="fas fa-users fa-3x"></i>
                <p>Administra los datos de los estudiantes y revisa su información académica.</p>
            </div>
            <div class="card">
                <h3>Gestión de Horarios</h3>
                <i class="fas fa-calendar-week fa-3x"></i>
                <p>Configura y actualiza los horarios de clases y eventos.</p>
            </div>
            <div class="card">
                <h3>Gestión de Profesores</h3>
                <i class="fas fa-chalkboard-teacher fa-3x"></i>
                <p>Revisa y actualiza la información de los profesores.</p>
            </div>
            <div class="card">
                <h3>Gestión de Cursos</h3>
                <i class="fas fa-book fa-3x"></i>
                <p>Administra los cursos ofrecidos y sus detalles.</p>
            </div>
            <div class="card">
                <h3>Expedientes</h3>
                <i class="fas fa-file-alt fa-3x"></i>
                <p>Accede y gestiona los expedientes académicos y administrativos.</p>
            </div>
            <div class="card">
                <h3>Anuncios</h3>
                <i class="fas fa-bullhorn"></i> <!-- Icono para Anuncios -->
                <ul>
                    <li>Evento de Ciencia - 30 de Julio</li>
                    <li>Reunión de Padres - 5 de Agosto</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
