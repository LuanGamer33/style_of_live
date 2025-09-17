<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_us = $data["id_us"] ?? '';

if (empty($id_us)) {
    echo json_encode(["status" => "error", "msg" => "ID de usuario requerido"]);
    exit;
}

$stmt = $conn->prepare("SELECT id_notas, nom, cont FROM notas WHERE id_us = ? ORDER BY id_notas DESC");
$stmt->bind_param("i", $id_us);
$stmt->execute();
$result = $stmt->get_result();

$notas = [];
while ($row = $result->fetch_assoc()) {
    $notas[] = $row;
}

echo json_encode($notas);

$stmt->close();
$conn->close();
?>