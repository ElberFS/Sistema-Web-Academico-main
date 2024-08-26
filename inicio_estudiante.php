<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
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
            <h2>Bienvenido, <?php echo htmlspecialchars($user_name); ?></h2>
            <p>¡Nos alegra verte de nuevo! Aquí tienes un resumen de tu progreso y próximas actividades.</p>
        </div>
        <div class="grid-container">
            <div class="academic-summary card">
                <h3>Resumen Académico</h3>
                <i class="fas fa-graduation-cap"></i> <!-- Icono para Resumen Académico -->
                <p>Promedio General: 8.5</p>
                <p>Asistencia: 95%</p>
                <p>Próximos Exámenes: Matemáticas (25 de Julio), Física (28 de Julio)</p>
            </div>
            <div class="schedule card">
                <h3>Horario de Clases</h3>
                <i class="fas fa-calendar-alt"></i> <!-- Icono para Horario de Clases -->
                <ul>
                    <li>Lunes - Matemáticas (08:00 - 09:30)</li>
                    <li>Martes - Física (10:00 - 11:30)</li>
                    <li>Miércoles - Química (12:00 - 13:30)</li>
                    <!-- Más clases aquí -->
                </ul>
            </div>
            <div class="announcements card">
                <h3>Anuncios</h3>
                <i class="fas fa-bullhorn"></i> <!-- Icono para Anuncios -->
                <ul>
                    <li>Evento de Ciencia - 30 de Julio</li>
                    <li>Reunión de Padres - 5 de Agosto</li>
                    <!-- Más anuncios aquí -->
                </ul>
            </div>
            <div class="tasks-exams card">
                <h3>Tareas y Exámenes</h3>
                <i class="fas fa-tasks"></i> <!-- Icono para Tareas y Exámenes -->
                <ul>
                    <li>Tarea de Matemáticas - Entrega: 23 de Julio</li>
                    <li>Examen de Física - 28 de Julio</li>
                    <!-- Más tareas y exámenes aquí -->
                </ul>
            </div>
            <div class="resources card">
                <h3>Recursos</h3>
                <i class="fas fa-book"></i> <!-- Icono para Recursos -->
                <ul>
                    <li><a href="#">Biblioteca Digital</a></li>
                    <li><a href="#">Materiales de Estudio</a></li>
                    <!-- Más recursos aquí -->
                </ul>
            </div>
            <div class="messages card">
                <h3>Mensajes Recientes</h3>
                <i class="fas fa-envelope"></i> <!-- Icono para Mensajes Recientes -->
                <ul>
                    <li>Mensaje de Profesor de Matemáticas - 21 de Julio</li>
                    <li>Mensaje de Administración - 20 de Julio</li>
                    <!-- Más mensajes aquí -->
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
