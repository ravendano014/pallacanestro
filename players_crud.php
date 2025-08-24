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
                $stmt = $pdo->prepare("INSERT INTO Players (first_name, last_name, gender, address, other_details) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['address'],
                    $_POST['other_details']
                ]);
                $message = 'Jugador creado exitosamente';
                $messageType = 'success';
                break;
                
            case 'update':
                $stmt = $pdo->prepare("UPDATE Players SET first_name=?, last_name=?, gender=?, address=?, other_details=? WHERE player_id=?");
                $stmt->execute([
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['address'],
                    $_POST['other_details'],
                    $_POST['player_id']
                ]);
                $message = 'Jugador actualizado exitosamente';
                $messageType = 'success';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM Players WHERE player_id = ?");
                $stmt->execute([$_POST['player_id']]);
                $message = 'Jugador eliminado exitosamente';
                $messageType = 'success';
                break;
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Obtener todos los jugadores
$stmt = $pdo->query("SELECT * FROM Players ORDER BY player_id DESC");
$players = $stmt->fetchAll();

// Obtener jugador específico para editar
$editPlayer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Players WHERE player_id = ?");
    $stmt->execute([$_GET['edit']]);
    $editPlayer = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Jugadores - Pallacanestro</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0078d4;
            box-shadow: 0 0 0 2px rgba(0,120,212,0.2);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #0078d4;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #005a9e;
        }
        
        .btn-success {
            background-color: #107c10;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0e5f0e;
        }
        
        .btn-warning {
            background-color: #ff8c00;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e07600;
        }
        
        .btn-danger {
            background-color: #d13438;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #a1292b;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .actions .btn {
            margin-right: 5px;
        }
        
        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-title {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #0078d4;
            padding-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .container {
                padding: 10px;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Gestión de Jugadores</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para crear/editar jugador -->
        <div class="form-container">
            <h2 class="form-title">
                <?php echo $editPlayer ? 'Editar Jugador' : 'Nuevo Jugador'; ?>
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editPlayer ? 'update' : 'create'; ?>">
                <?php if ($editPlayer): ?>
                    <input type="hidden" name="player_id" value="<?php echo $editPlayer['player_id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nombre *</label>
                        <input type="text" 
                               name="first_name" 
                               id="first_name" 
                               class="form-control" 
                               value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['first_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Apellido *</label>
                        <input type="text" 
                               name="last_name" 
                               id="last_name" 
                               class="form-control" 
                               value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['last_name']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Género</label>
                        <select name="gender" id="gender" class="form-control">
                            <option value="">Seleccionar género</option>
                            <option value="M" <?php echo ($editPlayer && $editPlayer['gender'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                            <option value="F" <?php echo ($editPlayer && $editPlayer['gender'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                            <option value="O" <?php echo ($editPlayer && $editPlayer['gender'] == 'O') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Dirección</label>
                        <input type="text" 
                               name="address" 
                               id="address" 
                               class="form-control" 
                               value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['address']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="other_details">Otros Detalles</label>
                    <textarea name="other_details" 
                              id="other_details" 
                              class="form-control" 
                              rows="3"><?php echo $editPlayer ? htmlspecialchars($editPlayer['other_details']) : ''; ?></textarea>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn <?php echo $editPlayer ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $editPlayer ? 'Actualizar Jugador' : 'Crear Jugador'; ?>
                    </button>
                    
                    <?php if ($editPlayer): ?>
                        <a href="players_crud.php" class="btn btn-primary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Tabla de jugadores -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Género</th>
                        <th>Dirección</th>
                        <th>Otros Detalles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($players) > 0): ?>
                        <?php foreach ($players as $player): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($player['player_id']); ?></td>
                                <td><?php echo htmlspecialchars($player['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($player['last_name']); ?></td>
                                <td>
                                    <?php 
                                    $genders = ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'];
                                    echo isset($genders[$player['gender']]) ? $genders[$player['gender']] : '-';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($player['address']) ?: '-'; ?></td>
                                <td><?php echo htmlspecialchars($player['other_details']) ?: '-'; ?></td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $player['player_id']; ?>" 
                                       class="btn btn-warning btn-sm">Editar</a>
                                    
                                    <form method="POST" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('¿Está seguro de eliminar este jugador?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="player_id" value="<?php echo $player['player_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                No hay jugadores registrados
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
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
        
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            
            if (!firstName || !lastName) {
                e.preventDefault();
                alert('Los campos Nombre y Apellido son obligatorios');
                return false;
            }
        });
    </script>
</body>
</html>