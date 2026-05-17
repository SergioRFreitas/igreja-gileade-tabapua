<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Conectar ao banco
$pdo = conectar_banco();

// Buscar membros
try {
    $stmt = $pdo->query("SELECT id, nome, email, telefone, endereco, data_nascimento, sexo, naturalidade, estado_civil, batismo_aguas, ministrio, funcao, situacao FROM membros ORDER BY nome");
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar membros: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Membros - Igreja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #3498db; color: white; font-weight: bold; }
        tr:hover { background-color: #f8f9fa; }
        .btn { background-color: #3498db; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
        .btn:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #95a5a6; }
        .btn-secondary:hover { background-color: #7f8c8d; }
        .btn-success { background-color: #27ae60; }
        .btn-success:hover { background-color: #229954; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .status-ativo { color: #27ae60; font-weight: bold; }
        .status-inativo { color: #e74c3c; }
        .status-visitante { color: #f39c12; }
        .actions { text-align: center; }
        .empty { text-align: center; color: #7f8c8d; font-style: italic; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-card { background: #ecf0f1; padding: 15px; border-radius: 8px; flex: 1; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>🙏 Lista de Membros da Igreja</h1>
            <div>
                <span>Logado como: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                <a href="logout.php" class="btn btn-secondary" style="margin-left: 10px;">Sair</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($membros); ?></div>
                <div class="stat-label">Total de Membros</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Ativo')); ?></div>
                <div class="stat-label">Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Transferido')); ?></div>
                <div class="stat-label">Transferidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Inativo')); ?></div>
                <div class="stat-label">Inativos</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <a href="index.php" class="btn btn-success">Novo Cadastro</a>
        </div>

        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Data Nasc.</th>
                    <th>Sexo</th>
                    <th>Estado Civil</th>
                    <th>Naturalidade</th>
                    <th>Batismo</th>
                    <th>Ministério</th>
                    <th>Função</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($membros)): ?>
                    <tr>
                        <td colspan="13" class="empty">Nenhum membro cadastrado ainda.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($membros as $membro): ?>
                        <tr>
                            <td><?php echo $membro['id']; ?></td>
                            <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                            <td><?php echo htmlspecialchars($membro['email'] ?: 'Não informado'); ?></td>
                            <td><?php echo formatar_telefone($membro['telefone']); ?></td>
                            <td><?php echo $membro['data_nascimento'] ? formatar_data($membro['data_nascimento']) : '—'; ?></td>
                            <td><?php echo htmlspecialchars($membro['sexo'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['estado_civil'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['naturalidade'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['batismo_aguas'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['ministrio'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['funcao'] ?: '—'); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($membro['situacao']); ?>">
                                    <?php echo htmlspecialchars($membro['situacao']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="editar_membro.php?id=<?php echo $membro['id']; ?>" class="btn btn-secondary">Editar</a>
                                <a href="excluir_membro.php?id=<?php echo $membro['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este membro?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</body>
</html>