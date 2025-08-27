<?php
require_once 'Connections/Connection.php';

// Consulta para obtener los próximos juegos
$query = "
    SELECT 
        tm.match_id,
        t.name as torneo,
        tm.phase as fase,
        tm.status as estado,
        CASE 
            WHEN tm.is_bye = 1 THEN CONCAT(bye_team.team_name, ' (BYE)')
            WHEN tm.home_team_id IS NOT NULL AND tm.away_team_id IS NOT NULL 
                THEN CONCAT(home.team_name, ' vs ', away.team_name)
            ELSE 'Partido por definir'
        END as partido,
        CASE 
            WHEN tm.start_datetime IS NOT NULL 
                THEN DATE_FORMAT(tm.start_datetime, '%d/%m/%Y %H:%i')
            ELSE 'Por programar'
        END as fecha_hora,
        COALESCE(bc.court_name, 'Sin asignar') as cancha,
        tm.start_datetime
    FROM Team_Matches tm
    LEFT JOIN Tournaments t ON tm.tournament_id = t.tournament_id
    LEFT JOIN Teams home ON tm.home_team_id = home.team_id
    LEFT JOIN Teams away ON tm.away_team_id = away.team_id
    LEFT JOIN Teams bye_team ON tm.bye_team_id = bye_team.team_id
    LEFT JOIN Basketball_Courts bc ON tm.court_id = bc.court_id
    WHERE tm.status = 'SCHEDULED'
    ORDER BY 
        CASE WHEN tm.start_datetime IS NULL THEN 1 ELSE 0 END,
        tm.start_datetime ASC,
        tm.match_id ASC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $upcoming_games = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximos Juegos - INDES Basketball</title>
    <link rel="stylesheet" href="metro.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 0;
        }
        
        .games-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .games-table thead th {
            background: #2c3e50;
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 3px solid #34495e;
        }
        
        .games-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .games-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .games-table tbody tr:hover {
            background-color: #e3f2fd;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .games-table td {
            padding: 18px 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-scheduled {
            background: #e8f5e8;
            color: #2e7d2e;
            border: 1px solid #c8e6c8;
        }
        
        .status-playing {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .phase-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #6c5ce7;
            color: white;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .match-info {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .tournament-name {
            color: #e17055;
            font-weight: 600;
        }
        
        .datetime {
            color: #636e72;
            font-family: monospace;
        }
        
        .court-info {
            background: #ddd;
            color: #2d3436;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .no-games {
            text-align: center;
            padding: 50px;
            color: #636e72;
            font-size: 18px;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #00b894;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 184, 148, 0.3);
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 184, 148, 0.4);
        }
        
        @media (max-width: 768px) {
            .games-table {
                font-size: 12px;
            }
            
            .games-table thead th,
            .games-table td {
                padding: 12px 8px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏀 Próximos Juegos</h1>
            <p>Torneo de Basketball INDES Goldslide</p>
        </div>
        
        <div class="content">
            <?php if (count($upcoming_games) > 0): ?>
                <table class="games-table">
                    <thead>
                        <tr>
                            <th>Torneo</th>
                            <th>Fase</th>
                            <th>Estado</th>
                            <th>Partido</th>
                            <th>Fecha/Hora</th>
                            <th>Cancha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_games as $game): ?>
                            <tr>
                                <td>
                                    <span class="tournament-name">
                                        <?php echo htmlspecialchars($game['torneo'] ?? 'Sin torneo'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="phase-badge">
                                        <?php echo htmlspecialchars($game['fase'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($game['estado'] ?? 'unknown'); ?>">
                                        <?php 
                                        $status_labels = [
                                            'SCHEDULED' => 'Programado',
                                            'PLAYING' => 'Jugando',
                                            'FINISHED' => 'Finalizado',
                                            'CANCELLED' => 'Cancelado'
                                        ];
                                        echo $status_labels[$game['estado']] ?? $game['estado'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="match-info">
                                        <?php echo htmlspecialchars($game['partido']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="datetime">
                                        <?php echo htmlspecialchars($game['fecha_hora']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="court-info">
                                        <?php echo htmlspecialchars($game['cancha']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-games">
                    <p>🔍 No hay juegos programados en este momento</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <button class="refresh-btn" onclick="location.reload();" title="Actualizar">
        ↻
    </button>
    
    <script src="metro.js"></script>
    <script>
        // Auto-refresh cada 30 segundos
        setInterval(function() {
            location.reload();
        }, 30000);
        
        // Mostrar última actualización
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const timeString = now.toLocaleString('es-ES');
            
            // Agregar timestamp en la esquina
            const timestamp = document.createElement('div');
            timestamp.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                background: rgba(0,0,0,0.7);
                color: white;
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 12px;
                z-index: 1000;
            `;
            timestamp.textContent = 'Actualizado: ' + timeString;
            document.body.appendChild(timestamp);
        });
    </script>
</body>
</html>

<?php
// Cerrar la conexión
closeConnection();
?>