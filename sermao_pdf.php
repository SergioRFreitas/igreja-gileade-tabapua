<?php
// sermao_pdf.php - Versão para impressão / "Salvar como PDF" do sermão
// Igreja Gileade Tabapuá
require_once 'config.php';

$pdo = conectar_banco();

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM sermoes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
} else {
    $stmt = $pdo->query("SELECT * FROM sermoes ORDER BY data_sermao DESC, id DESC LIMIT 1");
}
$sermao = $stmt->fetch();

if (!$sermao) {
    http_response_code(404);
    die("Sermão não encontrado.");
}

$dataFormatada = formatar_data($sermao['data_sermao']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($sermao['titulo']) ?></title>
<style>
    body {
        font-family: Georgia, 'Times New Roman', serif;
        max-width: 720px;
        margin: 40px auto;
        color: #222;
        line-height: 1.7;
        padding: 0 20px;
    }
    .cabecalho {
        text-align: center;
        border-bottom: 2px solid #2c3e50;
        padding-bottom: 16px;
        margin-bottom: 28px;
    }
    .cabecalho .igreja {
        font-size: 13px;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #7f8c8d;
        margin-bottom: 6px;
    }
    h1 { font-size: 26px; color: #2c3e50; margin: 6px 0; }
    .info { font-size: 14px; color: #555; }
    .texto-base { font-style: italic; color: #555; margin-top: 4px; }
    h3 { font-size: 18px; color: #2c3e50; margin-top: 26px; }
    blockquote {
        border-left: 3px solid #3498db;
        margin: 16px 0;
        padding: 8px 16px;
        font-style: italic;
        color: #333;
        background: #f7f9fb;
    }
    p { margin-bottom: 12px; text-align: justify; }

    .botao-imprimir {
        text-align: center;
        margin-bottom: 30px;
    }
    .botao-imprimir button {
        background: #2c3e50;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
    }
    .botao-imprimir .dica {
        font-size: 12px;
        color: #888;
        margin-top: 6px;
    }

    @media print {
        .botao-imprimir { display: none; }
        body { margin: 0; max-width: 100%; }
    }
</style>
</head>
<body>

<div class="botao-imprimir">
    <button onclick="window.print()">🖨️ Salvar como PDF / Imprimir</button>
    <div class="dica">Na janela de impressão, escolha "Salvar como PDF" no destino/impressora.</div>
</div>

<div class="cabecalho">
    <div class="igreja">Igreja Cristã Gileade Tabapuá</div>
    <h1><?= htmlspecialchars($sermao['titulo']) ?></h1>
    <div class="info">Preletor: <?= htmlspecialchars($sermao['preletor']) ?> &middot; <?= $dataFormatada ?></div>
    <?php if (!empty($sermao['texto_base'])): ?>
        <div class="texto-base"><?= htmlspecialchars($sermao['texto_base']) ?></div>
    <?php endif; ?>
</div>

<?= $sermao['conteudo'] ?>

</body>
</html>
