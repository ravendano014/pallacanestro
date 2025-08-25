<?php
require_once 'Connections/Connection.php';

// Manejar las solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createMatch();
            break;
        case 'read':
            readMatches();
            break;
        case 'update':
            updateMatch();
            break;
        case 'delete':
            deleteMatch();
            break;
        case 'get':
            getMatch();
            break;
        case 'get_teams':
            getTeams();
            break;
        case 'get_tournaments':
            getTournaments();
            break;
        case 'get_courts':
            getCourts();
            break;
    }
    exit;
}

function createMatch() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO Team_Matches (tournament_id, jornada, juego, phase, start_datetime, 
                                    home_team_id, away_team_id, home_score, away_score, 
                                    is_bye, bye_team_id, court_id, status, walkover_winner, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $tournament_id = $_POST['tournament_id'];
        $jornada = $_POST['jornada'];
        $juego = $_POST['juego'];
        $phase = $_POST['phase'];
        $start_datetime = !empty($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
        $home_team_id = $_POST['home_team_id'];
        $away_team_id = $_POST['away_team_id'];
        $home_score = !empty($_POST['home_score']) ? $_POST['home_score'] : null;
        $away_score = !empty($_POST['away_score']) ? $_POST['away_score'] : null;
        $is_bye = $_POST['is_bye'] ?? 0;
        $bye_team_id = !empty($_POST['bye_team_id']) ? $_POST['bye_team_id'] : null;
        $court_id = $_POST['court_id'];
        $status = $_POST['status'];
        $walkover_winner = !empty($_POST['walkover_winner']) ? $_POST['walkover_winner'] : null;
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        
        // Validaciones
        if ($home_team_id == $away_team_id && $is_bye == 0) {
            echo json_encode(['success' => false, 'message' => 'Los equipos local y visitante no pueden ser iguales']);
            return;
        }
        
        if ($home_score !== null && $away_score !== null && $home_score == $away_score) {
            echo json_encode(['success' => false, 'message' => 'No puede haber empates en baloncesto']);
            return;
        }
        
        $stmt->execute([
            $tournament_id, $jornada, $juego, $phase, $start_datetime,
            $home_team_id, $away_team_id, $home_score, $away_score,
            $is_bye, $bye_team_id, $court_id, $status, $walkover_winner, $notes
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Partido creado correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear partido: ' . $e->getMessage()]);
    }
}

function readMatches() {
    global $pdo;
    
    try {
        $search = $_POST['search'] ?? '';
        $filter_tournament = $_POST['filter_tournament'] ?? '';
        $filter_status = $_POST['filter_status'] ?? '';
        
        $query = "
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
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (t1.team_name LIKE ? OR t2.team_name LIKE ? OR tournaments.name LIKE ? OR tm.match_id LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($filter_tournament)) {
            $query .= " AND tm.tournament_id = ?";
            $params[] = $filter_tournament;
        }
        
        if (!empty($filter_status)) {
            $query .= " AND tm.status = ?";
            $params[] = $filter_status;
        }
        
        $query .= " ORDER BY tm.match_id DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $matches = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $matches]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener partidos: ' . $e->getMessage()]);
    }
}

function getMatch() {
    global $pdo;
    
    try {
        $match_id = $_POST['match_id'];
        $stmt = $pdo->prepare("SELECT * FROM Team_Matches WHERE match_id = ?");
        $stmt->execute([$match_id]);
        
        $match = $stmt->fetch();
        
        if ($match) {
            echo json_encode(['success' => true, 'data' => $match]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Partido no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener partido: ' . $e->getMessage()]);
    }
}

function updateMatch() {
    global $pdo;
    
    try {
        $match_id = $_POST['match_id'];
        $tournament_id = $_POST['tournament_id'];
        $jornada = $_POST['jornada'];
        $juego = $_POST['juego'];
        $phase = $_POST['phase'];
        $start_datetime = !empty($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
        $home_team_id = $_POST['home_team_id'];
        $away_team_id = $_POST['away_team_id'];
        $home_score = !empty($_POST['home_score']) ? $_POST['home_score'] : null;
        $away_score = !empty($_POST['away_score']) ? $_POST['away_score'] : null;
        $is_bye = $_POST['is_bye'] ?? 0;
        $bye_team_id = !empty($_POST['bye_team_id']) ? $_POST['bye_team_id'] : null;
        $court_id = $_POST['court_id'];
        $status = $_POST['status'];
        $walkover_winner = !empty($_POST['walkover_winner']) ? $_POST['walkover_winner'] : null;
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        
        // Validaciones
        if ($home_team_id == $away_team_id && $is_bye == 0) {
            echo json_encode(['success' => false, 'message' => 'Los equipos local y visitante no pueden ser iguales']);
            return;
        }
        
        if ($home_score !== null && $away_score !== null && $home_score == $away_score) {
            echo json_encode(['success' => false, 'message' => 'No puede haber empates en baloncesto']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE Team_Matches SET 
                tournament_id = ?, jornada = ?, juego = ?, phase = ?, start_datetime = ?,
                home_team_id = ?, away_team_id = ?, home_score = ?, away_score = ?,
                is_bye = ?, bye_team_id = ?, court_id = ?, status = ?, walkover_winner = ?, notes = ?
            WHERE match_id = ?
        ");
        
        $stmt->execute([
            $tournament_id, $jornada, $juego, $phase, $start_datetime,
            $home_team_id, $away_team_id, $home_score, $away_score,
            $is_bye, $bye_team_id, $court_id, $status, $walkover_winner, $notes, $match_id
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Partido actualizado correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar partido: ' . $e->getMessage()]);
    }
}

function deleteMatch() {
    global $pdo;
    
    try {
        $match_id = $_POST['match_id'];
        
        $stmt = $pdo->prepare("DELETE FROM Team_Matches WHERE match_id = ?");
        $stmt->execute([$match_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Partido eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Partido no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar partido: ' . $e->getMessage()]);
    }
}

function getTeams() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT team_id, team_name FROM Teams ORDER BY team_name");
        $stmt->execute();
        $teams = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $teams]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener equipos: ' . $e->getMessage()]);
    }
}

function getTournaments() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT tournament_id, name FROM Tournaments ORDER BY name");
        $stmt->execute();
        $tournaments = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $tournaments]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener torneos: ' . $e->getMessage()]);
    }
}

function getCourts() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT court_id, court_name FROM Basketball_Courts ORDER BY court_name");
        $stmt->execute();
        $courts = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $courts]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener canchas: ' . $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Matches - Metro UI PHP</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .crud-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            background: #fff;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #0366d6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(3, 102, 214, 0.1);
        }
        
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .table th,
        .table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
            font-size: 13px;
        }
        
        .table th {
            background-color: #f6f8fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody tr:hover {
            background-color: #f6f8fa;
        }
        
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s;
            line-height: 1.4;
        }
        
        .btn-primary { background-color: #0366d6; color: white; }
        .btn-primary:hover { background-color: #0256c4; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-secondary:hover { background-color: #545b62; }
        .btn-info { background-color: #17a2b8; color: white; }
        .btn-info:hover { background-color: #138496; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-success:hover { background-color: #218838; }
        .btn-light { background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6; }
        .btn-light:hover { background-color: #e2e6ea; }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 4px;
            max-width: 800px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .status-message {
            padding: 12px;
            margin: 10px 0;
            border-radius: 4px;
            display: none;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .text-center { text-align: center; }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 13px;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-primary { background: #0366d6; color: white; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        
        .score {
            font-weight: bold;
            font-size: 14px;
        }
        
        .filter-section {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
        }
        
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .form-row { grid-template-columns: 1fr; }
            .filter-section { grid-template-columns: 1fr; }
            .table-container { overflow-x: auto; }
            .modal-content { margin: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Gestión de Partidos (Matches)</h1>
        
        <!-- Status Message -->
        <div id="statusMessage" class="status-message"></div>
        
        <!-- Loading -->
        <div id="loading" class="loading">
            <p>Cargando...</p>
        </div>
        
        <!-- Create/Edit Form Section -->
        <div class="crud-section">
            <h3 id="formTitle">Crear Nuevo Partido</h3>
            <form id="matchForm">
                <input type="hidden" id="matchId" name="match_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tournamentId">Torneo:</label>
                        <select id="tournamentId" name="tournament_id" class="form-control" required>
                            <option value="">Seleccionar torneo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="jornada">Jornada:</label>
                        <input type="number" id="jornada" name="jornada" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="juego">Juego:</label>
                        <input type="number" id="juego" name="juego" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="phase">Fase:</label>
                        <select id="phase" name="phase" class="form-control" required>
                            <option value="">Seleccionar fase</option>
                            <option value="IDA">IDA</option>
                            <option value="VUELTA">VUELTA</option>
                            <option value="PLAYOFF">PLAYOFF</option>
                            <option value="FINAL">FINAL</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="homeTeamId">Equipo Local:</label>
                        <select id="homeTeamId" name="home_team_id" class="form-control" required>
                            <option value="">Seleccionar equipo local</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="awayTeamId">Equipo Visitante:</label>
                        <select id="awayTeamId" name="away_team_id" class="form-control" required>
                            <option value="">Seleccionar equipo visitante</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="courtId">Cancha:</label>
                        <select id="courtId" name="court_id" class="form-control" required>
                            <option value="">Seleccionar cancha</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Estado:</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="SCHEDULED">PROGRAMADO</option>
                            <option value="IN_PROGRESS">EN PROGRESO</option>
                            <option value="FINISHED">FINALIZADO</option>
                            <option value="CANCELLED">CANCELADO</option>
                            <option value="POSTPONED">POSPUESTO</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="startDatetime">Fecha y Hora:</label>
                        <input type="datetime-local" id="startDatetime" name="start_datetime" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="homeScore">Puntos Local:</label>
                        <input type="number" id="homeScore" name="home_score" class="form-control" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="awayScore">Puntos Visitante:</label>
                        <input type="number" id="awayScore" name="away_score" class="form-control" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="isBye">Es Bye:</label>
                        <select id="isBye" name="is_bye" class="form-control">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="byeTeamId">Equipo con Bye:</label>
                        <select id="byeTeamId" name="bye_team_id" class="form-control">
                            <option value="">Ninguno</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="walkoverWinner">Ganador por W.O.:</label>
                        <select id="walkoverWinner" name="walkover_winner" class="form-control">
                            <option value="">Ninguno</option>
                            <option value="HOME">Local</option>
                            <option value="AWAY">Visitante</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notas:</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Información adicional sobre el partido"></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Crear Partido</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;">Cancelar</button>
                </div>
            </form>
        </div>
        
        <!-- Matches List Section -->
        <div class="crud-section">
            <h3>Lista de Partidos</h3>
            <div class="btn-group">
                <button onclick="loadMatches()" class="btn btn-info">Actualizar Lista</button>
                <button onclick="clearFilters()" class="btn btn-light">Limpiar Filtros</button>
            </div>
            
            <!-- Search/Filter -->
            <div class="filter-section">
                <div class="form-group">
                    <label for="searchInput">Buscar:</label>
                    <input type="text" id="searchInput" placeholder="Buscar por equipos, torneo o ID..." class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="filterTournament">Torneo:</label>
                    <select id="filterTournament" class="form-control">
                        <option value="">Todos los torneos</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="filterStatus">Estado:</label>
                    <select id="filterStatus" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="SCHEDULED">PROGRAMADO</option>
                        <option value="IN_PROGRESS">EN PROGRESO</option>
                        <option value="FINISHED">FINALIZADO</option>
                        <option value="CANCELLED">CANCELADO</option>
                        <option value="POSTPONED">POSPUESTO</option>
                    </select>
                </div>
                
                <div>
                    <button onclick="applyFilters()" class="btn btn-info">Aplicar Filtros</button>
                </div>
            </div>
            
            <div class="table-container">
                <table class="table" id="matchesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Torneo</th>
                            <th>J.</th>
                            <th>G.</th>
                            <th>Fase</th>
                            <th>Fecha/Hora</th>
                            <th>Partido</th>
                            <th>Resultado</th>
                            <th>Cancha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="matchesTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h4>Confirmar Eliminación</h4>
            <p>¿Está seguro de que desea eliminar este partido?</p>
            <p><strong>Esta acción no se puede deshacer.</strong></p>
            <div class="btn-group">
                <button onclick="confirmDelete()" class="btn btn-danger">Eliminar</button>
                <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/metro.js"></script>
    <script>
        // Global variables
        let editingMatchId = null;
        let matchToDelete = null;
        let searchTimeout = null;
        let teamsData = [];
        let tournamentsData = [];
        let courtsData = [];
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialData();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Form submission
            document.getElementById('matchForm').addEventListener('submit', handleSubmit);
            
            // Search with debounce
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadMatches();
                }, 300);
            });
            
            // Close modal when clicking outside
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
        }
        
        // Load initial data (teams, tournaments, courts)
        function loadInitialData() {
            Promise.all([
                loadTeamsData(),
                loadTournamentsData(),
                loadCourtsData()
            ]).then(() => {
                loadMatches();
            });
        }
        
        // Load teams data
        function loadTeamsData() {
            return new Promise((resolve) => {
                makeRequest({ action: 'get_teams' }, function(response) {
                    if (response.success) {
                        teamsData = response.data;
                        populateTeamSelects();
                    }
                    resolve();
                });
            });
        }
        
        // Load tournaments data
        function loadTournamentsData() {
            return new Promise((resolve) => {
                makeRequest({ action: 'get_tournaments' }, function(response) {
                    if (response.success) {
                        tournamentsData = response.data;
                        populateTournamentSelects();
                    }
                    resolve();
                });
            });
        }
        
        // Load courts data
        function loadCourtsData() {
            return new Promise((resolve) => {
                makeRequest({ action: 'get_courts' }, function(response) {
                    if (response.success) {
                        courtsData = response.data;
                        populateCourtSelects();
                    }
                    resolve();
                });
            });
        }
        
        // Populate team selects
        function populateTeamSelects() {
            const selects = ['homeTeamId', 'awayTeamId', 'byeTeamId'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                const currentValue = select.value;
                
                // Clear options except first one
                while (select.children.length > 1) {
                    select.removeChild(select.lastChild);
                }
                
                teamsData.forEach(team => {
                    const option = document.createElement('option');
                    option.value = team.team_id;
                    option.textContent = team.team_name;
                    select.appendChild(option);
                });
                
                // Restore previous value if exists
                if (currentValue) {
                    select.value = currentValue;
                }
            });
        }
        
        // Populate tournament selects
        function populateTournamentSelects() {
            const selects = ['tournamentId', 'filterTournament'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                const currentValue = select.value;
                
                // Clear options except first one
                while (select.children.length > 1) {
                    select.removeChild(select.lastChild);
                }
                
                tournamentsData.forEach(tournament => {
                    const option = document.createElement('option');
                    option.value = tournament.tournament_id;
                    option.textContent = tournament.name;
                    select.appendChild(option);
                });
                
                // Restore previous value if exists
                if (currentValue) {
                    select.value = currentValue;
                }
            });
        }
        
        // Populate court selects
        function populateCourtSelects() {
            const select = document.getElementById('courtId');
            const currentValue = select.value;
            
            // Clear options except first one
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }
            
            courtsData.forEach(court => {
                const option = document.createElement('option');
                option.value = court.court_id;
                option.textContent = court.court_name;
                select.appendChild(option);
            });
            
            // Restore previous value if exists
            if (currentValue) {
                select.value = currentValue;
            }
        }
        
        // Show loading
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
        
        // Hide loading
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        // AJAX helper function
        function makeRequest(data, callback) {
            showLoading();
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                callback(data);
            })
            .catch(error => {
                hideLoading();
                showStatus('Error de conexión: ' + error.message, 'error');
            });
        }
        
        // Load matches
        function loadMatches() {
            const searchValue = document.getElementById('searchInput').value;
            const tournamentFilter = document.getElementById('filterTournament').value;
            const statusFilter = document.getElementById('filterStatus').value;
            
            makeRequest({
                action: 'read',
                search: searchValue,
                filter_tournament: tournamentFilter,
                filter_status: statusFilter
            }, function(response) {
                if (response.success) {
                    displayMatches(response.data);
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Display matches in table
        function displayMatches(matches) {
            const tbody = document.getElementById('matchesTableBody');
            tbody.innerHTML = '';
            
            if (matches.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron partidos</td></tr>';
                return;
            }
            
            matches.forEach(match => {
                const row = document.createElement('tr');
                
                // Format date
                const dateStr = match.start_datetime ? 
                    new Date(match.start_datetime).toLocaleString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'Por definir';
                
                // Format match display
                let matchDisplay = '';
                if (match.is_bye == 1) {
                    matchDisplay = `<span class="badge badge-warning">BYE</span> ${match.home_team_name || 'TBD'}`;
                } else {
                    matchDisplay = `${match.home_team_name || 'TBD'} <strong>vs</strong> ${match.away_team_name || 'TBD'}`;
                }
                
                // Format score
                let scoreDisplay = '<span style="color: #666;">-</span>';
                if (match.home_score !== null && match.away_score !== null) {
                    scoreDisplay = `<span class="score">${match.home_score} - ${match.away_score}</span>`;
                    if (match.walkover_winner) {
                        scoreDisplay += ' <span class="badge badge-danger">W.O.</span>';
                    }
                }
                
                // Format status badge
                const statusLabels = {
                    'SCHEDULED': 'PROGRAMADO',
                    'IN_PROGRESS': 'EN PROGRESO',
                    'FINISHED': 'FINALIZADO',
                    'CANCELLED': 'CANCELADO',
                    'POSTPONED': 'POSPUESTO'
                };
                
                const statusClasses = {
                    'SCHEDULED': 'badge-primary',
                    'IN_PROGRESS': 'badge-warning',
                    'FINISHED': 'badge-success',
                    'CANCELLED': 'badge-danger',
                    'POSTPONED': 'badge-secondary'
                };
                
                const statusText = statusLabels[match.status] || match.status;
                const statusClass = statusClasses[match.status] || 'badge-secondary';
                
                row.innerHTML = `
                    <td>${match.match_id}</td>
                    <td style="max-width: 120px; overflow: hidden; text-overflow: ellipsis;" title="${match.tournament_name || 'Sin asignar'}">${match.tournament_name || 'N/A'}</td>
                    <td>${match.jornada}</td>
                    <td>${match.juego}</td>
                    <td><span class="badge badge-info">${match.phase}</span></td>
                    <td style="font-size: 11px;">${dateStr}</td>
                    <td style="max-width: 180px;">${matchDisplay}</td>
                    <td>${scoreDisplay}</td>
                    <td style="max-width: 100px; overflow: hidden; text-overflow: ellipsis;" title="${match.court_name || 'Sin asignar'}">${match.court_name || 'N/A'}</td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td>
                        <button onclick="editMatch(${match.match_id})" class="btn btn-info">Editar</button>
                        <button onclick="deleteMatch(${match.match_id})" class="btn btn-danger">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Handle form submission
        function handleSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            // Validation
            if (!data.tournament_id) {
                showStatus('El torneo es requerido', 'error');
                return;
            }
            
            if (!data.home_team_id || !data.away_team_id) {
                showStatus('Los equipos local y visitante son requeridos', 'error');
                return;
            }
            
            if (data.home_team_id === data.away_team_id && data.is_bye == 0) {
                showStatus('Los equipos local y visitante no pueden ser iguales', 'error');
                return;
            }
            
            if (data.home_score && data.away_score && data.home_score === data.away_score) {
                showStatus('No puede haber empates en baloncesto', 'error');
                return;
            }
            
            if (!data.court_id) {
                showStatus('La cancha es requerida', 'error');
                return;
            }
            
            if (editingMatchId) {
                data.action = 'update';
                data.match_id = editingMatchId;
            } else {
                data.action = 'create';
            }
            
            makeRequest(data, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadMatches();
                    resetForm();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Edit match
        function editMatch(id) {
            makeRequest({
                action: 'get',
                match_id: id
            }, function(response) {
                if (response.success) {
                    const match = response.data;
                    
                    editingMatchId = id;
                    
                    // Fill form with match data
                    document.getElementById('matchId').value = match.match_id;
                    document.getElementById('tournamentId').value = match.tournament_id || '';
                    document.getElementById('jornada').value = match.jornada;
                    document.getElementById('juego').value = match.juego;
                    document.getElementById('phase').value = match.phase || '';
                    document.getElementById('homeTeamId').value = match.home_team_id || '';
                    document.getElementById('awayTeamId').value = match.away_team_id || '';
                    document.getElementById('courtId').value = match.court_id || '';
                    document.getElementById('status').value = match.status || '';
                    document.getElementById('homeScore').value = match.home_score || '';
                    document.getElementById('awayScore').value = match.away_score || '';
                    document.getElementById('isBye').value = match.is_bye || '0';
                    document.getElementById('byeTeamId').value = match.bye_team_id || '';
                    document.getElementById('walkoverWinner').value = match.walkover_winner || '';
                    document.getElementById('notes').value = match.notes || '';
                    
                    // Format datetime for input
                    if (match.start_datetime) {
                        const date = new Date(match.start_datetime);
                        const formatted = date.toISOString().slice(0, 16);
                        document.getElementById('startDatetime').value = formatted;
                    }
                    
                    // Update form title and button
                    document.getElementById('formTitle').textContent = 'Editar Partido';
                    document.getElementById('submitBtn').textContent = 'Actualizar Partido';
                    document.getElementById('cancelBtn').style.display = 'inline-block';
                    
                    // Scroll to form
                    document.querySelector('.crud-section').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Delete match
        function deleteMatch(id) {
            matchToDelete = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        // Confirm delete
        function confirmDelete() {
            if (!matchToDelete) return;
            
            makeRequest({
                action: 'delete',
                match_id: matchToDelete
            }, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadMatches();
                    closeDeleteModal();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            matchToDelete = null;
        }
        
        // Cancel edit
        function cancelEdit() {
            resetForm();
        }
        
        // Reset form
        function resetForm() {
            document.getElementById('matchForm').reset();
            editingMatchId = null;
            
            document.getElementById('formTitle').textContent = 'Crear Nuevo Partido';
            document.getElementById('submitBtn').textContent = 'Crear Partido';
            document.getElementById('cancelBtn').style.display = 'none';
        }
        
        // Apply filters
        function applyFilters() {
            loadMatches();
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterTournament').value = '';
            document.getElementById('filterStatus').value = '';
            loadMatches();
        }
        
        // Show status message
        function showStatus(message, type) {
            const statusEl = document.getElementById('statusMessage');
            statusEl.className = `status-message status-${type}`;
            statusEl.textContent = message;
            statusEl.style.display = 'block';
            
            setTimeout(() => {
                statusEl.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>