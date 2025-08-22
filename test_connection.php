<?php
// Archivo de prueba para verificar la conexión y estructura de la tabla Teams
require_once 'Connections/Connection.php';

echo "<h2>Prueba de Conexión y Estructura de la Tabla Teams</h2>";

try {
    // Verificar conexión
    echo "<p><strong>✅ Conexión a la base de datos exitosa</strong></p>";
    echo "<p>Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "</p>";
    
    // Verificar si existe la tabla Teams
    $stmt = $pdo->query("SHOW TABLES LIKE 'Teams'");
    if ($stmt->rowCount() > 0) {
        echo "<p><strong>✅ Tabla 'Teams' encontrada</strong></p>";
        
        // Mostrar estructura de la tabla
        echo "<h3>Estructura de la tabla Teams:</h3>";
        $stmt = $pdo->query("DESCRIBE Teams");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Valor por Defecto</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros existentes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Teams");
        $count = $stmt->fetchColumn();
        echo "<p><strong>Total de equipos en la base de datos: " . $count . "</strong></p>";
        
        if ($count > 0) {
            echo "<h3>Equipos existentes:</h3>";
            $stmt = $pdo->query("SELECT * FROM Teams LIMIT 5");
            $teams = $stmt->fetchAll();
            
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Ciudad</th><th>Entrenador</th><th>Año Fundación</th></tr>";
            
            foreach ($teams as $team) {
                echo "<tr>";
                echo "<td>" . $team['id'] . "</td>";
                echo "<td>" . $team['name'] . "</td>";
                echo "<td>" . ($team['city'] ?? 'N/A') . "</td>";
                echo "<td>" . ($team['coach'] ?? 'N/A') . "</td>";
                echo "<td>" . ($team['founded_year'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            if ($count > 5) {
                echo "<p><em>Mostrando solo los primeros 5 equipos...</em></p>";
            }
        }
        
    } else {
        echo "<p><strong>❌ La tabla 'Teams' no existe</strong></p>";
        echo "<p>Ejecuta el siguiente SQL para crear la tabla:</p>";
        echo "<pre>";
        echo "CREATE TABLE Teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50),
    coach VARCHAR(100),
    founded_year INT,
    logo_url VARCHAR(255),
    description TEXT
);";
        echo "</pre>";
    }
    
} catch(PDOException $e) {
    echo "<p><strong>❌ Error de conexión:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Verifica la configuración en Connections/Connection.php</p>";
}

echo "<hr>";
echo "<p><a href='teams_crud.php'>→ Ir al CRUD de Equipos</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
table {
    background-color: white;
    border-collapse: collapse;
    margin: 10px 0;
}
th {
    background-color: #0078d4;
    color: white;
    padding: 8px;
}
td {
    padding: 8px;
}
pre {
    background: #f0f0f0;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #0078d4;
    overflow-x: auto;
}
a {
    color: #0078d4;
    text-decoration: none;
    font-weight: bold;
    font-size: 16px;
}
a:hover {
    text-decoration: underline;
}
</style>
