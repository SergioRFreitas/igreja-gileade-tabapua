<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

if (isset($_GET['id'])) {
    try {
        $id = intval($_GET['id']);

        // Verificar se membro existe
        $stmt = $pdo->prepare("SELECT nome FROM membros WHERE id = ?");
        $stmt->execute([$id]);
        $membro = $stmt->fetch();

        if (!$membro) {
            $_SESSION['error'] = "Membro não encontrado!";
            header('Location: listar_membros.php');
            exit();
        }

        // Excluir filhos relacionados primeiro
        $pdo->prepare("DELETE FROM filhos WHERE membro_id = ?")->execute([$id]);

        // Excluir membro
        $pdo->prepare("DELETE FROM membros WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = "Membro '{$membro['nome']}' excluído com sucesso!";

    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao excluir: " . $e->getMessage();
    }
}

header('Location: listar_membros.php');
exit();
?>
