<?php
require_once 'Connections/Connection.php';

// Función para obtener estadísticas por torneo
function getStatsPerTournament() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            t.name as tournament_name,
            COUNT(tm.match_id) as total_matches,
            SUM(CASE WHEN tm.status = 'SCHEDULED' THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN tm.status = 'FINISHED' THEN 1 ELSE 0 END) as finished,
            SUM(CASE WHEN tm.status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN tm.status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled,
            AVG(tm.home_score + tm.away_score) as avg_total_points
        FROM Team_Matches tm
        LEFT JOIN Tournaments t ON tm.tournament_id = t.tournament_id
        GROUP BY tm.tournament_id, t.name
        ORDER BY total_matches DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener estadísticas por equipo
function getStatsPerTeam() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            teams.team_name,
            COUNT(*) as total_matches,
            SUM(CASE 
                WHEN (tm.home_team_id = teams.team_id AND tm.home_score > tm.away_score) 
                     OR (tm.away_team_id = teams.team_id AND tm.away_score > tm.home_score) 
                THEN 1 ELSE 0 END) as wins,
            SUM(CASE 
                WHEN (tm.home_team_id = teams.team_id AND tm.home_score < tm.away_score) 
                     OR (tm.away_team_id = teams.team_id AND tm.away_score < tm.home_score) 
                THEN 1 ELSE 0 END) as losses,
            SUM(CASE 
                WHEN tm.home_score = tm.away_score AND tm.home_score IS NOT NULL 
                THEN 1 ELSE 0 END) as draws,
            AVG(CASE 
                WHEN tm.home_team_id = teams.team_id THEN tm.home_score
                WHEN tm.away_team_id = teams.team_id THEN tm.away_score
                END) as avg_points_for,
            AVG(CASE 
                WHEN tm.home_team_id = teams.team_id THEN tm.away_score
                WHEN tm.away_team_id = teams.team_id THEN tm.home_score
                END) as avg_points_against
        FROM Teams teams
        LEFT JOIN Team_Matches tm ON (teams.team_id = tm.home_team_id OR teams.team_id = tm.away_team_id)
        WHERE tm.status = 'FINISHED' AND tm.home_score IS NOT NULL AND tm.away_score IS NOT NULL
        GROUP BY teams.team_id, teams.team_name
        ORDER BY wins DESC, teams.team_name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener próximos partidos
function getUpcomingMatches($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT tm.*, 
               t1.team_name as home_team_name, 
               t2.team_name as away_team_name,
               tournaments.name as tournament_name,
               bc.court_name
        FROM Team_Matches tm
        LEFT JOIN Teams t1 ON tm.home_team_id = t1.team_id
        LEFT JOIN Teams t2 ON tm.away_team_id = t2.team_id
        LEFT JOIN Tournaments tournaments ON tm.tournament_id = tournaments.tournament_id
        LEFT JOIN Basketball_Courts bc ON tm.court_id = bc.court_id
        WHERE tm.status = 'SCHEDULED' 
        AND (tm.start_datetime IS NULL OR tm.start_datetime >= NOW())
        ORDER BY tm.start_datetime ASC, tm.match_id ASC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Función para obtener resultados recientes
function getRecentResults($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT tm.*, 
               t1.team_name as home_team_name, 
               t2.team_name as away_team_name,
               tournaments.name as tournament_name,
               bc.court_name
        FROM Team_Matches tm
        LEFT JOIN Teams t1 ON tm.home_team_id = t1.team_id
        LEFT JOIN Teams t2 ON tm.away_team_id = t2.team_id
        LEFT JOIN Tournaments tournaments ON tm.tournament_id = tournaments.tournament_id
        LEFT JOIN Basketball_Courts bc ON tm.court_id = bc.court_id
        WHERE tm.status = 'FINISHED' 
        AND tm.home_score IS NOT NULL AND tm.away_score IS NOT NULL
        ORDER BY tm.start_datetime DESC, tm.match_id DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Obtener datos para los reportes
$tournamentStats = getStatsPerTournament();
$teamStats = getStatsPerTeam();
$upcomingMatches = getUpcomingMatches(5);
$recentResults = getRecentResults(5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - Team Matches</title>
    <link rel="stylesheet" href="metro.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .report-card h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.2em;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            font-weight: 500;
        }
        
        .stat-value {
            font-weight: bold;
            color: #3498db;
        }
        
        .match-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        
        .match-teams {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .match-details {
            font-size: 0.9em;
            color: #666;
        }
        
        .score {
            font-size: 1.1em;
            font-weight: bold;
            color: #27ae60;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .progress-bar {
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            height: 20px;
            margin: 5px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: width 0.3s ease;
        }
        
        .team-ranking {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .ranking-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
            margin-right: 15px;
            min-width: 30px;
        }
        
        .team-info {
            flex: 1;
        }
        
        .team-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .team-stats {
            font-size: 0.9em;
            color: #666;
        }
        
        .win-rate {
            font-weight: bold;
            color: #27ae60;
        }
        
        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 20px;
        }
        
        .nav-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            background: transparent;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nav-tab.active {
            background: white;
            color: #3498db;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">📊 Reportes y Estadísticas</h1>
        
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('overview')">📈 General</button>
            <button class="nav-tab" onclick="switchTab('tournaments')">🏆 Torneos</button>
            <button class="nav-tab" onclick="switchTab('teams')">👥 Equipos</button>
            <button class="nav-tab" onclick="switchTab('matches')">⚽ Partidos</button>
        </div>

        <!-- Tab: General Overview -->
        <div id="overview" class="tab-content active">
            <div class="dashboard-grid">
                <div class="report-card">
                    <h3>🎯 Próximos Partidos</h3>
                    <?php if (empty($upcomingMatches)): ?>
                        <p>No hay partidos programados próximamente.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingMatches as $match): ?>
                            <div class="match-item">
                                <div class="match-teams">
                                    <?php if ($match['is_bye'] == 1): ?>
                                        <span class="tag warning">BYE</span> 
                                        <?php echo htmlspecialchars($match['home_team_name'] ?? 'TBD'); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($match['home_team_name'] ?? 'TBD'); ?> 
                                        <strong>vs</strong> 
                                        <?php echo htmlspecialchars($match['away_team_name'] ?? 'TBD'); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="match-details">
                                    📅 <?php echo $match['start_datetime'] ? date('d/m/Y H:i', strtotime($match['start_datetime'])) : 'Fecha por definir'; ?><br>
                                    🏟️ <?php echo htmlspecialchars($match['court_name'] ?? 'Cancha por definir'); ?><br>
                                    🏆 <?php echo htmlspecialchars($match['tournament_name']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="report-card">
                    <h3>🏁 Resultados Recientes</h3>
                    <?php if (empty($recentResults)): ?>
                        <p>No hay resultados recientes disponibles.</p>
                    <?php else: ?>
                        <?php foreach ($recentResults as $match): ?>
                            <div class="match-item">
                                <div class="match-teams">
                                    <?php echo htmlspecialchars($match['home_team_name'] ?? 'TBD'); ?> 
                                    <strong>vs</strong> 
                                    <?php echo htmlspecialchars($match['away_team_name'] ?? 'TBD'); ?>
                                </div>
                                <div class="score">
                                    <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                    <?php if ($match['walkover_winner']): ?>
                                        <span class="tag alert">W.O.</span>
                                    <?php endif; ?>
                                </div>
                                <div class="match-details">
                                    📅 <?php echo date('d/m/Y H:i', strtotime($match['start_datetime'])); ?><br>
                                    🏆 <?php echo htmlspecialchars($match['tournament_name']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Tournaments -->
        <div id="tournaments" class="tab-content">
            <div class="chart-container">
                <h3>📊 Estadísticas por Torneo</h3>
                <?php if (empty($tournamentStats)): ?>
                    <p>No hay datos de torneos disponibles.</p>
                <?php else: ?>
                    <?php foreach ($tournamentStats as $stat): ?>
                        <div class="stat-item">
                            <div>
                                <strong><?php echo htmlspecialchars($stat['tournament_name']); ?></strong>
                                <div class="match-details">
                                    Total: <?php echo $stat['total_matches']; ?> partidos | 
                                    Promedio puntos: <?php echo round($stat['avg_total_points'] ?? 0, 1); ?>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($stat['finished'] / max($stat['total_matches'], 1)) * 100; ?>%"></div>
                                </div>
                            </div>
                            <div class="stat-value">
                                <?php echo round(($stat['finished'] / max($stat['total_matches'], 1)) * 100, 1); ?>% completado
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Teams -->
        <div id="teams" class="tab-content">
            <div class="chart-container">
                <h3>🏅 Ranking de Equipos</h3>
                <?php if (empty($teamStats)): ?>
                    <p>No hay estadísticas de equipos disponibles.</p>
                <?php else: ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($teamStats as $stat): ?>
                        <?php 
                        $totalMatches = $stat['total_matches'];
                        $winRate = $totalMatches > 0 ? ($stat['wins'] / $totalMatches) * 100 : 0;
                        ?>
                        <div class="team-ranking">
                            <div class="ranking-number"><?php echo $rank++; ?></div>
                            <div class="team-info">
                                <div class="team-name"><?php echo htmlspecialchars($stat['team_name']); ?></div>
                                <div class="team-stats">
                                    Partidos: <?php echo $stat['total_matches']; ?> | 
                                    G: <?php echo $stat['wins']; ?> | 
                                    P: <?php echo $stat['losses']; ?> | 
                                    E: <?php echo $stat['draws']; ?> | 
                                    <span class="win-rate"><?php echo round($winRate, 1); ?>%</span>
                                </div>
                                <div class="team-stats">
                                    Promedio anotado: <?php echo round($stat['avg_points_for'] ?? 0, 1); ?> | 
                                    Promedio recibido: <?php echo round($stat['avg_points_against'] ?? 0, 1); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Matches -->
        <div id="matches" class="tab-content">
            <div class="dashboard-grid">
                <div class="report-card">
                    <h3>📈 Estados de Partidos</h3>
                    <?php 
                    $totalMatches = array_sum(array_column($tournamentStats, 'total_matches'));
                    $scheduledMatches = array_sum(array_column($tournamentStats, 'scheduled'));
                    $finishedMatches = array_sum(array_column($tournamentStats, 'finished'));
                    $inProgressMatches = array_sum(array_column($tournamentStats, 'in_progress'));
                    $cancelledMatches = array_sum(array_column($tournamentStats, 'cancelled'));
                    ?>
                    
                    <div class="stat-item">
                        <div class="stat-label">Total de Partidos</div>
                        <div class="stat-value"><?php echo $totalMatches; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Programados</div>
                        <div class="stat-value"><?php echo $scheduledMatches; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Finalizados</div>
                        <div class="stat-value"><?php echo $finishedMatches; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">En Progreso</div>
                        <div class="stat-value"><?php echo $inProgressMatches; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Cancelados</div>
                        <div class="stat-value"><?php echo $cancelledMatches; ?></div>
                    </div>
                </div>

                <div class="report-card">
                    <h3>⏰ Progreso General</h3>
                    <?php if ($totalMatches > 0): ?>
                        <div class="stat-item">
                            <div class="stat-label">Completado</div>
                            <div class="stat-value"><?php echo round(($finishedMatches / $totalMatches) * 100, 1); ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($finishedMatches / $totalMatches) * 100; ?>%"></div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-label">En espera</div>
                            <div class="stat-value"><?php echo round(($scheduledMatches / $totalMatches) * 100, 1); ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($scheduledMatches / $totalMatches) * 100; ?>%; background: #f39c12;"></div>
                        </div>
                    <?php else: ?>
                        <p>No hay datos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center" style="margin-top: 30px;">
            <a href="team_matches_crud_advanced.php" class="button primary">← Volver al CRUD</a>
            <button class="button success" onclick="window.print()">🖨️ Imprimir Reporte</button>
        </div>
    </div>

    <script src="metro.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Metro.init();
        });

        function switchTab(tabName) {
            // Ocultar todos los contenidos
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => {
                content.classList.remove('active');
            });

            // Desactivar todas las pestañas
            const tabs = document.querySelectorAll('.nav-tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostrar el contenido seleccionado
            document.getElementById(tabName).classList.add('active');

            // Activar la pestaña seleccionada
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
