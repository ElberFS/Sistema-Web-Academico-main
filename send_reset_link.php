<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50));
        $url = "http://tu_dominio/reset_password.php?token=$token";

        $sql = "UPDATE usuarios SET reset_token = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        $to = $email;
        $subject = "Restablecer Contraseña";
        $message = "Haz clic en el siguiente enlace para restablecer tu contraseña: $url";
        $headers = "From: no-reply@tu_dominio.com";

        if (mail($to, $subject, $message, $headers)) {
            echo "Correo enviado con éxito.";
        } else {
            echo "Error al enviar el correo.";
        }
    } else {
        echo "No se encontró el usuario.";
    }

    $stmt->close();
}
$conn->close();
?>
