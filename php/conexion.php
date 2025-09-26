<?php
// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'mydaily'
];

// Función para registrar errores
function logError($message) {
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/error.log';
    
    // Crear directorio de logs si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Escribir en el archivo de log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // También mostrar el error para depuración
    if (php_sapi_name() !== 'cli') {
        error_log($logMessage);
    }
}

// Inicializar conexión
$conn = null;

// Registrar inicio del script
logError("=== Iniciando script de conexión ===");
logError("Versión de PHP: " . phpversion());
logError("Extensión MySQLi: " . (extension_loaded('mysqli') ? 'Cargada' : 'No cargada'));

try {
    logError("Intentando conectar a la base de datos...");
    logError("Host: {$config['host']}");
    logError("Usuario: {$config['username']}");
    logError("Base de datos: {$config['dbname']}");
    
    // Intentar conectar a la base de datos
    $conn = @new mysqli(
        $config['host'],
        $config['username'],
        $config['password']
    );

    // Verificar si hay error de conexión
    if ($conn->connect_error) {
        logError("Error en la conexión inicial: " . $conn->connect_error);
            // Si la base de datos no existe, intentar crearla
            if ($conn->connect_errno == 1049) { // Error 1049: Base de datos desconocida
                logError("La base de datos no existe, intentando crearla...");
                
                // Conectar sin seleccionar base de datos
                $conn = new mysqli($config['host'], $config['username'], $config['password']);
                
                if ($conn->connect_error) {
                    $error = "Error al conectar al servidor: " . $conn->connect_error;
                    logError($error);
                    throw new Exception($error);
                }
                
                // Crear la base de datos
                $sql = "CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                
                logError("Ejecutando: $sql");
                if ($conn->query($sql) === TRUE) {
                    logError("Base de datos creada exitosamente");
                    
                    // Seleccionar la base de datos
                    if (!$conn->select_db($config['dbname'])) {
                        $error = "No se pudo seleccionar la base de datos: " . $conn->error;
                        logError($error);
                        throw new Exception($error);
                    }
                    logError("Base de datos seleccionada: {$config['dbname']}");
                } else {
                    $error = "Error al crear la base de datos: " . $conn->error;
                    logError($error);
                    throw new Exception($error);
                }      } else {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
    }
    
    // Configurar el conjunto de caracteres
    if (!$conn->set_charset("utf8mb4")) {
        $error = "Error al configurar el conjunto de caracteres: " . $conn->error;
        logError($error);
        throw new Exception($error);
    }
    
    // Verificar si la tabla de usuarios existe, si no, crearla
    logError("Verificando existencia de la tabla 'usuario'...");
    $tableCheck = $conn->query("SHOW TABLES LIKE 'usuario'");
    
    if ($tableCheck === false) {
        $error = "Error al verificar la tabla: " . $conn->error;
        logError($error);
        throw new Exception($error);
    }
    if ($tableCheck->num_rows === 0) {
        logError("La tabla 'usuario' no existe, creando...");
        
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
        
        logError("Ejecutando: $createTable");
        
        if ($conn->query($createTable) === FALSE) {
            $error = "Error al crear la tabla: " . $conn->error;
            logError($error);
            throw new Exception($error);
        }
        logError("Tabla 'usuario' creada exitosamente");
    } else {
        logError("La tabla 'usuario' ya existe");
    }
    
    logError("Conexión establecida correctamente");
    
} catch (Exception $e) {
    logError("Error en la conexión: " . $e->getMessage());
    // No detener la ejecución aquí, permitir que el script maneje el error
    $conn = null;
}
?>