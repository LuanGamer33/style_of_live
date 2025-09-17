<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM usuario WHERE correo='$correo' AND passw='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        session_start();
        $_SESSION['correo'] = $correo;
        echo "success";
    } else {
        echo "error";
    }
    $conn->close();
}

$stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ? AND passw = ?");
$stmt->bind_param("ss", $correo, $pass);
$stmt->execute();
$result = $stmt->get_result();
?>
