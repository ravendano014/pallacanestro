<?php
// Configuración de la base de datos
//$servername = "localhost";
//$username = "root";
//$password = ""; // Agregar contraseña si es necesario
//$database = "Indes_goldslide";

// OnLine 
$servername = "v2k5a1.h.filess.io";
$username = "Indes_goldslide";
$password = "ef99d455fe29d8a767b5eec740922efb1c4b9a61"; // Agregar contraseña si es necesario
$database = "Indes_goldslide";

try {
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    
    // Establecer el modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar para obtener resultados como arrays asociativos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para cerrar la conexión
function closeConnection() {
    global $pdo;
    $pdo = null;
}
?>
