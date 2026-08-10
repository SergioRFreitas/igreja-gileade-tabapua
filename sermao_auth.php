<?php
// sermao_auth.php - Controle de acesso compartilhado da área de sermões
// Igreja Gileade Tabapuá
// Inclua este arquivo no topo de novo_sermao.php e editar_sermao.php
session_start();

// ⚠️ Senha única para cadastrar e editar sermões. Troque aqui - vale para as duas páginas.
define('SERMAO_ADMIN_SENHA', 'SermaoPastoral@GileadeTabapua206');

$paginaAtual = basename($_SERVER['PHP_SELF']);

// Guarda de onde a pessoa veio (ex: diretorgeral.html), para o botão "Sair" voltar para lá
if (!isset($_SESSION['sermao_origem']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
        $_SESSION['sermao_origem'] = $referer;
    }
}

if (isset($_POST['login_senha'])) {
    if ($_POST['login_senha'] === SERMAO_ADMIN_SENHA) {
        $_SESSION['sermao_autenticado'] = true;
    } else {
        $_SESSION['erro_login'] = 'Senha incorreta.';
    }
    header('Location: ' . $paginaAtual);
    exit;
}

if (isset($_GET['sair'])) {
    unset($_SESSION['sermao_autenticado']);
    $destino = $_SESSION['sermao_origem'] ?? 'index.html';
    unset($_SESSION['sermao_origem']);
    header('Location: ' . $destino);
    exit;
}

$autenticado = isset($_SESSION['sermao_autenticado']) && $_SESSION['sermao_autenticado'] === true;
