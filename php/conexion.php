<?php
$servername = "localhost";
$username = "root"; // el usuario por defecto de XAMPP
$password = "";     // en XAMPP normalmente la clave está vacía
$dbname = "mydaily";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Revisar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>