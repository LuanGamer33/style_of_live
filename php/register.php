<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $fn = $_POST['fn'];
    $sexo = trim($_POST['sexo']);
    $correo = trim($_POST['correo']);
    $username = trim($_POST['username']);
    $pass = trim($_POST['pass']);

    // Validar campos vacíos
    if (
        empty($nombre) || empty($apellido) || empty($fn) || empty($sexo) ||
        empty($correo) || empty($username) || empty($pass)
    ) {
        echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios."]);
        exit;
    }

    // Verificar si el correo ya está registrado
    $stmt = $conn->prepare("SELECT id_us FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "msg" => "El correo ya está registrado."]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuario (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw)
                            VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $username, $pass, $correo, $nombre, $fn, $apellido, $sexo, $pass);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "msg" => "Usuario registrado con éxito."]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al registrar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
