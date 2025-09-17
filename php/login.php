<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $correo = $data['correo'] ?? '';
    $pass = $data['pass'] ?? '';

    if (empty($correo) || empty($pass)) {
        echo json_encode(["status" => "error", "msg" => "Correo y contraseña requeridos"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_us, passw FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['passw'])) {
            session_start();
            $_SESSION['id_us'] = $user['id_us'];
            echo json_encode(["status" => "ok", "msg" => "Login exitoso"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "msg" => "Usuario no encontrado"]);
    }

    $stmt->close();
    $conn->close();
}
?>