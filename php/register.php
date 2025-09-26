<?php
// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Función para registrar errores
function logError($message) {
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/error.log';
    
    // Crear directorio de logs si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Mensaje con timestamp
    $logMessage = "[" . date('Y-m-d H:i:s') . "] $message" . PHP_EOL;
    
    // Escribir en el archivo de log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // También mostrar el error para depuración
    if (php_sapi_name() !== 'cli') {
        error_log($logMessage);
    }
}

// Incluir archivo de conexión
include 'conexion.php';

// Verificar si la conexión se estableció correctamente
if (!isset($conn) || $conn->connect_error) {
    $errorMsg = "Error de conexión: " . ($conn->connect_error ?? 'No se pudo establecer la conexión');
    logError($errorMsg);
    echo json_encode(["status" => "error", "msg" => $errorMsg]);
    exit;
}

// Verificar si la base de datos existe
if (!$conn->select_db('mydaily')) {
    $errorMsg = "La base de datos 'mydaily' no existe";
    logError($errorMsg);
    echo json_encode(["status" => "error", "msg" => $errorMsg]);
    exit;
}

// Crear la tabla si no existe
$createTable = "CREATE TABLE IF NOT EXISTS `usuario` (
    `id_us` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `passw` VARCHAR(255) NOT NULL,
    `status` TINYINT(1) DEFAULT 1,
    `correo` VARCHAR(100) NOT NULL UNIQUE,
    `per_nom` VARCHAR(50) NOT NULL,
    `fn` DATE NOT NULL,
    `per_ape` VARCHAR(50) NOT NULL,
    `gen` VARCHAR(20) NOT NULL,
    `conf_passw` VARCHAR(255) NOT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($createTable)) {
    logError("Error al crear la tabla: " . $conn->error);
    // No lanzamos excepción aquí para permitir que el script continúe
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Registrar datos recibidos
    logError("Datos recibidos: " . print_r($_POST, true));
    try {
        // Recibir y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $fn = $_POST['fn'] ?? '';
        $sexo = trim($_POST['sexo'] ?? '');
        $correo = trim($_POST['Correo'] ?? ''); // Cambiado a 'Correo' para coincidir con el formulario
        $username = trim($_POST['username'] ?? '');
        $pass = trim($_POST['pass'] ?? '');
        $conf_pass = trim($_POST['conf_pass'] ?? '');

        // Validar campos vacíos
        if (empty($nombre) || empty($apellido) || empty($fn) || empty($sexo) ||
            empty($correo) || empty($username) || empty($pass) || empty($conf_pass)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido.");
        }

        // Validar que las contraseñas coincidan
        if ($pass !== $conf_pass) {
            throw new Exception("Las contraseñas no coinciden.");
        }

        // Validar longitud mínima de contraseña
        if (strlen($pass) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres.");
        }

        // Verificar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id_us FROM usuario WHERE correo = ?");
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("El correo ya está registrado.");
        }
        $stmt->close();

        // Verificar si el nombre de usuario ya existe
        $stmt = $conn->prepare("SELECT id_us FROM usuario WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("El nombre de usuario ya está en uso.");
        }
        $stmt->close();

        // Hashear la contraseña
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            throw new Exception("Error al encriptar la contraseña.");
        }

        // Insertar nuevo usuario
        $sql = "INSERT INTO `usuario` (username, passw, status, correo, per_nom, fn, per_ape, gen, conf_passw) 
                VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)";
                
        logError("Consulta SQL: $sql");
        logError("Parámetros: $username, [hashed_password], $correo, $nombre, $fn, $apellido, $sexo, [hashed_password]");
        
        // Mostrar información de depuración
        logError("Intentando preparar la consulta...");
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = $conn->error;
            $errorMsg = "Error al preparar la consulta: $error";
            logError($errorMsg);
            throw new Exception($errorMsg);
        }
        
        logError("Consulta preparada correctamente");
        
        // Asegurarse de que la fecha esté en el formato correcto
        $fecha_nacimiento = date('Y-m-d', strtotime($fn));
        
        $stmt->bind_param("ssssssss", 
            $username, 
            $hashed_password, 
            $correo, 
            $nombre, 
            $fecha_nacimiento, 
            $apellido, 
            $sexo, 
            $hashed_password
        );
        
        logError("Ejecutando consulta con parámetros: " . print_r([$username, $correo, $nombre, $fecha_nacimiento, $apellido, $sexo], true));
        
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            logError("Usuario registrado con ID: $last_id");
            
            // Iniciar sesión automáticamente
            session_start();
            $_SESSION['user_id'] = $last_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $correo;
            
            echo json_encode([
                "status" => "ok", 
                "msg" => "Usuario registrado con éxito.",
                "redirect" => "../index.html"
            ]);
        } else {
            throw new Exception("Error al registrar el usuario: " . $stmt->error);
        }

    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        logError("Error en el registro: $errorMsg");
        echo json_encode([
            "status" => "error", 
            "msg" => $errorMsg,
            "debug" => [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString()
            ]
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo json_encode([
        "status" => "error", 
        "msg" => "Método de solicitud no válido."
    ]);
}
?>