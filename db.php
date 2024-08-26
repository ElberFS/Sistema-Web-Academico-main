<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_academico";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
    echo "Conexión fallida". "<br>";
}

?>
