<?php
require_once 'Connections/Connection.php';

// Script para agregar datos de ejemplo de partidos completados
echo "<h2>Agregando datos de ejemplo...</h2>";

try {
    // Actualizar algunos partidos de la primera vuelta (IDA) con resultados
    $sample_results = [
        ['match_id' => 1, 'home_score' => 85, 'away_score' => 78, 'status' => 'COMPLETED'],
        ['match_id' => 2, 'home_score' => 92, 'away_score' => 89, 'status' => 'COMPLETED'],
        ['match_id' => 3, 'home_score' => 76, 'away_score' => 82, 'status' => 'COMPLETED'],
        ['match_id' => 5, 'home_score' => 88, 'away_score' => 91, 'status' => 'COMPLETED'],
        ['match_id' => 6, 'home_score' => 79, 'away_score' => 79, 'status' => 'COMPLETED'], // Empate
        ['match_id' => 7, 'home_score' => 95, 'away_score' => 72, 'status' => 'COMPLETED'],
        ['match_id' => 9, 'home_score' => 0, 'away_score' => 0, 'status' => 'COMPLETED', 'walkover' => 1], // Walkover para equipo local
        ['match_id' => 10, 'home_score' => 86, 'away_score' => 93, 'status' => 'COMPLETED'],
    ];
    
    foreach ($sample_results as $result) {
        $sql = "UPDATE Team_Matches SET 
                home_score = :home_score, 
                away_score = :away_score, 
                status = :status";
        
        $params = [
            'home_score' => $result['home_score'],
            'away_score' => $result['away_score'],
            'status' => $result['status']
        ];
        
        if (isset($result['walkover'])) {
            // Para walkover, determinar ganador
            $sql .= ", walkover_winner = (SELECT home_team_id FROM Team_Matches WHERE match_id = :match_id)";
        }
        
        $sql .= " WHERE match_id = :match_id";
        $params['match_id'] = $result['match_id'];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo "✅ Partido {$result['match_id']} actualizado<br>";
    }
    
    // Actualizar algunos partidos de la segunda vuelta (VUELTA)
    $vuelta_results = [
        ['match_id' => 15, 'home_score' => 91, 'away_score' => 87, 'status' => 'COMPLETED'],
        ['match_id' => 16, 'home_score' => 84, 'away_score' => 90, 'status' => 'COMPLETED'],
        ['match_id' => 17, 'home_score' => 77, 'away_score' => 77, 'status' => 'COMPLETED'], // Empate
        ['match_id' => 18, 'home_score' => 0, 'away_score' => 0, 'status' => 'COMPLETED', 'walkover' => 2], // Walkover para equipo visitante
    ];
    
    // Primero necesito verificar qué partidos corresponden a VUELTA
    $sql_vuelta = "SELECT match_id FROM Team_Matches WHERE phase = 'VUELTA' LIMIT 10";
    $stmt_vuelta = $pdo->prepare($sql_vuelta);
    $stmt_vuelta->execute();
    $vuelta_matches = $stmt_vuelta->fetchAll();
    
    foreach ($vuelta_matches as $index => $match) {
        if ($index < 4) { // Solo los primeros 4
            $result = $vuelta_results[$index];
            $match_id = $match['match_id'];
            
            $sql = "UPDATE Team_Matches SET 
                    home_score = :home_score, 
                    away_score = :away_score, 
                    status = :status";
            
            $params = [
                'home_score' => $result['home_score'],
                'away_score' => $result['away_score'],
                'status' => $result['status']
            ];
            
            if (isset($result['walkover'])) {
                if ($result['walkover'] == 1) {
                    $sql .= ", walkover_winner = (SELECT home_team_id FROM Team_Matches WHERE match_id = :match_id)";
                } else {
                    $sql .= ", walkover_winner = (SELECT away_team_id FROM Team_Matches WHERE match_id = :match_id)";
                }
            }
            
            $sql .= " WHERE match_id = :match_id";
            $params['match_id'] = $match_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo "✅ Partido de vuelta {$match_id} actualizado<br>";
        }
    }
    
    echo "<br><h3>✅ Datos de ejemplo agregados exitosamente!</h3>";
    echo "<p><a href='tabla_posiciones.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Tabla de Posiciones</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
