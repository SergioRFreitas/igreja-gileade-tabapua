<?php
// salvar_sermao.php - Grava o sermão da semana no banco (mantém todos os anteriores)
// Igreja Gileade Tabapuá
session_start();
require_once 'config.php';
require_once 'upload_imagem_sermao.php';

if (!isset($_SESSION['sermao_autenticado']) || $_SESSION['sermao_autenticado'] !== true) {
    header('Location: novo_sermao.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: novo_sermao.php');
    exit;
}

$titulo       = trim($_POST['titulo'] ?? '');
$preletor     = trim($_POST['preletor'] ?? '');
$texto_base   = trim($_POST['texto_base'] ?? '');
$data_sermao  = trim($_POST['data_sermao'] ?? '');
$resumo       = trim($_POST['resumo'] ?? '');
$imagem_fundo = trim($_POST['imagem_fundo'] ?? '');
$conteudo_raw = trim($_POST['conteudo'] ?? '');

if ($titulo === '' || $preletor === '' || $data_sermao === '' || $conteudo_raw === '') {
    $_SESSION['error'] = 'Preencha todos os campos obrigatórios (título, preletor, data e conteúdo).';
    header('Location: novo_sermao.php');
    exit;
}

// Se um arquivo foi enviado, ele tem prioridade sobre o caminho digitado
try {
    $caminhoUpload = processarUploadImagemSermao($_FILES['imagem_arquivo'] ?? []);
    if ($caminhoUpload !== null) {
        $imagem_fundo = $caminhoUpload;
    }
} catch (RuntimeException $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: novo_sermao.php');
    exit;
}


// Se o texto colado ainda não tiver tags <p>, transforma quebras de linha duplas em parágrafos
if (stripos($conteudo_raw, '<p') === false) {
    $blocos = preg_split('/\r?\n\r?\n/', $conteudo_raw);
    $conteudo = '';
    foreach ($blocos as $bloco) {
        $bloco = trim($bloco);
        if ($bloco === '') continue;
        $conteudo .= '<p>' . nl2br(htmlspecialchars($bloco)) . '</p>' . "\n";
    }
} else {
    // Já veio formatado com HTML - mantém como está
    $conteudo = $conteudo_raw;
}

try {
    $pdo = conectar_banco();
    $stmt = $pdo->prepare(
        "INSERT INTO sermoes (titulo, preletor, texto_base, data_sermao, resumo, conteudo, imagem_fundo)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$titulo, $preletor, $texto_base, $data_sermao, $resumo, $conteudo, $imagem_fundo]);

    $_SESSION['success'] = 'Sermão cadastrado com sucesso! Ele já está disponível em sermao.php.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
}

header('Location: novo_sermao.php');
exit;
