<?php
// Incluir el archivo de conexión
require_once 'Connections/Connection.php';

// Configuración de paginación
$recordsPorPagina = 10;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $recordsPorPagina;

// Manejar operaciones CRUD
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $stmt = $pdo->prepare("INSERT INTO Tournaments (league_id, game_code, name, season_label, start_date, end_date, stage, gender, sport, category, win_points, draw_points, loss_points, wo_win_points, wo_loss_points, other_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $other_details = !empty($_POST['other_details']) ? $_POST['other_details'] : null;
                
                $stmt->execute([
                    $_POST['league_id'], $_POST['game_code'], $_POST['name'], 
                    $_POST['season_label'], $start_date, $end_date, $_POST['stage'], 
                    $_POST['gender'], $_POST['sport'], $_POST['category'], 
                    $_POST['win_points'], $_POST['draw_points'], $_POST['loss_points'], 
                    $_POST['wo_win_points'], $_POST['wo_loss_points'], $other_details
                ]);
                $mensaje = "Torneo creado exitosamente";
                $tipoMensaje = "success";
                break;
                
            case 'update':
                $stmt = $pdo->prepare("UPDATE Tournaments SET league_id=?, game_code=?, name=?, season_label=?, start_date=?, end_date=?, stage=?, gender=?, sport=?, category=?, win_points=?, draw_points=?, loss_points=?, wo_win_points=?, wo_loss_points=?, other_details=? WHERE tournament_id=?");
                
                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $other_details = !empty($_POST['other_details']) ? $_POST['other_details'] : null;
                
                $stmt->execute([
                    $_POST['league_id'], $_POST['game_code'], $_POST['name'], 
                    $_POST['season_label'], $start_date, $end_date, $_POST['stage'], 
                    $_POST['gender'], $_POST['sport'], $_POST['category'], 
                    $_POST['win_points'], $_POST['draw_points'], $_POST['loss_points'], 
                    $_POST['wo_win_points'], $_POST['wo_loss_points'], $other_details, 
                    $_POST['tournament_id']
                ]);
                $mensaje = "Torneo actualizado exitosamente";
                $tipoMensaje = "success";
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM Tournaments WHERE tournament_id = ?");
                $stmt->execute([$_POST['tournament_id']]);
                $mensaje = "Torneo eliminado exitosamente";
                $tipoMensaje = "success";
                break;
        }
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipoMensaje = "error";
    }
}

// Obtener datos para editar
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Tournaments WHERE tournament_id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

// Buscar registros
$searchTerm = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($searchTerm)) {
    $whereClause = "WHERE name LIKE ? OR game_code LIKE ? OR sport LIKE ? OR category LIKE ?";
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

// Contar total de registros
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM Tournaments $whereClause");
$countStmt->execute($params);
$totalRegistros = $countStmt->fetch()['total'];
$totalPaginas = ceil($totalRegistros / $recordsPorPagina);

// Obtener registros con paginación
$stmt = $pdo->prepare("SELECT * FROM Tournaments $whereClause ORDER BY tournament_id DESC LIMIT $recordsPorPagina OFFSET $offset");
$stmt->execute($params);
$tournaments = $stmt->fetchAll();

// Obtener opciones para selects
$stmt = $pdo->prepare("SELECT DISTINCT league_id FROM Tournaments");
$stmt->execute();
$leagues = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DISTINCT sport FROM Tournaments");
$stmt->execute();
$sports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Tournaments - Sistema de Gestión</title>
    <link rel="stylesheet" href="assets/css/metro.css">
    <style>
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }
        .table {
            min-width: 1000px;
        }
        .actions {
            white-space: nowrap;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            margin: 0 2px;
        }
        .alert {
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .search-box {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 20px 0;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #007bff;
        }
        .pagination .current {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body class="metro">
    <div class="container">
        <div class="tile-group">
            <h1 class="tile-group-title">
                <i class="icon-trophy"></i>
                Gestión de Torneos
            </h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <div class="search-box">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                       placeholder="Buscar por nombre, código, deporte o categoría..." class="form-control" style="width: 300px;">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-search"></i> Buscar
                </button>
                <?php if ($searchTerm): ?>
                    <a href="?" class="btn btn-secondary">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Formulario -->
        <div class="tile">
            <h3><?php echo $editData ? 'Editar' : 'Crear Nuevo'; ?> Torneo</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editData ? 'update' : 'create'; ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="tournament_id" value="<?php echo $editData['tournament_id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="league_id">ID Liga *</label>
                        <input type="number" name="league_id" id="league_id" class="form-control" 
                               value="<?php echo $editData ? htmlspecialchars($editData['league_id']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="game_code">Código del Juego *</label>
                        <input type="text" name="game_code" id="game_code" class="form-control" 
                               value="<?php echo $editData ? htmlspecialchars($editData['game_code']) : ''; ?>" required maxlength="10">
                    </div>

                    <div class="form-group">
                        <label for="name">Nombre del Torneo *</label>
                        <input type="text" name="name" id="name" class="form-control" 
                               value="<?php echo $editData ? htmlspecialchars($editData['name']) : ''; ?>" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="season_label">Temporada *</label>
                        <input type="text" name="season_label" id="season_label" class="form-control" 
                               value="<?php echo $editData ? htmlspecialchars($editData['season_label']) : ''; ?>" required maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="start_date">Fecha de Inicio</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="<?php echo $editData && $editData['start_date'] ? $editData['start_date'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">Fecha de Fin</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="<?php echo $editData && $editData['end_date'] ? $editData['end_date'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="stage">Etapa *</label>
                        <select name="stage" id="stage" class="form-control" required>
                            <option value="">Seleccionar Etapa</option>
                            <option value="Clasificación de Grupo" <?php echo $editData && $editData['stage'] == 'Clasificación de Grupo' ? 'selected' : ''; ?>>Clasificación de Grupo</option>
                            <option value="Octavos de Final" <?php echo $editData && $editData['stage'] == 'Octavos de Final' ? 'selected' : ''; ?>>Octavos de Final</option>
                            <option value="Cuartos de Final" <?php echo $editData && $editData['stage'] == 'Cuartos de Final' ? 'selected' : ''; ?>>Cuartos de Final</option>
                            <option value="Semifinal" <?php echo $editData && $editData['stage'] == 'Semifinal' ? 'selected' : ''; ?>>Semifinal</option>
                            <option value="Final" <?php echo $editData && $editData['stage'] == 'Final' ? 'selected' : ''; ?>>Final</option>
                            <option value="Liga Regular" <?php echo $editData && $editData['stage'] == 'Liga Regular' ? 'selected' : ''; ?>>Liga Regular</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gender">Género *</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option value="">Seleccionar Género</option>
                            <option value="Masculino" <?php echo $editData && $editData['gender'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Femenino" <?php echo $editData && $editData['gender'] == 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                            <option value="Mixto" <?php echo $editData && $editData['gender'] == 'Mixto' ? 'selected' : ''; ?>>Mixto</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sport">Deporte *</label>
                        <select name="sport" id="sport" class="form-control" required>
                            <option value="">Seleccionar Deporte</option>
                            <option value="Basketball" <?php echo $editData && $editData['sport'] == 'Basketball' ? 'selected' : ''; ?>>Basketball</option>
                            <option value="Football" <?php echo $editData && $editData['sport'] == 'Football' ? 'selected' : ''; ?>>Football</option>
                            <option value="Soccer" <?php echo $editData && $editData['sport'] == 'Soccer' ? 'selected' : ''; ?>>Soccer</option>
                            <option value="Volleyball" <?php echo $editData && $editData['sport'] == 'Volleyball' ? 'selected' : ''; ?>>Volleyball</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Categoría *</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="">Seleccionar Categoría</option>
                            <option value="General" <?php echo $editData && $editData['category'] == 'General' ? 'selected' : ''; ?>>General</option>
                            <option value="Juvenil" <?php echo $editData && $editData['category'] == 'Juvenil' ? 'selected' : ''; ?>>Juvenil</option>
                            <option value="Senior" <?php echo $editData && $editData['category'] == 'Senior' ? 'selected' : ''; ?>>Senior</option>
                            <option value="Master" <?php echo $editData && $editData['category'] == 'Master' ? 'selected' : ''; ?>>Master</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="win_points">Puntos por Victoria *</label>
                        <input type="number" name="win_points" id="win_points" class="form-control" 
                               value="<?php echo $editData ? $editData['win_points'] : '3'; ?>" required min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="draw_points">Puntos por Empate *</label>
                        <input type="number" name="draw_points" id="draw_points" class="form-control" 
                               value="<?php echo $editData ? $editData['draw_points'] : '1'; ?>" required min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="loss_points">Puntos por Derrota *</label>
                        <input type="number" name="loss_points" id="loss_points" class="form-control" 
                               value="<?php echo $editData ? $editData['loss_points'] : '0'; ?>" required min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="wo_win_points">Puntos por WO Victoria *</label>
                        <input type="number" name="wo_win_points" id="wo_win_points" class="form-control" 
                               value="<?php echo $editData ? $editData['wo_win_points'] : '3'; ?>" required min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="wo_loss_points">Puntos por WO Derrota *</label>
                        <input type="number" name="wo_loss_points" id="wo_loss_points" class="form-control" 
                               value="<?php echo $editData ? $editData['wo_loss_points'] : '0'; ?>" required min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="other_details">Otros Detalles</label>
                        <textarea name="other_details" id="other_details" class="form-control" rows="3"><?php echo $editData ? htmlspecialchars($editData['other_details']) : ''; ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-success">
                        <i class="icon-checkmark"></i>
                        <?php echo $editData ? 'Actualizar' : 'Crear'; ?> Torneo
                    </button>
                    
                    <?php if ($editData): ?>
                        <a href="?" class="btn btn-secondary">
                            <i class="icon-cross"></i>
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de resultados -->
        <div class="tile">
            <h3>
                Lista de Torneos 
                <span class="badge"><?php echo $totalRegistros; ?></span>
            </h3>
            
            <div class="table-responsive">
                <table class="table striped hovered border bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Liga</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Temporada</th>
                            <th>Fechas</th>
                            <th>Etapa</th>
                            <th>Género</th>
                            <th>Deporte</th>
                            <th>Categoría</th>
                            <th>Puntos V/E/D</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tournaments)): ?>
                            <tr>
                                <td colspan="12" class="text-center">No se encontraron torneos</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tournaments as $tournament): ?>
                                <tr>
                                    <td><?php echo $tournament['tournament_id']; ?></td>
                                    <td><?php echo $tournament['league_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($tournament['game_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                                    <td><?php echo htmlspecialchars($tournament['season_label']); ?></td>
                                    <td>
                                        <?php 
                                        $start = $tournament['start_date'] ? date('d/m/Y', strtotime($tournament['start_date'])) : 'N/A';
                                        $end = $tournament['end_date'] ? date('d/m/Y', strtotime($tournament['end_date'])) : 'N/A';
                                        echo "$start - $end";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tournament['stage']); ?></td>
                                    <td><?php echo htmlspecialchars($tournament['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($tournament['sport']); ?></td>
                                    <td><?php echo htmlspecialchars($tournament['category']); ?></td>
                                    <td>
                                        <?php echo $tournament['win_points'] . '/' . $tournament['draw_points'] . '/' . $tournament['loss_points']; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $tournament['tournament_id']; ?>" 
                                           class="btn btn-info btn-sm" title="Editar">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar este torneo?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="icon-bin"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <?php if ($paginaActual > 1): ?>
                        <a href="?page=<?php echo $paginaActual - 1; ?><?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            &laquo; Anterior
                        </a>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, $paginaActual - 2);
                    $fin = min($totalPaginas, $paginaActual + 2);
                    
                    for ($i = $inicio; $i <= $fin; $i++):
                    ?>
                        <?php if ($i == $paginaActual): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?page=<?php echo $paginaActual + 1; ?><?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            Siguiente &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center" style="margin-top: 30px; padding: 20px; border-top: 1px solid #ddd;">
            <p><i class="icon-info"></i> Sistema CRUD para Gestión de Torneos - 
               Mostrando <?php echo count($tournaments); ?> de <?php echo $totalRegistros; ?> registros</p>
        </div>
    </div>

    <script src="assets/js/metro.js"></script>
    <script>
        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    
                    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
                        return false;
                    }
                });
            }
            
            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>

<?php
// Cerrar la conexión
closeConnection();
?>