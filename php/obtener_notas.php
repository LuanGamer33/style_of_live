<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_us = $data["id_us"];

// Consultar las notas del usuario
$sql = "SELECT id_notas, nom, cont FROM notas WHERE id_us = $id_us ORDER BY id_notas DESC";
$result = $conn->query($sql);

$notas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notas[] = $row;
    }
}

echo json_encode($notas);

$conn->close();
?>
