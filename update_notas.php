<?php
include 'db.php';

// Lee el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['notas']) && isset($data['idEvaluacion'])) {
    $notas = $data['notas'];
    $idEvaluacion = $data['idEvaluacion'];

    // Actualiza las notas en la base de datos
    foreach ($notas as $nota) {
        $idUser_estudiante = $nota['idUser_estudiante'];
        $nota_valor = $nota['nota'];

        $sql = "UPDATE calificacion SET Nota = ? WHERE idEvaluacion = ? AND idUser_estudiante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $nota_valor, $idEvaluacion, $idUser_estudiante);
        $stmt->execute();
    }

    echo 'Notas actualizadas con éxito';
} else {
    echo 'Datos incompletos';
}

$conn->close();
?>
