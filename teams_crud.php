<?php
require_once 'Connections/Connection.php';

// Función para obtener todos los equipos
function getAllteams($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM teams ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return false;
    }
}

// Función para obtener un equipo por ID
function getTeamById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Función para crear o actualizar un equipo
function saveTeam($pdo, $data) {
    try {
        if (empty($data['id'])) {
            // Crear nuevo equipo
            $stmt = $pdo->prepare("INSERT INTO teams (name, city, coach, founded_year, logo_url, description) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['name'],
                $data['city'],
                $data['coach'],
                $data['founded_year'],
                $data['logo_url'] ?? null,
                $data['description'] ?? null
            ]);
        } else {
            // Actualizar equipo existente
            $stmt = $pdo->prepare("UPDATE teams SET name = ?, city = ?, coach = ?, founded_year = ?, logo_url = ?, description = ? WHERE id = ?");
            $result = $stmt->execute([
                $data['name'],
                $data['city'],
                $data['coach'],
                $data['founded_year'],
                $data['logo_url'] ?? null,
                $data['description'] ?? null,
                $data['id']
            ]);
        }
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Función para eliminar un equipo
function deleteTeam($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        return false;
    }
}

// Manejar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE' || isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get':
            $id = $_GET['id'] ?? 0;
            $team = getTeamById($pdo, $id);
            if ($team) {
                echo json_encode(['success' => true, 'team' => $team]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
            }
            exit;
            
        case 'save':
            $data = [
                'id' => $_POST['teamId'] ?? '',
                'name' => $_POST['teamName'] ?? '',
                'city' => $_POST['teamCity'] ?? '',
                'coach' => $_POST['teamCoach'] ?? '',
                'founded_year' => $_POST['teamFoundedYear'] ?? null,
                'logo_url' => $_POST['teamLogoUrl'] ?? '',
                'description' => $_POST['teamDescription'] ?? ''
            ];
            
            if (empty($data['name'])) {
                echo json_encode(['success' => false, 'message' => 'El nombre del equipo es requerido']);
                exit;
            }
            
            if (saveTeam($pdo, $data)) {
                $message = empty($data['id']) ? 'Equipo creado exitosamente' : 'Equipo actualizado exitosamente';
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el equipo']);
            }
            exit;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            if (deleteTeam($pdo, $id)) {
                echo json_encode(['success' => true, 'message' => 'Equipo eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el equipo']);
            }
            exit;
    }
}

// Obtener todos los equipos para mostrar
$teams = getAllteams($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos - Pallacanestro</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .team-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons .btn {
            padding: 8px 12px;
            margin: 2px;
            font-size: 12px;
            min-width: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🏀 Gestión de Equipos</h1>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Lista de Equipos</span>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#teamModal" onclick="clearForm()">
                        + Nuevo Equipo
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($teams && count($teams) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Nombre</th>
                                    <th>Ciudad</th>
                                    <th>Entrenador</th>
                                    <th>Año Fundación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $team): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($team['id']) ?></td>
                                        <td>
                                            <?php if (!empty($team['logo_url'])): ?>
                                                <img src="<?= htmlspecialchars($team['logo_url']) ?>" 
                                                     alt="Logo" class="team-logo" 
                                                     onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <div class="team-logo" style="background-color: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                    <?= strtoupper(substr($team['name'], 0, 2)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($team['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($team['city'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($team['coach'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($team['founded_year'] ?? '') ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-warning edit-team" 
                                                    data-id="<?= $team['id'] ?>">
                                                ✏️ Editar
                                            </button>
                                            <button class="btn btn-danger delete-team" 
                                                    data-id="<?= $team['id'] ?>"
                                                    data-name="<?= htmlspecialchars($team['name']) ?>">
                                                🗑️ Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px;">
                        <h3 style="color: #666;">No hay equipos registrados</h3>
                        <p>Haga clic en "Nuevo Equipo" para agregar el primer equipo.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Equipo -->
    <div id="teamModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modalTitle">Nuevo Equipo</h4>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="teamForm" data-validate="true">
                    <input type="hidden" id="teamId" name="teamId">
                    
                    <div class="form-group">
                        <label for="teamName" class="form-label">Nombre del Equipo *</label>
                        <input type="text" id="teamName" name="teamName" class="form-control" 
                               required data-min-length="2">
                    </div>

                    <div class="form-group">
                        <label for="teamCity" class="form-label">Ciudad</label>
                        <input type="text" id="teamCity" name="teamCity" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="teamCoach" class="form-label">Entrenador</label>
                        <input type="text" id="teamCoach" name="teamCoach" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="teamFoundedYear" class="form-label">Año de Fundación</label>
                        <input type="number" id="teamFoundedYear" name="teamFoundedYear" 
                               class="form-control" min="1900" max="2024">
                    </div>

                    <div class="form-group">
                        <label for="teamLogoUrl" class="form-label">URL del Logo</label>
                        <input type="url" id="teamLogoUrl" name="teamLogoUrl" class="form-control"
                               placeholder="https://ejemplo.com/logo.png">
                    </div>

                    <div class="form-group">
                        <label for="teamDescription" class="form-label">Descripción</label>
                        <textarea id="teamDescription" name="teamDescription" class="form-control" 
                                  rows="3" placeholder="Descripción del equipo..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="Metro.closeModal(document.getElementById('teamModal'))">
                    Cancelar
                </button>
                <button type="submit" form="teamForm" class="btn btn-primary">
                    Guardar Equipo
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/metro.js"></script>
    <script>
        function clearForm() {
            document.getElementById('teamForm').reset();
            document.getElementById('teamId').value = '';
            document.getElementById('modalTitle').textContent = 'Nuevo Equipo';
            
            // Clear any existing errors
            const errors = document.querySelectorAll('.field-error');
            errors.forEach(error => error.remove());
            
            const errorFields = document.querySelectorAll('.error');
            errorFields.forEach(field => field.classList.remove('error'));
        }

        // Update modal title when editing
        document.addEventListener('click', (e) => {
            if (e.target.matches('.edit-team')) {
                document.getElementById('modalTitle').textContent = 'Editar Equipo';
            }
        });

        // Show loading state during operations
        document.getElementById('teamForm').addEventListener('submit', (e) => {
            const submitBtn = document.querySelector('button[type="submit"][form="teamForm"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
            
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Equipo';
            }, 3000);
        });
    </script>
</body>
</html>