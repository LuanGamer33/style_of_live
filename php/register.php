<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $nombre = trim($data['nombre'] ?? '');
    $apellido = trim($data['apellido'] ?? '');
    $fn = $data['fn'] ?? '';
    $sexo = trim($data['sexo'] ?? '');
    $correo = trim($data['correo'] ?? '');
    $username = trim($data['username'] ?? '');
    $pass = $data['pass'] ?? '';
    $conf_pass = $data['conf_pass'] ?? '';

    if (
        empty($nombre) || empty($apellido) || empty($fn) || empty($sexo) ||
        empty($correo) || empty($username) || empty($pass) || empty($conf_pass)
    ) {
        echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios"]);
        exit;
    }

    if ($pass !== $conf_pass) {
        echo json_encode(["status" => "error", "msg" => "Las contraseñas no coinciden"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_us FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "msg" => "El correo ya está registrado"]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuario (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw)
                            VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $username, $hash, $correo, $nombre, $fn, $apellido, $sexo, $hash);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "msg" => "Usuario registrado con éxito"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al registrar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>