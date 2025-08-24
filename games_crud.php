<?php
require_once 'Connections/Connection.php';

// Variables para mensajes
$message = '';
$messageType = '';

// Procesar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $stmt = $pdo->prepare("INSERT INTO Games (game_code, game_name, game_description, other_details) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['game_code'],
                    $_POST['game_name'],
                    $_POST['game_description'],
                    $_POST['other_details']
                ]);
                $message = 'Juego creado exitosamente';
                $messageType = 'success';
                break;
                
            case 'update':
                $stmt = $pdo->prepare("UPDATE Games SET game_name=?, game_description=?, other_details=? WHERE game_code=?");
                $stmt->execute([
                    $_POST['game_name'],
                    $_POST['game_description'],
                    $_POST['other_details'],
                    $_POST['game_code']
                ]);
                $message = 'Juego actualizado exitosamente';
                $messageType = 'success';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM Games WHERE game_code = ?");
                $stmt->execute([$_POST['game_code']]);
                $message = 'Juego eliminado exitosamente';
                $messageType = 'success';
                break;
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = 'Error: No se puede eliminar el juego porque está siendo usado en otros registros';
        } else {
            $message = 'Error: ' . $e->getMessage();
        }
        $messageType = 'error';
    }
}

// Obtener todos los juegos
$stmt = $pdo->query("SELECT * FROM Games ORDER BY game_code ASC");
$games = $stmt->fetchAll();

// Obtener juego específico para editar
$editGame = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Games WHERE game_code = ?");
    $stmt->execute([$_GET['edit']]);
    $editGame = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Juegos - Pallacanestro</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 4px solid #0078d4;
        }
        
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group.full-width {
            flex: 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0078d4;
            box-shadow: 0 0 0 3px rgba(0,120,212,0.1);
        }
        
        .form-control.code-input {
            text-transform: uppercase;
            font-family: monospace;
            font-weight: bold;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0078d4, #005a9e);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #005a9e, #004578);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #107c10, #0e5f0e);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #0e5f0e, #0a4b0a);
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ff8c00, #e07600);
            color: white;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #e07600, #cc6600);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #d13438, #a1292b);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #a1292b, #8b2325);
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
        
        .alert {
            padding: 16px 20px;
            margin-bottom: 25px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            position: relative;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            color: #155724;
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            color: #721c24;
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid #dc3545;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-weight: 700;
            color: #495057;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .table tr {
            transition: all 0.3s ease;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .actions .btn {
            margin-right: 8px;
        }
        
        .page-title {
            color: #333;
            margin-bottom: 40px;
            text-align: center;
            font-size: 2.5em;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .form-title {
            color: #333;
            margin-bottom: 25px;
            border-bottom: 3px solid #0078d4;
            padding-bottom: 10px;
            font-size: 1.5em;
            font-weight: 400;
        }
        
        .game-code-display {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            color: #495057;
            border: 2px solid #e9ecef;
        }
        
        .stats-container {
            background: linear-gradient(135deg, #0078d4, #005a9e);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stats-label {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .container {
                padding: 15px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .page-title {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">🎮 Gestión de Juegos</h1>
        
        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stats-number"><?php echo count($games); ?></div>
            <div class="stats-label">Juegos Registrados</div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para crear/editar juego -->
        <div class="form-container">
            <h2 class="form-title">
                <?php echo $editGame ? '✏️ Editar Juego' : '➕ Nuevo Juego'; ?>
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editGame ? 'update' : 'create'; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="game_code">Código del Juego *</label>
                        <?php if ($editGame): ?>
                            <input type="hidden" name="game_code" value="<?php echo $editGame['game_code']; ?>">
                            <div class="game-code-display">
                                <?php echo htmlspecialchars($editGame['game_code']); ?>
                            </div>
                        <?php else: ?>
                            <input type="text" 
                                   name="game_code" 
                                   id="game_code" 
                                   class="form-control code-input" 
                                   placeholder="Ej: BASK01, FOOT02"
                                   maxlength="10"
                                   required>
                            <small style="color: #666; font-size: 12px;">Código único para identificar el juego</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="game_name">Nombre del Juego *</label>
                        <input type="text" 
                               name="game_name" 
                               id="game_name" 
                               class="form-control" 
                               placeholder="Ej: Basketball, Fútbol"
                               value="<?php echo $editGame ? htmlspecialchars($editGame['game_name']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="game_description">Descripción del Juego</label>
                        <textarea name="game_description" 
                                  id="game_description" 
                                  class="form-control" 
                                  rows="4"
                                  placeholder="Describe las características y reglas del juego..."><?php echo $editGame ? htmlspecialchars($editGame['game_description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="other_details">Detalles Adicionales</label>
                        <textarea name="other_details" 
                                  id="other_details" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Información adicional, equipamiento necesario, etc..."><?php echo $editGame ? htmlspecialchars($editGame['other_details']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn <?php echo $editGame ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $editGame ? '💾 Actualizar Juego' : '🎯 Crear Juego'; ?>
                    </button>
                    
                    <?php if ($editGame): ?>
                        <a href="games_crud.php" class="btn btn-primary">🔙 Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Tabla de juegos -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>🎮 Código</th>
                        <th>📛 Nombre</th>
                        <th>📝 Descripción</th>
                        <th>ℹ️ Detalles</th>
                        <th>⚙️ Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($games) > 0): ?>
                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td>
                                    <span class="game-code-display" style="display: inline-block; padding: 4px 8px; font-size: 12px;">
                                        <?php echo htmlspecialchars($game['game_code']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($game['game_name']); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $description = htmlspecialchars($game['game_description']);
                                    echo $description && $description !== 'None' ? 
                                         (strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description) : 
                                         '<span style="color: #999; font-style: italic;">Sin descripción</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $details = htmlspecialchars($game['other_details']);
                                    echo $details && $details !== 'None' ? 
                                         (strlen($details) > 30 ? substr($details, 0, 30) . '...' : $details) : 
                                         '<span style="color: #999; font-style: italic;">Sin detalles</span>';
                                    ?>
                                </td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $game['game_code']; ?>" 
                                       class="btn btn-warning btn-sm"
                                       title="Editar juego">✏️ Editar</a>
                                    
                                    <form method="POST" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('¿Está seguro de eliminar el juego \"<?php echo htmlspecialchars($game['game_name']); ?>\"?\n\nEsta acción no se puede deshacer.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="game_code" value="<?php echo $game['game_code']; ?>">
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm"
                                                title="Eliminar juego">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px; color: #666;">
                                <div style="font-size: 48px; margin-bottom: 20px;">🎮</div>
                                <h3>No hay juegos registrados</h3>
                                <p>Comienza creando tu primer juego usando el formulario de arriba</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="assets/js/metro.js"></script>
    <script>
        // Función para limpiar mensajes después de unos segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
        
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const gameCode = document.getElementById('game_code');
            const gameName = document.getElementById('game_name');
            
            if (gameCode) {
                const code = gameCode.value.trim();
                if (!code || code.length < 3) {
                    e.preventDefault();
                    alert('El código del juego debe tener al menos 3 caracteres');
                    gameCode.focus();
                    return false;
                }
                
                // Convertir a mayúsculas
                gameCode.value = code.toUpperCase();
            }
            
            const name = gameName.value.trim();
            if (!name || name.length < 2) {
                e.preventDefault();
                alert('El nombre del juego debe tener al menos 2 caracteres');
                gameName.focus();
                return false;
            }
        });
        
        // Auto-conversión a mayúsculas para el código
        const gameCodeInput = document.getElementById('game_code');
        if (gameCodeInput) {
            gameCodeInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });
        }
        
        // Animación para las filas de la tabla
        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach(function(row, index) {
            row.style.animationDelay = (index * 0.1) + 's';
            row.style.animation = 'slideIn 0.5s ease forwards';
        });
    </script>
</body>
</html>