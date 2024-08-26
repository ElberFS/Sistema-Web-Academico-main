<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$sql = "DELETE FROM estudiantes WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: estudiantes.php");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
