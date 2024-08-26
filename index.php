<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="logo.png" alt="Logo">
            <h3>Sistema Académico - <br>Miguel Muro Zapata</h3>
        </div>
        <div class="right">
            <h2>¡Bienvenido de nuevo!</h2>
            <form action="login.php" method="post">
                <input type="text" id="dni" name="User_dni" placeholder="Dni" required>
                <input type="password" id="password" name="User_password" placeholder="Contraseña" required>
                <button type="submit" id="loginButton">Iniciar sesión</button>
            </form>
            <a href='forgot_password.php'>Recuperar contraseña</a>
        </div>
    </div>

    <!-- Superposición -->
    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Modal de Error -->
    <div id="errorModal" class="error-modal">
        <p id="errorMessage">Usuario o contraseña incorrectos. Por favor, inténtelo nuevamente.</p>
        <button onclick="closeErrorModal()">Aceptar</button>
    </div>

    <script> 
        window.onload = function() {
            var error = '<?php echo isset($_GET['error']) ? $_GET['error'] : ''; ?>';
            var loginButton = document.getElementById('loginButton');
            var modalOverlay = document.getElementById('modalOverlay');

            if (error === '1') {
                openErrorModal();
            }

            function openErrorModal() {
                document.getElementById('errorModal').style.display = 'block';
                modalOverlay.style.display = 'block'; // Mostrar superposición
                loginButton.disabled = true; // Deshabilitar el botón cuando se muestra el modal
                document.body.classList.add('modal-open'); // Añadir clase para evitar el desplazamiento del contenido
            }

            function closeErrorModal() {
                document.getElementById('errorModal').style.display = 'none';
                modalOverlay.style.display = 'none'; // Ocultar superposición
                loginButton.disabled = false; // Habilitar el botón cuando se cierra el modal
                document.body.classList.remove('modal-open'); // Eliminar clase para permitir el desplazamiento del contenido
                window.location.href = 'index.php';
            }

            window.closeErrorModal = closeErrorModal; // Hacer la función accesible globalmente
        };
    </script>
</body>
</html>
