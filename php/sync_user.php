<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"];
$nombre = $data["nombre"];

// Verificar si el usuario ya existe en la tabla `usuario`
$sql = "SELECT id_us FROM usuario WHERE correo = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Usuario ya existe
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "ok", "id_us" => $row["id_us"]]);
} else {
    // Insertar usuario nuevo
    $sql = "INSERT INTO usuario (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw)
            VALUES ('$nombre', '', 1, '$email', '$nombre', '2000-01-01', '', '', '')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "nuevo", "id_us" => $conn->insert_id]);
    } else {
        echo json_encode(["status" => "error", "msg" => $conn->error]);
    }
}
$conn->close();
?>
