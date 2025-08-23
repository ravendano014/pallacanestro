<?php
require_once 'Connections/Connection.php';

// Manejar las solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createTeam();
            break;
        case 'read':
            readTeams();
            break;
        case 'update':
            updateTeam();
            break;
        case 'delete':
            deleteTeam();
            break;
        case 'get':
            getTeam();
            break;
    }
    exit;
}

function createTeam() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO Teams (created_by_player_id, team_name, date_created, date_disbanded, other_details) VALUES (?, ?, ?, ?, ?)");
        
        $created_by_player_id = $_POST['created_by_player_id'];
        $team_name = strtoupper(trim($_POST['team_name']));
        $date_created = $_POST['date_created'];
        $date_disbanded = !empty($_POST['date_disbanded']) ? $_POST['date_disbanded'] : null;
        $other_details = !empty($_POST['other_details']) ? trim($_POST['other_details']) : null;
        
        // Verificar si ya existe un equipo con ese nombre
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Teams WHERE team_name = ?");
        $checkStmt->execute([$team_name]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un equipo con ese nombre']);
            return;
        }
        
        $stmt->execute([$created_by_player_id, $team_name, $date_created, $date_disbanded, $other_details]);
        
        echo json_encode(['success' => true, 'message' => 'Equipo creado correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear equipo: ' . $e->getMessage()]);
    }
}

function readTeams() {
    global $pdo;
    
    try {
        $search = $_POST['search'] ?? '';
        
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT * FROM Teams WHERE team_name LIKE ? OR team_id LIKE ? OR created_by_player_id LIKE ? ORDER BY team_id");
            $searchTerm = "%$search%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        } else {
            $stmt = $pdo->query("SELECT * FROM Teams ORDER BY team_id");
        }
        
        $teams = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $teams]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener equipos: ' . $e->getMessage()]);
    }
}

function getTeam() {
    global $pdo;
    
    try {
        $team_id = $_POST['team_id'];
        $stmt = $pdo->prepare("SELECT * FROM Teams WHERE team_id = ?");
        $stmt->execute([$team_id]);
        
        $team = $stmt->fetch();
        
        if ($team) {
            echo json_encode(['success' => true, 'data' => $team]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener equipo: ' . $e->getMessage()]);
    }
}

function updateTeam() {
    global $pdo;
    
    try {
        $team_id = $_POST['team_id'];
        $created_by_player_id = $_POST['created_by_player_id'];
        $team_name = strtoupper(trim($_POST['team_name']));
        $date_created = $_POST['date_created'];
        $date_disbanded = !empty($_POST['date_disbanded']) ? $_POST['date_disbanded'] : null;
        $other_details = !empty($_POST['other_details']) ? trim($_POST['other_details']) : null;
        
        // Verificar si ya existe otro equipo con ese nombre
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Teams WHERE team_name = ? AND team_id != ?");
        $checkStmt->execute([$team_name, $team_id]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro equipo con ese nombre']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE Teams SET created_by_player_id = ?, team_name = ?, date_created = ?, date_disbanded = ?, other_details = ? WHERE team_id = ?");
        $stmt->execute([$created_by_player_id, $team_name, $date_created, $date_disbanded, $other_details, $team_id]);
        
        echo json_encode(['success' => true, 'message' => 'Equipo actualizado correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar equipo: ' . $e->getMessage()]);
    }
}

function deleteTeam() {
    global $pdo;
    
    try {
        $team_id = $_POST['team_id'];
        
        $stmt = $pdo->prepare("DELETE FROM Teams WHERE team_id = ?");
        $stmt->execute([$team_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Equipo eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar equipo: ' . $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Teams - Metro UI PHP</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container {
            padding: 20px;
            max-width: 1200px;
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
            max-height: 500px;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .table th {
            background-color: #f6f8fa;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .table tbody tr:hover {
            background-color: #f6f8fa;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #0366d6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0256c4;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-light {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
        }
        
        .btn-light:hover {
            background-color: #e2e6ea;
        }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 12px;
        }
        
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
            max-width: 500px;
            width: 90%;
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
        
        .text-center {
            text-align: center;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .modal-content {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Gestión de Equipos (Teams)</h1>
        
        <!-- Status Message -->
        <div id="statusMessage" class="status-message"></div>
        
        <!-- Loading -->
        <div id="loading" class="loading">
            <p>Cargando...</p>
        </div>
        
        <!-- Create/Edit Form Section -->
        <div class="crud-section">
            <h3 id="formTitle">Crear Nuevo Equipo</h3>
            <form id="teamForm">
                <input type="hidden" id="teamId" name="team_id">
                
                <div class="form-group">
                    <label for="createdByPlayerId">ID del Jugador Creador:</label>
                    <input type="number" id="createdByPlayerId" name="created_by_player_id" class="form-control" required min="1">
                </div>
                
                <div class="form-group">
                    <label for="teamName">Nombre del Equipo:</label>
                    <input type="text" id="teamName" name="team_name" class="form-control" required maxlength="100" placeholder="Ingrese el nombre del equipo">
                </div>
                
                <div class="form-group">
                    <label for="dateCreated">Fecha de Creación:</label>
                    <input type="date" id="dateCreated" name="date_created" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="dateDisbanded">Fecha de Disolución (opcional):</label>
                    <input type="date" id="dateDisbanded" name="date_disbanded" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="otherDetails">Otros Detalles:</label>
                    <textarea id="otherDetails" name="other_details" class="form-control" rows="3" placeholder="Información adicional sobre el equipo"></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Crear Equipo</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;">Cancelar</button>
                </div>
            </form>
        </div>
        
        <!-- Teams List Section -->
        <div class="crud-section">
            <h3>Lista de Equipos</h3>
            <div class="btn-group">
                <button onclick="loadTeams()" class="btn btn-info">Actualizar Lista</button>
                <button onclick="clearFilters()" class="btn btn-light">Limpiar Filtros</button>
            </div>
            
            <!-- Search/Filter -->
            <div class="form-group">
                <input type="text" id="searchInput" placeholder="Buscar por nombre, ID de equipo o ID de creador..." class="form-control">
            </div>
            
            <div class="table-container">
                <table class="table" id="teamsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Creador (Player ID)</th>
                            <th>Nombre del Equipo</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Disolución</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="teamsTableBody">
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
            <p>¿Está seguro de que desea eliminar el equipo "<span id="deleteTeamName"></span>"?</p>
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
        let editingTeamId = null;
        let teamToDelete = null;
        let searchTimeout = null;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadTeams();
            setupEventListeners();
            
            // Set today's date as default
            document.getElementById('dateCreated').valueAsDate = new Date();
        });
        
        function setupEventListeners() {
            // Form submission
            document.getElementById('teamForm').addEventListener('submit', handleSubmit);
            
            // Search with debounce
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadTeams(this.value);
                }, 300);
            });
            
            // Close modal when clicking outside
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
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
        
        // Load teams
        function loadTeams(search = '') {
            makeRequest({
                action: 'read',
                search: search
            }, function(response) {
                if (response.success) {
                    displayTeams(response.data);
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Display teams in table
        function displayTeams(teams) {
            const tbody = document.getElementById('teamsTableBody');
            tbody.innerHTML = '';
            
            if (teams.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron equipos</td></tr>';
                return;
            }
            
            teams.forEach(team => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${team.team_id}</td>
                    <td>${team.created_by_player_id}</td>
                    <td>${team.team_name}</td>
                    <td>${team.date_created}</td>
                    <td>${team.date_disbanded || 'N/A'}</td>
                    <td>${team.other_details || 'N/A'}</td>
                    <td>
                        <button onclick="editTeam(${team.team_id})" class="btn btn-info btn-small">Editar</button>
                        <button onclick="deleteTeam(${team.team_id}, '${team.team_name}')" class="btn btn-danger btn-small">Eliminar</button>
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
            if (!data.team_name.trim()) {
                showStatus('El nombre del equipo es requerido', 'error');
                return;
            }
            
            if (data.date_disbanded && data.date_disbanded <= data.date_created) {
                showStatus('La fecha de disolución debe ser posterior a la fecha de creación', 'error');
                return;
            }
            
            if (editingTeamId) {
                data.action = 'update';
                data.team_id = editingTeamId;
            } else {
                data.action = 'create';
            }
            
            makeRequest(data, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadTeams();
                    resetForm();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Edit team
        function editTeam(id) {
            makeRequest({
                action: 'get',
                team_id: id
            }, function(response) {
                if (response.success) {
                    const team = response.data;
                    
                    editingTeamId = id;
                    
                    // Fill form with team data
                    document.getElementById('teamId').value = team.team_id;
                    document.getElementById('createdByPlayerId').value = team.created_by_player_id;
                    document.getElementById('teamName').value = team.team_name;
                    document.getElementById('dateCreated').value = team.date_created;
                    document.getElementById('dateDisbanded').value = team.date_disbanded || '';
                    document.getElementById('otherDetails').value = team.other_details || '';
                    
                    // Update form title and button
                    document.getElementById('formTitle').textContent = 'Editar Equipo';
                    document.getElementById('submitBtn').textContent = 'Actualizar Equipo';
                    document.getElementById('cancelBtn').style.display = 'inline-block';
                    
                    // Scroll to form
                    document.querySelector('.crud-section').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Delete team
        function deleteTeam(id, name) {
            teamToDelete = id;
            document.getElementById('deleteTeamName').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        // Confirm delete
        function confirmDelete() {
            if (!teamToDelete) return;
            
            makeRequest({
                action: 'delete',
                team_id: teamToDelete
            }, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadTeams();
                    closeDeleteModal();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            teamToDelete = null;
        }
        
        // Cancel edit
        function cancelEdit() {
            resetForm();
        }
        
        // Reset form
        function resetForm() {
            document.getElementById('teamForm').reset();
            document.getElementById('dateCreated').valueAsDate = new Date();
            editingTeamId = null;
            
            document.getElementById('formTitle').textContent = 'Crear Nuevo Equipo';
            document.getElementById('submitBtn').textContent = 'Crear Equipo';
            document.getElementById('cancelBtn').style.display = 'none';
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            loadTeams();
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
