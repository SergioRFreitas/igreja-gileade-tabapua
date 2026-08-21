<?php
// editar_sermao.php - Lista e edita sermões cadastrados (área protegida)
// Igreja Gileade Tabapuá
require_once 'config.php';
require_once 'sermao_auth.php';
require_once 'upload_imagem_sermao.php';

$pdo = conectar_banco();

// ── Salvar edição ──
if ($autenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id           = (int)$_POST['id'];
    $titulo       = trim($_POST['titulo'] ?? '');
    $preletor     = trim($_POST['preletor'] ?? '');
    $texto_base   = trim($_POST['texto_base'] ?? '');
    $data_sermao  = trim($_POST['data_sermao'] ?? '');
    $resumo       = trim($_POST['resumo'] ?? '');
    $imagem_fundo = trim($_POST['imagem_fundo'] ?? '');
    $conteudo     = trim($_POST['conteudo'] ?? '');

    // Se um novo arquivo foi enviado, ele substitui o caminho atual
    try {
        $caminhoUpload = processarUploadImagemSermao($_FILES['imagem_arquivo'] ?? []);
        if ($caminhoUpload !== null) {
            $imagem_fundo = $caminhoUpload;
        }
    } catch (RuntimeException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: editar_sermao.php?editar=' . $id);
        exit;
    }

    if ($titulo === '' || $preletor === '' || $data_sermao === '' || $conteudo === '') {
        $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE sermoes SET titulo=?, preletor=?, texto_base=?, data_sermao=?, resumo=?, imagem_fundo=?, conteudo=? WHERE id=?"
            );
            $stmt->execute([$titulo, $preletor, $texto_base, $data_sermao, $resumo, $imagem_fundo, $conteudo, $id]);
            $_SESSION['success'] = 'Sermão atualizado com sucesso!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
        }
    }
    header('Location: editar_sermao.php');
    exit;
}

// ── Excluir ──
if ($autenticado && isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM sermoes WHERE id = ?");
    $stmt->execute([$_GET['excluir']]);
    $_SESSION['success'] = 'Sermão excluído.';
    header('Location: editar_sermao.php');
    exit;
}

// ── Dados para exibir ──
$sermaoEditando = null;
if ($autenticado && isset($_GET['editar']) && ctype_digit($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM sermoes WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $sermaoEditando = $stmt->fetch();
}

$listaSermoes = [];
if ($autenticado && !$sermaoEditando) {
    $listaSermoes = $pdo->query("SELECT id, titulo, preletor, data_sermao FROM sermoes ORDER BY data_sermao DESC, id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Sermões - Igreja Gileade</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 10px; background-color: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; text-align: center; font-size: clamp(1.2rem, 5vw, 1.8rem); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; font-size: 14px; }
    input[type="text"], input[type="date"], input[type="password"], textarea {
        width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;
    }
    textarea { min-height: 220px; font-family: inherit; }
    .btn { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px 5px 5px 0; display: inline-block; text-decoration: none; }
    .btn:hover { background-color: #2980b9; }
    .btn-secondary { background-color: #95a5a6; }
    .btn-danger { background-color: #e74c3c; }
    .btn-sm { padding: 6px 12px; font-size: 14px; }
    .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .dica { font-size: 13px; color: #7f8c8d; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
    th { color: #7f8c8d; text-transform: uppercase; font-size: 12px; }
    .acoes { white-space: nowrap; }
    @media (max-width: 600px) {
        .container { padding: 15px; border-radius: 5px; }
        .btn { width: 100%; text-align: center; margin: 5px 0; }
        table, thead, tbody, th, td, tr { display: block; }
        thead { display: none; }
        tr { margin-bottom: 14px; border-bottom: 2px solid #eee; }
        td { border: none; padding: 4px 0; }
        td::before { content: attr(data-label); font-weight: bold; display: block; font-size: 12px; color: #7f8c8d; }
        .acoes { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
        .acoes .btn { display: block; width: 100%; margin: 0; }
    }
</style>
</head>
<body>
<div class="container">

<?php if (!$autenticado): ?>

    <h1>🔒 Editar Sermões</h1>
    <?php if (isset($_SESSION['erro_login'])): ?>
        <div class="error"><?= $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="login_senha">Senha de acesso</label>
            <div style="position: relative;">
                <input type="password" id="login_senha" name="login_senha" required autofocus style="padding-right: 42px;">
                <button type="button" onclick="alternarSenha()" aria-label="Mostrar ou ocultar senha"
                        style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; padding: 4px 8px;">
                    👁️
                </button>
            </div>
        </div>
        <button type="submit" class="btn">Entrar</button>
    </form>
    <script>
        function alternarSenha() {
            const campo = document.getElementById('login_senha');
            campo.type = campo.type === 'password' ? 'text' : 'password';
        }
    </script>

<?php elseif ($sermaoEditando): ?>

    <div class="top-bar">
        <h1>✏️ Editar Sermão</h1>
        <a href="editar_sermao.php" class="btn btn-secondary btn-sm">&larr; Voltar à lista</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $sermaoEditando['id'] ?>">

        <div class="form-group">
            <label for="titulo">Título / Tema do sermão <span style="color:#e74c3c">*</span></label>
            <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($sermaoEditando['titulo']) ?>">
        </div>

        <div class="form-group">
            <label for="preletor">Pregador/Preletor <span style="color:#e74c3c">*</span></label>
            <input type="text" id="preletor" name="preletor" required value="<?= htmlspecialchars($sermaoEditando['preletor']) ?>">
        </div>

        <div class="form-group">
            <label for="texto_base">Texto base (referência bíblica)</label>
            <input type="text" id="texto_base" name="texto_base" value="<?= htmlspecialchars($sermaoEditando['texto_base']) ?>">
        </div>

        <div class="form-group">
            <label for="data_sermao">Data do sermão <span style="color:#e74c3c">*</span></label>
            <input type="date" id="data_sermao" name="data_sermao" required value="<?= htmlspecialchars($sermaoEditando['data_sermao']) ?>">
        </div>

        <div class="form-group">
            <label for="resumo">Resumo curto</label>
            <textarea id="resumo" name="resumo" style="min-height:80px;"><?= htmlspecialchars($sermaoEditando['resumo']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="imagem_fundo_atual">Imagem de fundo atual</label>
            <?php if (!empty($sermaoEditando['imagem_fundo'])): ?>
                <div class="dica" style="margin-bottom:6px;">📷 <?= htmlspecialchars($sermaoEditando['imagem_fundo']) ?></div>
            <?php else: ?>
                <div class="dica" style="margin-bottom:6px;">Nenhuma - usando o fundo padrão (gradiente).</div>
            <?php endif; ?>
            <input type="file" id="imagem_arquivo" name="imagem_arquivo" accept=".jpg,.jpeg,.png,.webp">
            <div class="dica">Escolha uma nova foto para substituir a atual (JPG, PNG ou WEBP, até 5MB). Deixe em branco para manter a imagem/fundo atual.</div>
        </div>

        <div class="form-group">
            <label for="imagem_fundo">Ou, se preferir, caminho de uma imagem já existente no site</label>
            <input type="text" id="imagem_fundo" name="imagem_fundo" value="<?= htmlspecialchars($sermaoEditando['imagem_fundo']) ?>">
            <div class="dica">Só mexa aqui se NÃO for escolher um arquivo acima.</div>
        </div>

        <div class="form-group">
            <label for="conteudo">Conteúdo completo do sermão <span style="color:#e74c3c">*</span></label>
            <textarea id="conteudo" name="conteudo" required><?= htmlspecialchars($sermaoEditando['conteudo']) ?></textarea>
            <div class="dica">O texto está em HTML (tags &lt;p&gt;, &lt;h3&gt;, &lt;blockquote&gt;). Edite com cuidado para não quebrar as tags.</div>
        </div>

        <button type="submit" class="btn">Salvar Alterações</button>
        <a href="editar_sermao.php" class="btn btn-secondary">Cancelar</a>
    </form>

<?php else: ?>

    <div class="top-bar">
        <h1>📋 Sermões Cadastrados</h1>
        <div>
            <a href="novo_sermao.php" class="btn btn-sm">+ Novo Sermão</a>
            <a href="editar_sermao.php?sair=1" class="btn btn-secondary btn-sm">Sair</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($listaSermoes)): ?>
        <p>Nenhum sermão cadastrado ainda.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Pregador/Preletor</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaSermoes as $s): ?>
                <tr>
                    <td data-label="Título"><?= htmlspecialchars($s['titulo']) ?></td>
                    <td data-label="Pregador/Preletor"><?= htmlspecialchars($s['preletor']) ?></td>
                    <td data-label="Data"><?= formatar_data($s['data_sermao']) ?></td>
                    <td data-label="Ações" class="acoes">
                        <a href="editar_sermao.php?editar=<?= $s['id'] ?>" class="btn btn-sm">Editar</a>
                        <a href="sermao.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary" target="_blank">Ver</a>
                        <a href="editar_sermao.php?excluir=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Tem certeza que deseja excluir este sermão? Essa ação não pode ser desfeita.');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
