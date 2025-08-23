<?php
require_once 'Connections/Connection.php';

// Manejar las solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createCourt();
            break;
        case 'read':
            readCourts();
            break;
        case 'update':
            updateCourt();
            break;
        case 'delete':
            deleteCourt();
            break;
        case 'get':
            getCourt();
            break;
    }
    exit;
}

function createCourt() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO Basketball_Courts (court_name, location, capacity, other_details) VALUES (?, ?, ?, ?)");
        
        $court_name = strtoupper(trim($_POST['court_name']));
        $location = !empty($_POST['location']) ? trim($_POST['location']) : null;
        $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null;
        $other_details = !empty($_POST['other_details']) ? trim($_POST['other_details']) : null;
        
        // Verificar si ya existe una cancha con ese nombre
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Basketball_Courts WHERE court_name = ?");
        $checkStmt->execute([$court_name]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una cancha con ese nombre']);
            return;
        }
        
        $stmt->execute([$court_name, $location, $capacity, $other_details]);
        
        echo json_encode(['success' => true, 'message' => 'Cancha creada correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear cancha: ' . $e->getMessage()]);
    }
}

function readCourts() {
    global $pdo;
    
    try {
        $search = $_POST['search'] ?? '';
        
        if (!empty($search)) {
            $stmt = $pdo->prepare("SELECT * FROM Basketball_Courts WHERE court_name LIKE ? OR location LIKE ? OR court_id LIKE ? ORDER BY court_id");
            $searchTerm = "%$search%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        } else {
            $stmt = $pdo->query("SELECT * FROM Basketball_Courts ORDER BY court_id");
        }
        
        $courts = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $courts]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener canchas: ' . $e->getMessage()]);
    }
}

function getCourt() {
    global $pdo;
    
    try {
        $court_id = $_POST['court_id'];
        $stmt = $pdo->prepare("SELECT * FROM Basketball_Courts WHERE court_id = ?");
        $stmt->execute([$court_id]);
        
        $court = $stmt->fetch();
        
        if ($court) {
            echo json_encode(['success' => true, 'data' => $court]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cancha no encontrada']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener cancha: ' . $e->getMessage()]);
    }
}

function updateCourt() {
    global $pdo;
    
    try {
        $court_id = $_POST['court_id'];
        $court_name = strtoupper(trim($_POST['court_name']));
        $location = !empty($_POST['location']) ? trim($_POST['location']) : null;
        $capacity = !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null;
        $other_details = !empty($_POST['other_details']) ? trim($_POST['other_details']) : null;
        
        // Verificar si ya existe otra cancha con ese nombre
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Basketball_Courts WHERE court_name = ? AND court_id != ?");
        $checkStmt->execute([$court_name, $court_id]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra cancha con ese nombre']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE Basketball_Courts SET court_name = ?, location = ?, capacity = ?, other_details = ? WHERE court_id = ?");
        $stmt->execute([$court_name, $location, $capacity, $other_details, $court_id]);
        
        echo json_encode(['success' => true, 'message' => 'Cancha actualizada correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar cancha: ' . $e->getMessage()]);
    }
}

function deleteCourt() {
    global $pdo;
    
    try {
        $court_id = $_POST['court_id'];
        
        $stmt = $pdo->prepare("DELETE FROM Basketball_Courts WHERE court_id = ?");
        $stmt->execute([$court_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Cancha eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cancha no encontrada']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar cancha: ' . $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Canchas de Baloncesto - Metro UI PHP</title>
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
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
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
        
        .capacity-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .court-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            padding: 15px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .stat-card h4 {
            margin: 0;
            color: #0366d6;
        }
        
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
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
            
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">🏀 Gestión de Canchas de Baloncesto</h1>
        
        <!-- Status Message -->
        <div id="statusMessage" class="status-message"></div>
        
        <!-- Loading -->
        <div id="loading" class="loading">
            <p>Cargando...</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="court-stats" id="courtStats" style="display: none;">
            <div class="stat-card">
                <h4 id="totalCourts">0</h4>
                <p>Total de Canchas</p>
            </div>
            <div class="stat-card">
                <h4 id="activeCourts">0</h4>
                <p>Canchas Activas</p>
            </div>
            <div class="stat-card">
                <h4 id="totalCapacity">0</h4>
                <p>Capacidad Total</p>
            </div>
        </div>
        
        <!-- Create/Edit Form Section -->
        <div class="crud-section">
            <h3 id="formTitle">➕ Crear Nueva Cancha</h3>
            <form id="courtForm">
                <input type="hidden" id="courtId" name="court_id">
                
                <div class="form-group">
                    <label for="courtName">Nombre de la Cancha:</label>
                    <input type="text" id="courtName" name="court_name" class="form-control" required maxlength="100" placeholder="Ejemplo: CANCHA PRINCIPAL">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Ubicación:</label>
                        <input type="text" id="location" name="location" class="form-control" maxlength="255" placeholder="Ejemplo: Gimnasio Municipal">
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacidad de Espectadores:</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" min="0" max="50000" placeholder="Ejemplo: 500">
                        <div class="capacity-info">Número máximo de espectadores</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="otherDetails">Detalles Adicionales:</label>
                    <textarea id="otherDetails" name="other_details" class="form-control" rows="3" placeholder="Información adicional sobre la cancha (dimensiones, características especiales, etc.)"></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Crear Cancha</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;">Cancelar</button>
                </div>
            </form>
        </div>
        
        <!-- Courts List Section -->
        <div class="crud-section">
            <h3>📋 Lista de Canchas</h3>
            <div class="btn-group">
                <button onclick="loadCourts()" class="btn btn-info">🔄 Actualizar Lista</button>
                <button onclick="clearFilters()" class="btn btn-light">🗑️ Limpiar Filtros</button>
                <button onclick="exportData()" class="btn btn-success">📊 Exportar Datos</button>
            </div>
            
            <!-- Search/Filter -->
            <div class="form-group">
                <input type="text" id="searchInput" placeholder="🔍 Buscar por nombre, ubicación o ID..." class="form-control">
            </div>
            
            <div class="table-container">
                <table class="table" id="courtsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de la Cancha</th>
                            <th>Ubicación</th>
                            <th>Capacidad</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="courtsTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h4>⚠️ Confirmar Eliminación</h4>
            <p>¿Está seguro de que desea eliminar la cancha "<span id="deleteCourtName"></span>"?</p>
            <p><strong>Esta acción no se puede deshacer.</strong></p>
            <div class="btn-group">
                <button onclick="confirmDelete()" class="btn btn-danger">🗑️ Eliminar</button>
                <button onclick="closeDeleteModal()" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/metro.js"></script>
    <script>
        // Global variables
        let editingCourtId = null;
        let courtToDelete = null;
        let searchTimeout = null;
        let allCourts = [];
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadCourts();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Form submission
            document.getElementById('courtForm').addEventListener('submit', handleSubmit);
            
            // Search with debounce
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadCourts(this.value);
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
        
        // Load courts
        function loadCourts(search = '') {
            makeRequest({
                action: 'read',
                search: search
            }, function(response) {
                if (response.success) {
                    allCourts = response.data;
                    displayCourts(response.data);
                    updateStatistics(response.data);
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Update statistics
        function updateStatistics(courts) {
            const totalCourts = courts.length;
            const activeCourts = courts.filter(court => court.court_name && court.court_name !== 'None').length;
            const totalCapacity = courts.reduce((sum, court) => {
                const capacity = court.capacity && court.capacity !== 'None' ? parseInt(court.capacity) : 0;
                return sum + capacity;
            }, 0);
            
            document.getElementById('totalCourts').textContent = totalCourts;
            document.getElementById('activeCourts').textContent = activeCourts;
            document.getElementById('totalCapacity').textContent = totalCapacity.toLocaleString();
            document.getElementById('courtStats').style.display = 'grid';
        }
        
        // Display courts in table
        function displayCourts(courts) {
            const tbody = document.getElementById('courtsTableBody');
            tbody.innerHTML = '';
            
            if (courts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron canchas</td></tr>';
                return;
            }
            
            courts.forEach(court => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${court.court_id}</td>
                    <td><strong>${court.court_name}</strong></td>
                    <td>${court.location || 'No especificada'}</td>
                    <td>${court.capacity ? parseInt(court.capacity).toLocaleString() + ' personas' : 'No especificada'}</td>
                    <td>${court.other_details || 'Sin detalles'}</td>
                    <td>
                        <button onclick="editCourt(${court.court_id})" class="btn btn-info btn-small">✏️ Editar</button>
                        <button onclick="deleteCourt(${court.court_id}, '${court.court_name}')" class="btn btn-danger btn-small">🗑️ Eliminar</button>
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
            if (!data.court_name.trim()) {
                showStatus('El nombre de la cancha es requerido', 'error');
                return;
            }
            
            if (data.capacity && (data.capacity < 0 || data.capacity > 50000)) {
                showStatus('La capacidad debe estar entre 0 y 50,000 espectadores', 'error');
                return;
            }
            
            if (editingCourtId) {
                data.action = 'update';
                data.court_id = editingCourtId;
            } else {
                data.action = 'create';
            }
            
            makeRequest(data, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadCourts();
                    resetForm();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Edit court
        function editCourt(id) {
            makeRequest({
                action: 'get',
                court_id: id
            }, function(response) {
                if (response.success) {
                    const court = response.data;
                    
                    editingCourtId = id;
                    
                    // Fill form with court data
                    document.getElementById('courtId').value = court.court_id;
                    document.getElementById('courtName').value = court.court_name;
                    document.getElementById('location').value = court.location || '';
                    document.getElementById('capacity').value = court.capacity || '';
                    document.getElementById('otherDetails').value = court.other_details || '';
                    
                    // Update form title and button
                    document.getElementById('formTitle').textContent = '✏️ Editar Cancha';
                    document.getElementById('submitBtn').textContent = 'Actualizar Cancha';
                    document.getElementById('cancelBtn').style.display = 'inline-block';
                    
                    // Scroll to form
                    document.querySelector('.crud-section').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Delete court
        function deleteCourt(id, name) {
            courtToDelete = id;
            document.getElementById('deleteCourtName').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        // Confirm delete
        function confirmDelete() {
            if (!courtToDelete) return;
            
            makeRequest({
                action: 'delete',
                court_id: courtToDelete
            }, function(response) {
                if (response.success) {
                    showStatus(response.message, 'success');
                    loadCourts();
                    closeDeleteModal();
                } else {
                    showStatus(response.message, 'error');
                }
            });
        }
        
        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            courtToDelete = null;
        }
        
        // Cancel edit
        function cancelEdit() {
            resetForm();
        }
        
        // Reset form
        function resetForm() {
            document.getElementById('courtForm').reset();
            editingCourtId = null;
            
            document.getElementById('formTitle').textContent = '➕ Crear Nueva Cancha';
            document.getElementById('submitBtn').textContent = 'Crear Cancha';
            document.getElementById('cancelBtn').style.display = 'none';
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            loadCourts();
        }
        
        // Export data
        function exportData() {
            if (allCourts.length === 0) {
                showStatus('No hay datos para exportar', 'error');
                return;
            }
            
            let csvContent = "ID,Nombre,Ubicación,Capacidad,Detalles\n";
            
            allCourts.forEach(court => {
                csvContent += `${court.court_id},"${court.court_name}","${court.location || ''}","${court.capacity || ''}","${court.other_details || ''}"\n`;
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `canchas_baloncesto_${new Date().toISOString().slice(0, 10)}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
            
            showStatus('Datos exportados correctamente', 'success');
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
