<?php
// verificar_sessao.php - Verificação de timeout de sessão
$timeout = 1800; // 30 minutos em segundos

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > $timeout) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

$_SESSION['ultimo_acesso'] = time();
?>