<?php
require_once 'Connections/Connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Team_Matches WHERE match_id = ?");
    $stmt->execute([$_GET['id']]);
    $match = $stmt->fetch();
    
    if ($match) {
        echo json_encode(['success' => true, 'match' => $match]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Partido no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
