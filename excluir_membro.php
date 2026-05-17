<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = $_POST['id'];
        
        // Verificar se membro existe
        $stmt = $pdo->prepare("SELECT nome FROM membros WHERE id = ?");
        $stmt->execute([$id]);
        $membro = $stmt->fetch();
        
        if (!$membro) {
            throw new Exception("Membro não encontrado");
        }
        
        // Excluir membro
        $stmt = $pdo->prepare("DELETE FROM membros WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Membro '{$membro['nome']}' excluído com sucesso!";
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao excluir: " . $e->getMessage();
    }
    
    header('Location: listar_membros.php');
    exit();
}

// Se não for POST, redirecionar
header('Location: listar_membros.php');
exit();
?>