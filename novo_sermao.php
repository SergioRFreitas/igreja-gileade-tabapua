<?php
// novo_sermao.php - Cadastro do sermão pastoral da semana (área protegida)
// Igreja Gileade Tabapuá
require_once 'config.php';
require_once 'sermao_auth.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastrar Sermão - Igreja Gileade</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 10px; background-color: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
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
    .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .dica { font-size: 13px; color: #7f8c8d; margin-top: 4px; }
    @media (max-width: 600px) {
        .container { padding: 15px; border-radius: 5px; }
        .btn { width: 100%; text-align: center; margin: 5px 0; }
    }
</style>
</head>
<body>
<div class="container">

<?php if (!$autenticado): ?>

    <h1>🔒 Área do Sermão Pastoral</h1>
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

<?php else: ?>

    <div class="top-bar">
        <h1>📖 Cadastrar Sermão da Semana</h1>
        <div>
            <a href="editar_sermao.php" class="btn btn-secondary">Ver / Editar sermões</a>
            <a href="novo_sermao.php?sair=1" class="btn btn-secondary">Sair</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="salvar_sermao.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título / Tema do sermão <span style="color:#e74c3c">*</span></label>
            <input type="text" id="titulo" name="titulo" required placeholder="Ex: A Difícil Tarefa de Prosseguir para o Alvo">
        </div>

        <div class="form-group">
            <label for="preletor">Pregador/Preletor <span style="color:#e74c3c">*</span></label>
            <input type="text" id="preletor" name="preletor" required placeholder="Nome de quem pregou">
        </div>

        <div class="form-group">
            <label for="texto_base">Texto base (referência bíblica)</label>
            <input type="text" id="texto_base" name="texto_base" placeholder="Ex: Filipenses 3:12-14">
        </div>

        <div class="form-group">
            <label for="data_sermao">Data do sermão <span style="color:#e74c3c">*</span></label>
            <input type="date" id="data_sermao" name="data_sermao" required value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="resumo">Resumo curto (aparece na chamada, se você usar)</label>
            <textarea id="resumo" name="resumo" style="min-height:80px;" placeholder="Uma ou duas frases sobre a mensagem"></textarea>
        </div>

        <div class="form-group">
            <label for="imagem_arquivo">Imagem de fundo (opcional)</label>
            <input type="file" id="imagem_arquivo" name="imagem_arquivo" accept=".jpg,.jpeg,.png,.webp">
            <div class="dica">Escolha uma foto do seu computador/celular (JPG, PNG ou WEBP, até 5MB). Deixe em branco para usar o fundo padrão.</div>
        </div>

        <div class="form-group">
            <label for="imagem_fundo">Ou, se preferir, caminho de uma imagem já existente no site</label>
            <input type="text" id="imagem_fundo" name="imagem_fundo" placeholder="imagens/nome-do-arquivo.jpg">
            <div class="dica">Só preencha isso se NÃO escolher um arquivo acima. Se os dois forem preenchidos, o arquivo escolhido acima tem prioridade.</div>
        </div>

        <div class="form-group">
            <label for="conteudo">Conteúdo completo do sermão <span style="color:#e74c3c">*</span></label>
            <textarea id="conteudo" name="conteudo" required placeholder="Cole aqui o texto do sermão. Pode usar &lt;p&gt;...&lt;/p&gt; para separar parágrafos."></textarea>
            <div class="dica">Dica: separe os parágrafos com uma linha em branco, ou use tags &lt;p&gt;&lt;/p&gt; — o sistema mantém a formatação.</div>
        </div>

        <button type="submit" class="btn">Salvar Sermão</button>
        <a href="index.html" class="btn btn-secondary">Cancelar</a>
    </form>

<?php endif; ?>

</div>
</body>
</html>
