<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

$mensagem = '';
$erro = '';
$editando = null;

// Ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'adicionar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        if (empty($nome) || empty($email) || empty($senha)) {
            $erro = 'Preencha todos os campos!';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $email, $senha]);
                $mensagem = "Usuário '$nome' cadastrado com sucesso!";
            } catch (Exception $e) {
                $erro = 'Email já cadastrado!';
            }
        }
    } elseif ($_POST['acao'] === 'editar') {
        $id = $_POST['id'];
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        if (empty($nome) || empty($email)) {
            $erro = 'Nome e email são obrigatórios!';
        } else {
            try {
                if (!empty($senha)) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, senha=? WHERE id=?");
                    $stmt->execute([$nome, $email, $senha, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=? WHERE id=?");
                    $stmt->execute([$nome, $email, $id]);
                }
                $mensagem = "Usuário '$nome' atualizado com sucesso!";
            } catch (Exception $e) {
                $erro = 'Email já cadastrado por outro usuário!';
            }
        }
    } elseif ($_POST['acao'] === 'toggle') {
        $id = $_POST['id'];
        $pdo->prepare("UPDATE usuarios SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$id]);
        $mensagem = 'Status atualizado!';
    } elseif ($_POST['acao'] === 'excluir') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
        $mensagem = 'Usuário removido!';
    }
}

// Carregar usuário para edição
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Usuários - Sistema Igreja</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 10px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; font-size: clamp(1.1rem, 4vw, 1.6rem); }
        h3 { color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; }
        .btn { background-color: #3498db; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-success { background-color: #27ae60; }
        .btn-danger { background-color: #e74c3c; }
        .btn-warning { background-color: #f39c12; }
        .btn-secondary { background-color: #95a5a6; }
        .btn-edit { background-color: #3498db; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #2c3e50; color: white; }
        .ativo { color: #27ae60; font-weight: bold; }
        .inativo { color: #e74c3c; font-weight: bold; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .card { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .card-edit { background: #eaf4fb; border: 2px solid #3498db; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .row { display: flex; gap: 15px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 200px; }
        @media (max-width: 600px) { .row { flex-direction: column; } .col { min-width: 100%; } .btn { width: 100%; margin: 3px 0; } }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>👥 Gerenciar Usuários</h1>
        <a href="listar_membros.php" class="btn btn-secondary">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><div class="success"><?php echo $mensagem; ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="error"><?php echo $erro; ?></div><?php endif; ?>

    <?php if ($editando): ?>
    <!-- Formulário de edição -->
    <div class="card-edit">
        <h3>✏️ Editar Usuário</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($editando['nome']); ?>" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($editando['email']); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="text" name="senha" placeholder="Nova senha">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
            <a href="gerenciar_usuarios.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <?php else: ?>
    <!-- Formulário de cadastro -->
    <div class="card">
        <h3>➕ Novo Usuário</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="adicionar">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" placeholder="Nome completo">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@exemplo.com">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="text" name="senha" placeholder="Senha de acesso">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Cadastrar Usuário</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Lista de usuários -->
    <h3>👤 Usuários Cadastrados</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Senha</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['nome']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['senha']); ?></td>
                    <td class="<?php echo $u['ativo'] ? 'ativo' : 'inativo'; ?>">
                        <?php echo $u['ativo'] ? '✅ Ativo' : '❌ Inativo'; ?>
                    </td>
                    <td>
                        <a href="gerenciar_usuarios.php?editar=<?php echo $u['id']; ?>" class="btn btn-edit">✏️ Editar</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="acao" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-warning"><?php echo $u['ativo'] ? 'Desativar' : 'Ativar'; ?></button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remover este usuário?')">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-danger">Remover</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
