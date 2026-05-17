<?php
// get_ministerios.php - API para carregar ministérios

require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = conectar_banco();
    
    $stmt = $pdo->query("SELECT nome, descricao FROM ministerios WHERE ativo = 1 ORDER BY nome");
    $ministerios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($ministerios);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>