<?php
session_start();
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Preparar las consultas
    $sql_delete_formacion = "DELETE FROM formacion_academica WHERE idUser_maestro = ?";
    $sql_delete_perfil_educ = "DELETE FROM perfil_educativo WHERE idUser_maestro = ?";
    $sql_delete_user_maestro = "DELETE FROM user_maestro WHERE idUser_maestro = ?";

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Eliminar registros en formacion_academica
        $stmt = $conn->prepare($sql_delete_formacion);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Eliminar registros en perfil educativo
        $stmt = $conn->prepare($sql_delete_perfil_educ);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Eliminar el profesor en user_maestro
        $stmt = $conn->prepare($sql_delete_user_maestro);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Confirmar la transacción
        $conn->commit();
        
        // Redirigir de vuelta a la lista de profesores
        header("Location: dashboard.php?page=profesores");
        exit();
    } catch (Exception $e) {
        // Deshacer la transacción en caso de error
        $conn->rollback();
        
        // Mostrar mensaje de error
        echo "Error al eliminar el profesor: " . $e->getMessage();
    }
} else {
    echo "ID del profesor no especificado.";
}

$conn->close();
?>
