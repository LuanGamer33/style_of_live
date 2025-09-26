<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_us = $data["id_us"];
$nombre = $data["nombre"];
$contenido = $data["contenido"];

$sql = "INSERT INTO notas (nom, cont, id_us) VALUES ('$nombre', '$contenido', '$id_us')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "ok", "id_nota" => $conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "msg" => $conn->error]);
}

$conn->close();
?>
