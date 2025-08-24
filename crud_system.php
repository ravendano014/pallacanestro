<?php
require_once 'Connections/Connection.php';

// Determinar qué tabla mostrar
$table = isset($_GET['table']) ? $_GET['table'] : 'players';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $action = $_POST['action'];
    
    try {
        if ($table === 'players') {
            handlePlayersAction($pdo, $action, $_POST);
        } elseif ($table === 'leagues') {
            handleLeaguesAction($pdo, $action, $_POST);
        } elseif ($table === 'matches') {
            handleMatchesAction($pdo, $action, $_POST);
        }
        
        // Redireccionar después de la acción
        header("Location: crud_system.php?table=$table&message=" . urlencode("Operación realizada exitosamente"));
        exit;
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Funciones para manejar cada tabla
function handlePlayersAction($pdo, $action, $data) {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO Players (first_name, last_name, gender, address, other_details) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['gender'] ?: null,
            $data['address'] ?: null,
            $data['other_details'] ?: null
        ]);
    } elseif ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE Players SET first_name=?, last_name=?, gender=?, address=?, other_details=? WHERE player_id=?");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['gender'] ?: null,
            $data['address'] ?: null,
            $data['other_details'] ?: null,
            $data['id']
        ]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM Players WHERE player_id=?");
        $stmt->execute([$data['id']]);
    }
}

function handleLeaguesAction($pdo, $action, $data) {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO Leagues (league_name, league_details) VALUES (?, ?)");
        $stmt->execute([
            $data['league_name'],
            $data['league_details'] ?: null
        ]);
    } elseif ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE Leagues SET league_name=?, league_details=? WHERE league_id=?");
        $stmt->execute([
            $data['league_name'],
            $data['league_details'] ?: null,
            $data['id']
        ]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM Leagues WHERE league_id=?");
        $stmt->execute([$data['id']]);
    }
}

function handleMatchesAction($pdo, $action, $data) {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO Matches (game_code, player_1_id, player_2_id, court_id, match_date, result, other_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['game_code'],
            $data['player_1_id'],
            $data['player_2_id'],
            $data['court_id'],
            $data['match_date'],
            $data['result'] ?: null,
            $data['other_details'] ?: null
        ]);
    } elseif ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE Matches SET game_code=?, player_1_id=?, player_2_id=?, court_id=?, match_date=?, result=?, other_details=? WHERE match_id=?");
        $stmt->execute([
            $data['game_code'],
            $data['player_1_id'],
            $data['player_2_id'],
            $data['court_id'],
            $data['match_date'],
            $data['result'] ?: null,
            $data['other_details'] ?: null,
            $data['id']
        ]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM Matches WHERE match_id=?");
        $stmt->execute([$data['id']]);
    }
}

// Obtener datos para editar
$editData = null;
if ($action === 'edit' && $id > 0) {
    if ($table === 'players') {
        $stmt = $pdo->prepare("SELECT * FROM Players WHERE player_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
    } elseif ($table === 'leagues') {
        $stmt = $pdo->prepare("SELECT * FROM Leagues WHERE league_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
    } elseif ($table === 'matches') {
        $stmt = $pdo->prepare("SELECT * FROM Matches WHERE match_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
    }
}

// Obtener lista de datos
if ($table === 'players') {
    $stmt = $pdo->query("SELECT * FROM Players ORDER BY player_id DESC");
    $data = $stmt->fetchAll();
} elseif ($table === 'leagues') {
    $stmt = $pdo->query("SELECT * FROM Leagues ORDER BY league_id DESC");
    $data = $stmt->fetchAll();
} elseif ($table === 'matches') {
    $stmt = $pdo->query("
        SELECT m.*, 
               CONCAT(p1.first_name, ' ', p1.last_name) as player_1_name,
               CONCAT(p2.first_name, ' ', p2.last_name) as player_2_name,
               bc.court_name
        FROM Matches m
        LEFT JOIN Players p1 ON m.player_1_id = p1.player_id
        LEFT JOIN Players p2 ON m.player_2_id = p2.player_id
        LEFT JOIN Basketball_Courts bc ON m.court_id = bc.court_id
        ORDER BY m.match_id DESC
    ");
    $data = $stmt->fetchAll();
}

// Obtener datos auxiliares para formularios
$players = $pdo->query("SELECT * FROM Players ORDER BY first_name, last_name")->fetchAll();
$courts = $pdo->query("SELECT * FROM Basketball_Courts ORDER BY court_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD System - Basketball Management</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .nav-tabs {
            margin-bottom: 20px;
        }
        
        .nav-tabs a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 5px;
            background: #f4f4f4;
            color: #333;
            text-decoration: none;
            border-radius: 5px 5px 0 0;
        }
        
        .nav-tabs a.active {
            background: #007ACC;
            color: white;
        }
        
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .btn {
            padding: 10px 20px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #007ACC; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background: #f4f4f4;
            font-weight: bold;
        }
        
        .table tr:hover {
            background: #f9f9f9;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistema CRUD - Gestión de Basketball</h1>
        
        <!-- Navegación entre tablas -->
        <div class="nav-tabs">
            <a href="crud_system.php?table=players" <?php echo $table === 'players' ? 'class="active"' : ''; ?>>
                Jugadores
            </a>
            <a href="crud_system.php?table=leagues" <?php echo $table === 'leagues' ? 'class="active"' : ''; ?>>
                Ligas
            </a>
            <a href="crud_system.php?table=matches" <?php echo $table === 'matches' ? 'class="active"' : ''; ?>>
                Partidos
            </a>
        </div>
        
        <!-- Mensajes -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario -->
        <div class="form-section">
            <h2>
                <?php 
                if ($action === 'edit') {
                    echo 'Editar ' . ucfirst($table);
                } else {
                    echo 'Agregar ' . ucfirst($table);
                }
                ?>
            </h2>
            
            <?php if ($table === 'players'): ?>
                <!-- Formulario Players -->
                <form method="POST">
                    <input type="hidden" name="table" value="players">
                    <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $editData['player_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Nombre *</label>
                            <input type="text" id="first_name" name="first_name" required
                                   value="<?php echo $editData ? htmlspecialchars($editData['first_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Apellido *</label>
                            <input type="text" id="last_name" name="last_name" required
                                   value="<?php echo $editData ? htmlspecialchars($editData['last_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Género</label>
                            <select id="gender" name="gender">
                                <option value="">Seleccionar...</option>
                                <option value="M" <?php echo ($editData && $editData['gender'] === 'M') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="F" <?php echo ($editData && $editData['gender'] === 'F') ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <input type="text" id="address" name="address"
                                   value="<?php echo $editData ? htmlspecialchars($editData['address']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="other_details">Otros Detalles</label>
                        <textarea id="other_details" name="other_details" rows="3"><?php echo $editData ? htmlspecialchars($editData['other_details']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'edit' ? 'Actualizar' : 'Crear'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="crud_system.php?table=players" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
                
            <?php elseif ($table === 'leagues'): ?>
                <!-- Formulario Leagues -->
                <form method="POST">
                    <input type="hidden" name="table" value="leagues">
                    <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $editData['league_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="league_name">Nombre de la Liga *</label>
                        <input type="text" id="league_name" name="league_name" required
                               value="<?php echo $editData ? htmlspecialchars($editData['league_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="league_details">Detalles de la Liga</label>
                        <textarea id="league_details" name="league_details" rows="4"><?php echo $editData ? htmlspecialchars($editData['league_details']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'edit' ? 'Actualizar' : 'Crear'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="crud_system.php?table=leagues" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
                
            <?php elseif ($table === 'matches'): ?>
                <!-- Formulario Matches -->
                <form method="POST">
                    <input type="hidden" name="table" value="matches">
                    <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $editData['match_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="game_code">Código del Juego *</label>
                            <input type="text" id="game_code" name="game_code" required
                                   value="<?php echo $editData ? htmlspecialchars($editData['game_code']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="court_id">Cancha *</label>
                            <select id="court_id" name="court_id" required>
                                <option value="">Seleccionar cancha...</option>
                                <?php foreach ($courts as $court): ?>
                                    <option value="<?php echo $court['court_id']; ?>" 
                                            <?php echo ($editData && $editData['court_id'] == $court['court_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($court['court_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="player_1_id">Jugador 1 *</label>
                            <select id="player_1_id" name="player_1_id" required>
                                <option value="">Seleccionar jugador...</option>
                                <?php foreach ($players as $player): ?>
                                    <option value="<?php echo $player['player_id']; ?>"
                                            <?php echo ($editData && $editData['player_1_id'] == $player['player_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="player_2_id">Jugador 2 *</label>
                            <select id="player_2_id" name="player_2_id" required>
                                <option value="">Seleccionar jugador...</option>
                                <?php foreach ($players as $player): ?>
                                    <option value="<?php echo $player['player_id']; ?>"
                                            <?php echo ($editData && $editData['player_2_id'] == $player['player_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="match_date">Fecha y Hora del Partido *</label>
                            <input type="datetime-local" id="match_date" name="match_date" required
                                   value="<?php echo $editData ? date('Y-m-d\TH:i', strtotime($editData['match_date'])) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="result">Resultado</label>
                            <input type="text" id="result" name="result" placeholder="Ej: 21-18"
                                   value="<?php echo $editData ? htmlspecialchars($editData['result']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="other_details">Otros Detalles</label>
                        <textarea id="other_details" name="other_details" rows="3"><?php echo $editData ? htmlspecialchars($editData['other_details']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'edit' ? 'Actualizar' : 'Crear'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="crud_system.php?table=matches" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Lista de datos -->
        <div>
            <h2>Lista de <?php echo ucfirst($table); ?></h2>
            
            <table class="table">
                <?php if ($table === 'players'): ?>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Género</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo $row['player_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo $row['gender'] ? htmlspecialchars($row['gender']) : '-'; ?></td>
                                <td><?php echo $row['address'] ? htmlspecialchars($row['address']) : '-'; ?></td>
                                <td>
                                    <a href="crud_system.php?table=players&action=edit&id=<?php echo $row['player_id']; ?>" 
                                       class="btn btn-secondary">Editar</a>
                                    <button onclick="deleteRecord('players', <?php echo $row['player_id']; ?>)" 
                                            class="btn btn-danger">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    
                <?php elseif ($table === 'leagues'): ?>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de la Liga</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo $row['league_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['league_name']); ?></td>
                                <td><?php echo $row['league_details'] ? htmlspecialchars(substr($row['league_details'], 0, 100)) . '...' : '-'; ?></td>
                                <td>
                                    <a href="crud_system.php?table=leagues&action=edit&id=<?php echo $row['league_id']; ?>" 
                                       class="btn btn-secondary">Editar</a>
                                    <button onclick="deleteRecord('leagues', <?php echo $row['league_id']; ?>)" 
                                            class="btn btn-danger">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    
                <?php elseif ($table === 'matches'): ?>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Jugador 1</th>
                            <th>Jugador 2</th>
                            <th>Cancha</th>
                            <th>Fecha</th>
                            <th>Resultado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo $row['match_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['game_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['player_1_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['player_2_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['court_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['match_date'])); ?></td>
                                <td><?php echo $row['result'] ? htmlspecialchars($row['result']) : '-'; ?></td>
                                <td>
                                    <a href="crud_system.php?table=matches&action=edit&id=<?php echo $row['match_id']; ?>" 
                                       class="btn btn-secondary">Editar</a>
                                    <button onclick="deleteRecord('matches', <?php echo $row['match_id']; ?>)" 
                                            class="btn btn-danger">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <!-- Modal de confirmación para eliminar -->
    <div id="deleteModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 5px; width: 300px;">
            <h3>Confirmar eliminación</h3>
            <p>¿Está seguro de que desea eliminar este registro?</p>
            <form id="deleteForm" method="POST" style="text-align: right;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="table" id="deleteTable">
                <input type="hidden" name="id" id="deleteId">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
    
    <script src="assets/js/metro.js"></script>
    <script>
        function deleteRecord(table, id) {
            document.getElementById('deleteTable').value = table;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Validación del formulario de matches para evitar que un jugador juegue contra sí mismo
        document.addEventListener('DOMContentLoaded', function() {
            const player1Select = document.getElementById('player_1_id');
            const player2Select = document.getElementById('player_2_id');
            
            if (player1Select && player2Select) {
                function validatePlayers() {
                    if (player1Select.value && player2Select.value && player1Select.value === player2Select.value) {
                        alert('Un jugador no puede jugar contra sí mismo. Por favor, seleccione jugadores diferentes.');
                        player2Select.value = '';
                    }
                }
                
                player1Select.addEventListener('change', validatePlayers);
                player2Select.addEventListener('change', validatePlayers);
            }
        });
    </script>
</body>
</html>