<?php
require_once 'Connections/Connection.php';

// Función para calcular estadísticas de equipo por fase
function calculateTeamStats($team_id, $phase, $pdo) {
    $stats = [
        'juegos_jugados' => 0,
        'juegos_ganados' => 0,
        'juegos_perdidos' => 0,
        'juegos_empatados' => 0,
        'juegos_ganados_default' => 0,
        'juegos_perdidos_default' => 0,
        'puntos' => 0,
        'goles_favor' => 0,
        'goles_contra' => 0,
        'diferencia_goles' => 0
    ];
    
    // Obtener configuración de puntos del torneo
    $sql_config = "SELECT win_points, draw_points, loss_points, wo_win_points, wo_loss_points 
                   FROM Tournaments WHERE tournament_id = 1";
    $stmt_config = $pdo->prepare($sql_config);
    $stmt_config->execute();
    $config = $stmt_config->fetch();
    
    // Obtener partidos del equipo en la fase específica
    $sql = "SELECT tm.*, 
                   home.team_name as home_team_name, 
                   away.team_name as away_team_name
            FROM Team_Matches tm
            LEFT JOIN Teams home ON tm.home_team_id = home.team_id
            LEFT JOIN Teams away ON tm.away_team_id = away.team_id
            WHERE (tm.home_team_id = ? OR tm.away_team_id = ?) 
            AND tm.phase = ?
            AND tm.is_bye = 0
            AND tm.status = 'COMPLETED'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$team_id, $team_id, $phase]);
    $matches = $stmt->fetchAll();
    
    foreach ($matches as $match) {
        $stats['juegos_jugados']++;
        
        $is_home = ($match['home_team_id'] == $team_id);
        $team_score = $is_home ? $match['home_score'] : $match['away_score'];
        $opponent_score = $is_home ? $match['away_score'] : $match['home_score'];
        
        // Agregar puntos a favor y en contra
        $stats['goles_favor'] += $team_score;
        $stats['goles_contra'] += $opponent_score;
        
        // Verificar si hay walkover
        if ($match['walkover_winner']) {
            if ($match['walkover_winner'] == $team_id) {
                $stats['juegos_ganados_default']++;
                $stats['puntos'] += $config['wo_win_points'];
            } else {
                $stats['juegos_perdidos_default']++;
                $stats['puntos'] += $config['wo_loss_points'];
            }
        } else {
            // Resultado normal
            if ($team_score > $opponent_score) {
                $stats['juegos_ganados']++;
                $stats['puntos'] += $config['win_points'];
            } elseif ($team_score < $opponent_score) {
                $stats['juegos_perdidos']++;
                $stats['puntos'] += $config['loss_points'];
            } else {
                $stats['juegos_empatados']++;
                $stats['puntos'] += $config['draw_points'];
            }
        }
    }
    
    $stats['diferencia_goles'] = $stats['goles_favor'] - $stats['goles_contra'];
    return $stats;
}

// Función para combinar estadísticas de ambas fases
function combineStats($stats1, $stats2) {
    return [
        'juegos_jugados' => $stats1['juegos_jugados'] + $stats2['juegos_jugados'],
        'juegos_ganados' => $stats1['juegos_ganados'] + $stats2['juegos_ganados'],
        'juegos_perdidos' => $stats1['juegos_perdidos'] + $stats2['juegos_perdidos'],
        'juegos_empatados' => $stats1['juegos_empatados'] + $stats2['juegos_empatados'],
        'juegos_ganados_default' => $stats1['juegos_ganados_default'] + $stats2['juegos_ganados_default'],
        'juegos_perdidos_default' => $stats1['juegos_perdidos_default'] + $stats2['juegos_perdidos_default'],
        'puntos' => $stats1['puntos'] + $stats2['puntos'],
        'goles_favor' => $stats1['goles_favor'] + $stats2['goles_favor'],
        'goles_contra' => $stats1['goles_contra'] + $stats2['goles_contra'],
        'diferencia_goles' => $stats1['diferencia_goles'] + $stats2['diferencia_goles']
    ];
}

// Obtener todos los equipos
$sql_teams = "SELECT team_id, team_name FROM Teams ORDER BY team_name";
$stmt_teams = $pdo->prepare($sql_teams);
$stmt_teams->execute();
$teams = $stmt_teams->fetchAll();

// Calcular estadísticas para cada fase
$primera_vuelta = [];
$segunda_vuelta = [];
$tabla_general = [];

foreach ($teams as $team) {
    $stats_ida = calculateTeamStats($team['team_id'], 'IDA', $pdo);
    $stats_vuelta = calculateTeamStats($team['team_id'], 'VUELTA', $pdo);
    $stats_general = combineStats($stats_ida, $stats_vuelta);
    
    $primera_vuelta[] = [
        'equipo' => $team['team_name'],
        'stats' => $stats_ida
    ];
    
    $segunda_vuelta[] = [
        'equipo' => $team['team_name'],
        'stats' => $stats_vuelta
    ];
    
    $tabla_general[] = [
        'equipo' => $team['team_name'],
        'stats' => $stats_general
    ];
}

// Ordenar por puntos (descendente)
usort($primera_vuelta, function($a, $b) {
    return $b['stats']['puntos'] - $a['stats']['puntos'];
});

usort($segunda_vuelta, function($a, $b) {
    if ($b['stats']['puntos'] == $a['stats']['puntos']) {
        return $b['stats']['diferencia_goles'] - $a['stats']['diferencia_goles'];
    }
    return $b['stats']['puntos'] - $a['stats']['puntos'];
});

usort($tabla_general, function($a, $b) {
    if ($b['stats']['puntos'] == $a['stats']['puntos']) {
        return $b['stats']['diferencia_goles'] - $a['stats']['diferencia_goles'];
    }
    return $b['stats']['puntos'] - $a['stats']['puntos'];
});

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Posiciones - INDES Goldslide</title>
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
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .phases-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .general-table-container {
            margin-top: 40px;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 2px solid #667eea;
        }
        
        .phase-section {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }
        
        .phase-title {
            text-align: center;
            color: #667eea;
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .positions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .positions-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .positions-table td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e1e8ed;
            transition: background-color 0.3s ease;
        }
        
        .positions-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .positions-table tbody tr:hover {
            background-color: #e3f2fd;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .team-name {
            font-weight: bold;
            color: #2c3e50;
            text-align: left !important;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .position {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: bold;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            line-height: 25px;
            margin: 0 auto;
        }
        
        .stats-summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        
        .legend {
            margin-top: 20px;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 8px;
            font-size: 12px;
        }
        
        .legend h4 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .legend-item {
            display: inline-block;
            margin: 0 15px;
            color: #5a6c7d;
        }
        
        @media (max-width: 768px) {
            .phases-container {
                grid-template-columns: 1fr;
            }
            
            .positions-table {
                font-size: 12px;
            }
            
            .positions-table th,
            .positions-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏀 TABLA DE POSICIONES</h1>
            <p style="color: #7f8c8d; font-size: 1.2em;">Torneo INDES Goldslide - Basketball Masculino</p>
        </div>
        
        <div class="phases-container">
            <!-- Primera Vuelta -->
            <div class="phase-section">
                <h2 class="phase-title">🔄 Primera Vuelta (IDA)</h2>
                <table class="positions-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Equipo</th>
                            <th>PJ</th>
                            <th>PG</th>
                            <th>PP</th>
                            <th>PE</th>
                            <th>PGD</th>
                            <th>PPD</th>
                            <th>PF</th>
                            <th>PC</th>
                            <th>Dif</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($primera_vuelta as $index => $team): ?>
                        <tr>
                            <td><div class="position"><?php echo $index + 1; ?></div></td>
                            <td class="team-name"><?php echo htmlspecialchars($team['equipo']); ?></td>
                            <td><?php echo $team['stats']['juegos_jugados']; ?></td>
                            <td><?php echo $team['stats']['juegos_ganados']; ?></td>
                            <td><?php echo $team['stats']['juegos_perdidos']; ?></td>
                            <td><?php echo $team['stats']['juegos_empatados']; ?></td>
                            <td><?php echo $team['stats']['juegos_ganados_default']; ?></td>
                            <td><?php echo $team['stats']['juegos_perdidos_default']; ?></td>
                            <td><?php echo $team['stats']['goles_favor']; ?></td>
                            <td><?php echo $team['stats']['goles_contra']; ?></td>
                            <td style="color: <?php echo $team['stats']['diferencia_goles'] >= 0 ? 'green' : 'red'; ?>"><?php echo ($team['stats']['diferencia_goles'] >= 0 ? '+' : '') . $team['stats']['diferencia_goles']; ?></td>
                            <td><strong><?php echo $team['stats']['puntos']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Segunda Vuelta -->
            <div class="phase-section">
                <h2 class="phase-title">🔄 Segunda Vuelta (VUELTA)</h2>
                <table class="positions-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Equipo</th>
                            <th>PJ</th>
                            <th>PG</th>
                            <th>PP</th>
                            <th>PE</th>
                            <th>PGD</th>
                            <th>PPD</th>
                            <th>PF</th>
                            <th>PC</th>
                            <th>Dif</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($segunda_vuelta as $index => $team): ?>
                        <tr>
                            <td><div class="position"><?php echo $index + 1; ?></div></td>
                            <td class="team-name"><?php echo htmlspecialchars($team['equipo']); ?></td>
                            <td><?php echo $team['stats']['juegos_jugados']; ?></td>
                            <td><?php echo $team['stats']['juegos_ganados']; ?></td>
                            <td><?php echo $team['stats']['juegos_perdidos']; ?></td>
                            <td><?php echo $team['stats']['juegos_empatados']; ?></td>
                            <td><?php echo $team['stats']['juegos_ganados_default']; ?></td>
                            <td><?php echo $team['stats']['juegos_perdidos_default']; ?></td>
                            <td><?php echo $team['stats']['goles_favor']; ?></td>
                            <td><?php echo $team['stats']['goles_contra']; ?></td>
                            <td style="color: <?php echo $team['stats']['diferencia_goles'] >= 0 ? 'green' : 'red'; ?>"><?php echo ($team['stats']['diferencia_goles'] >= 0 ? '+' : '') . $team['stats']['diferencia_goles']; ?></td>
                            <td><strong><?php echo $team['stats']['puntos']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tabla General -->
        <div class="general-table-container">
            <h2 class="phase-title" style="font-size: 2.2em; color: #667eea; text-align: center; margin-bottom: 25px;">
                🏆 TABLA GENERAL (ACUMULADA)
            </h2>
            <table class="positions-table">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Equipo</th>
                        <th>PJ</th>
                        <th>PG</th>
                        <th>PP</th>
                        <th>PE</th>
                        <th>PGD</th>
                        <th>PPD</th>
                        <th>PF</th>
                        <th>PC</th>
                        <th>Dif</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tabla_general as $index => $team): ?>
                    <tr style="<?php echo $index < 4 ? 'background: linear-gradient(90deg, #e8f5e8, #f0f8f0);' : ''; ?>">
                        <td>
                            <div class="position" style="<?php echo $index < 4 ? 'background: linear-gradient(135deg, #28a745, #20c997);' : ''; ?>">
                                <?php echo $index + 1; ?>
                            </div>
                        </td>
                        <td class="team-name" style="<?php echo $index < 4 ? 'font-weight: bold; color: #155724;' : ''; ?>">
                            <?php echo htmlspecialchars($team['equipo']); ?>
                            <?php if ($index < 4): ?>
                                <span style="color: #28a745; font-size: 12px;"> ★</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $team['stats']['juegos_jugados']; ?></td>
                        <td><?php echo $team['stats']['juegos_ganados']; ?></td>
                        <td><?php echo $team['stats']['juegos_perdidos']; ?></td>
                        <td><?php echo $team['stats']['juegos_empatados']; ?></td>
                        <td><?php echo $team['stats']['juegos_ganados_default']; ?></td>
                        <td><?php echo $team['stats']['juegos_perdidos_default']; ?></td>
                        <td><?php echo $team['stats']['goles_favor']; ?></td>
                        <td><?php echo $team['stats']['goles_contra']; ?></td>
                        <td style="color: <?php echo $team['stats']['diferencia_goles'] >= 0 ? 'green' : 'red'; ?>; font-weight: bold;">
                            <?php echo ($team['stats']['diferencia_goles'] >= 0 ? '+' : '') . $team['stats']['diferencia_goles']; ?>
                        </td>
                        <td><strong style="font-size: 16px; color: #2c3e50;"><?php echo $team['stats']['puntos']; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="legend">
            <h4>📋 Leyenda</h4>
            <span class="legend-item"><strong>PJ:</strong> Partidos Jugados</span>
            <span class="legend-item"><strong>PG:</strong> Partidos Ganados</span>
            <span class="legend-item"><strong>PP:</strong> Partidos Perdidos</span>
            <span class="legend-item"><strong>PE:</strong> Partidos Empatados</span>
            <span class="legend-item"><strong>PGD:</strong> Partidos Ganados por Default</span>
            <span class="legend-item"><strong>PPD:</strong> Partidos Perdidos por Default</span><br>
            <span class="legend-item"><strong>PF:</strong> Puntos a Favor</span>
            <span class="legend-item"><strong>PC:</strong> Puntos en Contra</span>
            <span class="legend-item"><strong>Dif:</strong> Diferencia de Puntos</span>
            <span class="legend-item"><strong>Pts:</strong> Puntos de Liga</span>
            <span class="legend-item"><strong>★:</strong> Clasificado a Playoffs</span>
        </div>
        
        <div class="stats-summary">
            <p><strong>🏆 Sistema de Puntuación:</strong> 
                Ganado: 2 pts | Empate: 0 pts | Perdido: 1 pt | 
                Ganado por Default: 2 pts | Perdido por Default: 0 pts
            </p>
            <p style="color: #7f8c8d; margin-top: 10px;">
                <em>Última actualización: <?php echo date('d/m/Y H:i:s'); ?></em>
            </p>
        </div>
    </div>
    
    <script src="metro.js"></script>
    <script>
        // Animación de entrada para las tablas
        document.addEventListener('DOMContentLoaded', function() {
            const tables = document.querySelectorAll('.phase-section');
            tables.forEach((table, index) => {
                table.style.opacity = '0';
                table.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    table.style.transition = 'all 0.6s ease';
                    table.style.opacity = '1';
                    table.style.transform = 'translateY(0)';
                }, index * 200);
            });
            
            // Efecto hover mejorado para las filas
            const rows = document.querySelectorAll('.positions-table tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
        
        // Actualización automática cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
