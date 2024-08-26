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
            <h1>Bienvenido, <?php echo htmlspecialchars($user_name); ?></h1>
            <p>Esta es tu página de inicio donde puedes acceder a tus herramientas y recursos más importantes.</p>
        </div>

        <div class="grid-container">
            <div class="card">
                <h3>Calendario</h3>
                <i class="fas fa-calendar-alt fa-3x"></i>
                <p>Consulta el calendario para conocer tus próximas clases y eventos importantes.</p>
            </div>
            <div class="card">
                <h3>Mis Clases</h3>
                <i class="fas fa-chalkboard-teacher fa-3x"></i>
                <p>Revisa la lista de clases que impartirás esta semana.</p>
            </div>
            <div class="card">
                <h3>Asistencias</h3>
                <i class="fas fa-user-check fa-3x"></i>
                <p>Registra y revisa las asistencias de tus alumnos.</p>
            </div>
            <div class="card">
                <h3>Recursos</h3>
                <i class="fas fa-book fa-3x"></i>
                <p>Accede a materiales y recursos educativos para tus clases.</p>
            </div>
        </div>
    </div>
</body>
</html>
