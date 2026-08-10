<?php
// upload_imagem_sermao.php - Processa o upload de imagem de fundo do sermão
// Igreja Gileade Tabapuá
// Inclua este arquivo em salvar_sermao.php e editar_sermao.php

/**
 * Processa o upload de uma imagem enviada via formulário e move para imagens/sermoes/.
 * Retorna o caminho relativo (ex: "imagens/sermoes/1691000000_foto.jpg") em caso de sucesso,
 * null se nenhum arquivo foi enviado, ou lança uma exceção com a mensagem de erro.
 */
function processarUploadImagemSermao(array $arquivo): ?string {
    // Nenhum arquivo selecionado - não é erro, só não há upload
    if (!isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro ao enviar a imagem (código ' . $arquivo['error'] . ').');
    }

    // Limite de 5MB
    $tamanhoMaximo = 5 * 1024 * 1024;
    if ($arquivo['size'] > $tamanhoMaximo) {
        throw new RuntimeException('A imagem é muito grande. O limite é 5MB.');
    }

    // Confere o tipo real do arquivo (não confia só na extensão)
    $extensoesPermitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if (!array_key_exists($extensao, $extensoesPermitidas)) {
        throw new RuntimeException('Formato não permitido. Use JPG, PNG ou WEBP.');
    }

    $tipoReal = mime_content_type($arquivo['tmp_name']);
    if ($tipoReal !== $extensoesPermitidas[$extensao] && !($extensao === 'jpg' && $tipoReal === 'image/jpeg')) {
        throw new RuntimeException('O arquivo enviado não parece ser uma imagem válida.');
    }

    // Pasta de destino: imagens/sermoes/ (cria se não existir)
    $pastaDestino = __DIR__ . '/imagens/sermoes';
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    // Nome de arquivo único e seguro
    $nomeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($arquivo['name'], PATHINFO_FILENAME));
    $nomeArquivo = time() . '_' . substr($nomeBase, 0, 40) . '.' . $extensao;
    $caminhoCompleto = $pastaDestino . '/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        throw new RuntimeException('Não foi possível salvar a imagem no servidor.');
    }

    return 'imagens/sermoes/' . $nomeArquivo;
}
