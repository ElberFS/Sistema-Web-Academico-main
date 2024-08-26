<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $sql = "SELECT * FROM usuarios WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $sql = "UPDATE usuarios SET password = ?, reset_token = NULL WHERE reset_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $token);
        $stmt->execute();

        echo "Contraseña restablecida con éxito.";
    } else {
        echo "Token no válido.";
    }

    $stmt->close();
}
$conn->close();
?>
