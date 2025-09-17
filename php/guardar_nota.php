<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_us = $data["id_us"] ?? '';
$nombre = $data["nombre"] ?? '';
$contenido = $data["contenido"] ?? '';

if (empty($id_us) || empty($nombre) || empty($contenido)) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO notas (nom, cont, id_us) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $nombre, $contenido, $id_us);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "id_nota" => $conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "msg" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>