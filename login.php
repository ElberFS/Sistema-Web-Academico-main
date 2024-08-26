<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['User_dni'];
    $password = $_POST['User_password'];

    $sql = "SELECT * FROM usuario WHERE User_dni = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "Ingresado: " . $password . "<br>"; // Mostrar contraseña ingresada
        echo "Almacenado: " . $user['User_password'] . "<br>"; // Mostrar contraseña almacenada

        // Comparar directamente la contraseña sin hash
        if ($password === $user['User_password']) {
            $_SESSION['user_id'] = $user['idUsuario'];
            $_SESSION['user_profile'] = $user['idPerfil']; // Almacena el perfil en la sesión
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        $_SESSION['error'] = "Usuario no encontrado";
        header("Location: index.php?error=1");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
