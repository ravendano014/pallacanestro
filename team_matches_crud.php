<?php
require_once 'Connections/Connection.php';

// Función para obtener todos los team matches
function getTeamMatches() {
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
        ORDER BY tm.match_id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener un team match específico
function getTeamMatch($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Team_Matches WHERE match_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para obtener equipos
function getTeams() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT team_id, team_name FROM Teams ORDER BY team_name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener torneos
function getTournaments() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT tournament_id, name FROM Tournaments ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener canchas
function getCourts() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT court_id, court_name FROM Basketball_Courts ORDER BY court_name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Procesar formularios
$message = '';
$messageType = '';

if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $stmt = $pdo->prepare("
                        INSERT INTO Team_Matches (tournament_id, jornada, juego, phase, start_datetime, 
                                                home_team_id, away_team_id, home_score, away_score, 
                                                is_bye, bye_team_id, court_id, status, walkover_winner, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['tournament_id'],
                        $_POST['jornada'],
                        $_POST['juego'],
                        $_POST['phase'],
                        !empty($_POST['start_datetime']) ? $_POST['start_datetime'] : null,
                        $_POST['home_team_id'],
                        $_POST['away_team_id'],
                        !empty($_POST['home_score']) ? $_POST['home_score'] : null,
                        !empty($_POST['away_score']) ? $_POST['away_score'] : null,
                        $_POST['is_bye'],
                        !empty($_POST['bye_team_id']) ? $_POST['bye_team_id'] : null,
                        $_POST['court_id'],
                        $_POST['status'],
                        !empty($_POST['walkover_winner']) ? $_POST['walkover_winner'] : null,
                        !empty($_POST['notes']) ? $_POST['notes'] : null
                    ]);
                    $message = 'Partido creado exitosamente';
                    $messageType = 'success';
                    break;

                case 'update':
                    $stmt = $pdo->prepare("
                        UPDATE Team_Matches SET 
                            tournament_id = ?, jornada = ?, juego = ?, phase = ?, start_datetime = ?,
                            home_team_id = ?, away_team_id = ?, home_score = ?, away_score = ?,
                            is_bye = ?, bye_team_id = ?, court_id = ?, status = ?, walkover_winner = ?, notes = ?
                        WHERE match_id = ?
                    ");
                    $stmt->execute([
                        $_POST['tournament_id'],
                        $_POST['jornada'],
                        $_POST['juego'],
                        $_POST['phase'],
                        !empty($_POST['start_datetime']) ? $_POST['start_datetime'] : null,
                        $_POST['home_team_id'],
                        $_POST['away_team_id'],
                        !empty($_POST['home_score']) ? $_POST['home_score'] : null,
                        !empty($_POST['away_score']) ? $_POST['away_score'] : null,
                        $_POST['is_bye'],
                        !empty($_POST['bye_team_id']) ? $_POST['bye_team_id'] : null,
                        $_POST['court_id'],
                        $_POST['status'],
                        !empty($_POST['walkover_winner']) ? $_POST['walkover_winner'] : null,
                        !empty($_POST['notes']) ? $_POST['notes'] : null,
                        $_POST['match_id']
                    ]);
                    $message = 'Partido actualizado exitosamente';
                    $messageType = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM Team_Matches WHERE match_id = ?");
                    $stmt->execute([$_POST['match_id']]);
                    $message = 'Partido eliminado exitosamente';
                    $messageType = 'success';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'alert';
    }
}

// Obtener datos
$teamMatches = getTeamMatches();
$teams = getTeams();
$tournaments = getTournaments();
$courts = getCourts();

// Si se está editando, obtener los datos del partido
$editMatch = null;
if (isset($_GET['edit'])) {
    $editMatch = getTeamMatch($_GET['edit']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Partidos - Team Matches</title>
    <link rel="stylesheet" href="metro.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group.full-width {
            flex: 100%;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .match-header {
            font-weight: bold;
            color: #2c3e50;
        }
        .score {
            font-weight: bold;
            font-size: 1.1em;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-scheduled {
            background: #3498db;
            color: white;
        }
        .status-finished {
            background: #27ae60;
            color: white;
        }
        .status-cancelled {
            background: #e74c3c;
            color: white;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Gestión de Partidos - Team Matches</h1>

        <?php if ($message): ?>
            <div class="toast <?php echo $messageType; ?>" data-role="toast" data-timeout="5000">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="form-container">
            <h3><?php echo $editMatch ? 'Editar Partido' : 'Nuevo Partido'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editMatch ? 'update' : 'create'; ?>">
                <?php if ($editMatch): ?>
                    <input type="hidden" name="match_id" value="<?php echo $editMatch['match_id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tournament_id">Torneo:</label>
                        <select name="tournament_id" data-role="select" required>
                            <option value="">Seleccionar torneo</option>
                            <?php foreach ($tournaments as $tournament): ?>
                                <option value="<?php echo $tournament['tournament_id']; ?>"
                                    <?php echo ($editMatch && $editMatch['tournament_id'] == $tournament['tournament_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tournament['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jornada">Jornada:</label>
                        <input type="number" name="jornada" data-role="input" required
                               value="<?php echo $editMatch ? $editMatch['jornada'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="juego">Juego:</label>
                        <input type="number" name="juego" data-role="input" required
                               value="<?php echo $editMatch ? $editMatch['juego'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="phase">Fase:</label>
                        <select name="phase" data-role="select" required>
                            <option value="">Seleccionar fase</option>
                            <option value="IDA" <?php echo ($editMatch && $editMatch['phase'] == 'IDA') ? 'selected' : ''; ?>>IDA</option>
                            <option value="VUELTA" <?php echo ($editMatch && $editMatch['phase'] == 'VUELTA') ? 'selected' : ''; ?>>VUELTA</option>
                            <option value="PLAYOFF" <?php echo ($editMatch && $editMatch['phase'] == 'PLAYOFF') ? 'selected' : ''; ?>>PLAYOFF</option>
                            <option value="FINAL" <?php echo ($editMatch && $editMatch['phase'] == 'FINAL') ? 'selected' : ''; ?>>FINAL</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_datetime">Fecha y Hora:</label>
                        <input type="datetime-local" name="start_datetime" data-role="input"
                               value="<?php echo $editMatch && $editMatch['start_datetime'] ? date('Y-m-d\TH:i', strtotime($editMatch['start_datetime'])) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="court_id">Cancha:</label>
                        <select name="court_id" data-role="select" required>
                            <option value="">Seleccionar cancha</option>
                            <?php foreach ($courts as $court): ?>
                                <option value="<?php echo $court['court_id']; ?>"
                                    <?php echo ($editMatch && $editMatch['court_id'] == $court['court_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($court['court_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Estado:</label>
                        <select name="status" data-role="select" required>
                            <option value="SCHEDULED" <?php echo ($editMatch && $editMatch['status'] == 'SCHEDULED') ? 'selected' : ''; ?>>PROGRAMADO</option>
                            <option value="IN_PROGRESS" <?php echo ($editMatch && $editMatch['status'] == 'IN_PROGRESS') ? 'selected' : ''; ?>>EN PROGRESO</option>
                            <option value="FINISHED" <?php echo ($editMatch && $editMatch['status'] == 'FINISHED') ? 'selected' : ''; ?>>FINALIZADO</option>
                            <option value="CANCELLED" <?php echo ($editMatch && $editMatch['status'] == 'CANCELLED') ? 'selected' : ''; ?>>CANCELADO</option>
                            <option value="POSTPONED" <?php echo ($editMatch && $editMatch['status'] == 'POSTPONED') ? 'selected' : ''; ?>>POSPUESTO</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="home_team_id">Equipo Local:</label>
                        <select name="home_team_id" data-role="select" required>
                            <option value="">Seleccionar equipo local</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['team_id']; ?>"
                                    <?php echo ($editMatch && $editMatch['home_team_id'] == $team['team_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="away_team_id">Equipo Visitante:</label>
                        <select name="away_team_id" data-role="select" required>
                            <option value="">Seleccionar equipo visitante</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['team_id']; ?>"
                                    <?php echo ($editMatch && $editMatch['away_team_id'] == $team['team_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="home_score">Puntos Local:</label>
                        <input type="number" name="home_score" data-role="input" min="0"
                               value="<?php echo $editMatch ? $editMatch['home_score'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="away_score">Puntos Visitante:</label>
                        <input type="number" name="away_score" data-role="input" min="0"
                               value="<?php echo $editMatch ? $editMatch['away_score'] : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="is_bye">Es Bye:</label>
                        <select name="is_bye" data-role="select">
                            <option value="0" <?php echo ($editMatch && $editMatch['is_bye'] == '0') ? 'selected' : ''; ?>>No</option>
                            <option value="1" <?php echo ($editMatch && $editMatch['is_bye'] == '1') ? 'selected' : ''; ?>>Sí</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bye_team_id">Equipo con Bye:</label>
                        <select name="bye_team_id" data-role="select">
                            <option value="">Ninguno</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['team_id']; ?>"
                                    <?php echo ($editMatch && $editMatch['bye_team_id'] == $team['team_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="walkover_winner">Ganador por W.O.:</label>
                        <select name="walkover_winner" data-role="select">
                            <option value="">Ninguno</option>
                            <option value="HOME" <?php echo ($editMatch && $editMatch['walkover_winner'] == 'HOME') ? 'selected' : ''; ?>>Local</option>
                            <option value="AWAY" <?php echo ($editMatch && $editMatch['walkover_winner'] == 'AWAY') ? 'selected' : ''; ?>>Visitante</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="notes">Notas:</label>
                        <textarea name="notes" data-role="textarea" rows="3"><?php echo $editMatch ? htmlspecialchars($editMatch['notes']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="button primary">
                        <?php echo $editMatch ? 'Actualizar Partido' : 'Crear Partido'; ?>
                    </button>
                    <?php if ($editMatch): ?>
                        <a href="team_matches_crud.php" class="button secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de partidos -->
        <div class="table-container">
            <h3 style="padding: 20px; margin: 0; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                Lista de Partidos
            </h3>
            <div class="table-responsive">
                <table class="table striped hovered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Torneo</th>
                            <th>Jornada</th>
                            <th>Juego</th>
                            <th>Fase</th>
                            <th>Fecha/Hora</th>
                            <th>Partido</th>
                            <th>Resultado</th>
                            <th>Cancha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teamMatches as $match): ?>
                            <tr>
                                <td><?php echo $match['match_id']; ?></td>
                                <td><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                                <td><?php echo $match['jornada']; ?></td>
                                <td><?php echo $match['juego']; ?></td>
                                <td>
                                    <span class="tag"><?php echo $match['phase']; ?></span>
                                </td>
                                <td>
                                    <?php echo $match['start_datetime'] ? date('d/m/Y H:i', strtotime($match['start_datetime'])) : 'Por definir'; ?>
                                </td>
                                <td class="match-header">
                                    <?php if ($match['is_bye'] == 1): ?>
                                        <span class="tag warning">BYE</span> 
                                        <?php echo htmlspecialchars($match['home_team_name'] ?? 'TBD'); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($match['home_team_name'] ?? 'TBD'); ?> 
                                        <strong>vs</strong> 
                                        <?php echo htmlspecialchars($match['away_team_name'] ?? 'TBD'); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="score">
                                    <?php if ($match['home_score'] !== null && $match['away_score'] !== null): ?>
                                        <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                        <?php if ($match['walkover_winner']): ?>
                                            <span class="tag alert">W.O.</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($match['court_name'] ?? 'Sin asignar'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($match['status']); ?>">
                                        <?php 
                                        $statusLabels = [
                                            'SCHEDULED' => 'Programado',
                                            'IN_PROGRESS' => 'En Progreso',
                                            'FINISHED' => 'Finalizado',
                                            'CANCELLED' => 'Cancelado',
                                            'POSTPONED' => 'Pospuesto'
                                        ];
                                        echo $statusLabels[$match['status']] ?? $match['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $match['match_id']; ?>" class="button small success">
                                            Editar
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('¿Está seguro de eliminar este partido?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                            <button type="submit" class="button small alert">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="metro.js"></script>
    <script>
        // Inicializar componentes Metro UI después de que se cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar todos los componentes
            Metro.init();
            
            // Auto-ocultar mensajes toast después de 5 segundos
            setTimeout(function() {
                const toasts = document.querySelectorAll('.toast');
                toasts.forEach(function(toast) {
                    if (toast) {
                        toast.style.display = 'none';
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>
