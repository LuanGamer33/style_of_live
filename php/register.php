<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fn = $_POST['fn'];
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    $sql = "INSERT INTO usuario (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw)
            VALUES ('$username', '$pass', 1, '$correo', '$nombre', '$fn', '$apellido', '$sexo', '$pass')";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado con éxito";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
