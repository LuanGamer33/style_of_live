<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? '';
$nombre = $data["nombre"] ?? '';
$firebase_uid = $data["firebase_uid"] ?? '';

if (empty($email) || empty($firebase_uid)) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$stmt = $conn->prepare("SELECT id_us FROM usuario WHERE firebase_uid = ?");
$stmt->bind_param("s", $firebase_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "ok", "id_us" => $row["id_us"]]);
} else {
    $stmt = $conn->prepare("INSERT INTO usuario (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw, firebase_uid)
                            VALUES (?, '', 1, ?, ?, '2000-01-01', '', '', '', ?)");
    $stmt->bind_param("ssss", $nombre, $email, $nombre, $firebase_uid);

    if ($stmt->execute()) {
        echo json_encode(["status" => "nuevo", "id_us" => $conn->insert_id]);
    } else {
        echo json_encode(["status" => "error", "msg" => $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>