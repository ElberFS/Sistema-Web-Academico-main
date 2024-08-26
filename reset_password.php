<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="styles_password.css">
</head>
<body>
    <div class="container">
        <div class="reset-password-box">
            <h2>Restablecer Contraseña</h2>
            <?php
            if (isset($_GET['token'])) {
                $token = $_GET['token'];
                echo '
                <form action="update_password.php" method="post">
                    <input type="hidden" name="token" value="' . $token . '">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="submit">Restablecer</button>
                </form>';
            } else {
                echo '<p>Token no válido.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
