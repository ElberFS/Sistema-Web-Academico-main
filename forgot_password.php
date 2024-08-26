<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="styles_password.css">
</head>
<body>
    <div class="container">
        <div class="forgot-password-box">
            <h2>Recuperar contraseña</h2>
            <p>Recibe un correo electrónico para restablecer tu contraseña o comuníquese con el administrador.</p>
            <form action="send_reset_link.php" method="post">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
                <button type="submit">Enviar</button>
                <button type="button" onclick="window.location.href='index.php';">Cancelar</button>
            </form>
        </div>
    </div>
</body>
</html>
