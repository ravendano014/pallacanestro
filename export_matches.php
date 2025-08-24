<?php
require_once 'Connections/Connection.php';

// Función para obtener team matches con filtros
function getMatchesForExport($filters = []) {
    global $pdo;
    $where = "WHERE 1=1";
    $params = [];
    
    if (!empty($filters['tournament_id'])) {
        $where .= " AND tm.tournament_id = ?";
        $params[] = $filters['tournament_id'];
    }
    
    if (!empty($filters['status'])) {
        $where .= " AND tm.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['date_from'])) {
        $where .= " AND DATE(tm.start_datetime) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where .= " AND DATE(tm.start_datetime) <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $where .= " AND (t1.team_name LIKE ? OR t2.team_name LIKE ? OR tournaments.name LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql = "
        SELECT tm.match_id,
               tournaments.name as tournament_name,
               tm.jornada,
               tm.juego,
               tm.phase,
               tm.start_datetime,
               t1.team_name as home_team_name, 
               t2.team_name as away_team_name,
               tm.home_score,
               tm.away_score,
               bc.court_name,
               tm.status,
               tm.is_bye,
               tm.walkover_winner,
               tm.notes
        FROM Team_Matches tm
        LEFT JOIN Teams t1 ON tm.home_team_id = t1.team_id
        LEFT JOIN Teams t2 ON tm.away_team_id = t2.team_id
        LEFT JOIN Tournaments tournaments ON tm.tournament_id = tournaments.tournament_id
        LEFT JOIN Basketball_Courts bc ON tm.court_id = bc.court_id
        $where
        ORDER BY tm.start_datetime DESC, tm.match_id DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Procesar filtros
$filters = [
    'tournament_id' => $_GET['tournament_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$format = $_GET['format'] ?? 'csv';
$matches = getMatchesForExport($filters);

switch ($format) {
    case 'csv':
        exportCSV($matches);
        break;
    case 'excel':
        exportExcel($matches);
        break;
    case 'pdf':
        exportPDF($matches);
        break;
    default:
        echo "Formato no válido";
        break;
}

function exportCSV($matches) {
    $filename = 'partidos_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fputs($output, "\xEF\xBB\xBF");
    
    // Headers
    fputcsv($output, [
        'ID',
        'Torneo',
        'Jornada',
        'Juego',
        'Fase',
        'Fecha/Hora',
        'Equipo Local',
        'Equipo Visitante',
        'Puntos Local',
        'Puntos Visitante',
        'Cancha',
        'Estado',
        'Es Bye',
        'Walkover',
        'Notas'
    ]);
    
    // Data
    foreach ($matches as $match) {
        fputcsv($output, [
            $match['match_id'],
            $match['tournament_name'],
            $match['jornada'],
            $match['juego'],
            $match['phase'],
            $match['start_datetime'] ? date('d/m/Y H:i', strtotime($match['start_datetime'])) : '',
            $match['home_team_name'] ?? 'TBD',
            $match['away_team_name'] ?? 'TBD',
            $match['home_score'] ?? '',
            $match['away_score'] ?? '',
            $match['court_name'] ?? '',
            getStatusLabel($match['status']),
            $match['is_bye'] == 1 ? 'Sí' : 'No',
            getWalkoverLabel($match['walkover_winner']),
            $match['notes'] ?? ''
        ]);
    }
    
    fclose($output);
}

function exportExcel($matches) {
    // Para Excel necesitarías una librería como PhpSpreadsheet
    // Por ahora exportamos como CSV con extensión Excel
    $filename = 'partidos_' . date('Y-m-d_H-i-s') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "\xEF\xBB\xBF"; // BOM for UTF-8
    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Torneo</th>";
    echo "<th>Jornada</th>";
    echo "<th>Juego</th>";
    echo "<th>Fase</th>";
    echo "<th>Fecha/Hora</th>";
    echo "<th>Equipo Local</th>";
    echo "<th>Equipo Visitante</th>";
    echo "<th>Puntos Local</th>";
    echo "<th>Puntos Visitante</th>";
    echo "<th>Cancha</th>";
    echo "<th>Estado</th>";
    echo "<th>Es Bye</th>";
    echo "<th>Walkover</th>";
    echo "<th>Notas</th>";
    echo "</tr>";
    
    foreach ($matches as $match) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($match['match_id']) . "</td>";
        echo "<td>" . htmlspecialchars($match['tournament_name']) . "</td>";
        echo "<td>" . htmlspecialchars($match['jornada']) . "</td>";
        echo "<td>" . htmlspecialchars($match['juego']) . "</td>";
        echo "<td>" . htmlspecialchars($match['phase']) . "</td>";
        echo "<td>" . ($match['start_datetime'] ? date('d/m/Y H:i', strtotime($match['start_datetime'])) : '') . "</td>";
        echo "<td>" . htmlspecialchars($match['home_team_name'] ?? 'TBD') . "</td>";
        echo "<td>" . htmlspecialchars($match['away_team_name'] ?? 'TBD') . "</td>";
        echo "<td>" . htmlspecialchars($match['home_score'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($match['away_score'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($match['court_name'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars(getStatusLabel($match['status'])) . "</td>";
        echo "<td>" . ($match['is_bye'] == 1 ? 'Sí' : 'No') . "</td>";
        echo "<td>" . htmlspecialchars(getWalkoverLabel($match['walkover_winner'])) . "</td>";
        echo "<td>" . htmlspecialchars($match['notes'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

function exportPDF($matches) {
    // Para PDF necesitarías una librería como TCPDF o FPDF
    // Por ahora mostramos un mensaje
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Exportación PDF</title>
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
            body { font-family: Arial, sans-serif; padding: 20px; }
            .message { background: #f0f0f0; padding: 20px; border-radius: 5px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='message'>
            <h2>🚧 Exportación PDF en desarrollo</h2>
            <p>La funcionalidad de exportación a PDF requiere la instalación de librerías adicionales como TCPDF o FPDF.</p>
            <p>Por ahora, puede usar la exportación a CSV o Excel.</p>
            <button onclick='history.back()'>Volver</button>
        </div>
    </body>
    </html>
    ";
}

function getStatusLabel($status) {
    $statusLabels = [
        'SCHEDULED' => 'Programado',
        'IN_PROGRESS' => 'En Progreso',
        'FINISHED' => 'Finalizado',
        'CANCELLED' => 'Cancelado',
        'POSTPONED' => 'Pospuesto'
    ];
    return $statusLabels[$status] ?? $status;
}

function getWalkoverLabel($walkover) {
    if (!$walkover) return '';
    return $walkover == 'HOME' ? 'Local' : 'Visitante';
}
?>
