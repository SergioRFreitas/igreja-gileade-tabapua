<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

try {
    $stmt = $pdo->query("SELECT id, nome, email, telefone, data_nascimento, sexo, naturalidade, estado_civil, batismo_aguas, ministrio, funcao, situacao FROM membros ORDER BY nome");
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
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <title>Lista de Membros - Igreja</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            margin: 0; padding: 10px; background: #f0f2f5;
        }
        .container {
            max-width: 1200px; margin: 0 auto; background: white;
            padding: 16px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        h1 {
            color: #2c3e50; text-align: center; font-size: 1.4rem;
            margin: 0 0 16px;
        }
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 10px; margin-bottom: 16px;
        }
        .user-info {
            font-size: 13px; color: #555; display: flex; gap: 8px; align-items: center;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 8px; margin-bottom: 18px;
        }
        .stat-card {
            background: #f7f9fc; padding: 12px 8px; border-radius: 10px;
            text-align: center; border: 1px solid #eef1f5;
        }
        .stat-number { font-size: 22px; font-weight: 700; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 11px; margin-top: 2px; }
        .btn {
            background: #3498db; color: white; padding: 10px 18px;
            text-decoration: none; border-radius: 8px; display: inline-block;
            font-size: 14px; border: none; cursor: pointer; text-align: center;
            transition: background .2s; -webkit-tap-highlight-color: transparent;
        }
        .btn:hover { background: #2980b9; }
        .btn-secondary { background: #7f8c8d; }
        .btn-secondary:hover { background: #6c7a7d; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-sm { padding: 7px 14px; font-size: 13px; }
        .btn-cadastro {
            display: block; width: 100%; max-width: 300px; margin: 0 auto 18px;
            padding: 12px; font-size: 15px; font-weight: 600;
        }
        .search-bar { margin-bottom: 16px; }
        .search-bar input {
            width: 100%; padding: 10px 14px; border: 2px solid #e0e4e8;
            border-radius: 8px; font-size: 15px; outline: none;
            transition: border-color .2s;
        }
        .search-bar input:focus { border-color: #3498db; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #2c3e50; color: white; font-weight: 600;
            padding: 12px 10px; text-align: left; font-size: 13px;
            white-space: nowrap; position: sticky; top: 0;
        }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid #eef1f5; }
        tr:hover td { background: #f8f9fa; }
        .status-ativo { color: #27ae60; font-weight: 600; }
        .status-transferido { color: #f39c12; font-weight: 600; }
        .status-inativo { color: #e74c3c; font-weight: 600; }
        .empty { text-align: center; color: #7f8c8d; font-style: italic; padding: 30px; }

        @media (max-width: 768px) {
            body { padding: 8px; }
            .container { padding: 12px; border-radius: 8px; }
            h1 { font-size: 1.2rem; }
            .top-bar { flex-direction: column; align-items: stretch; text-align: center; }
            .user-info { justify-content: center; flex-wrap: wrap; }
            .stats { grid-template-columns: repeat(2, 1fr); gap: 6px; }
            .stat-card { padding: 10px 6px; }
            .stat-number { font-size: 19px; }
            .btn-cadastro { max-width: 100%; }
            thead { display: none; }
            tr {
                display: block;
                border: 1px solid #e8ecf0;
                border-radius: 10px;
                padding: 14px;
                margin-bottom: 12px;
                background: #fff;
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }
            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 6px 0;
                border: none;
                font-size: 14px;
                gap: 8px;
            }
            td:not(:last-child) { border-bottom: 1px dashed #f0f0f0; }
            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #2c3e50;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                min-width: 80px;
            }
            td:first-child {
                font-size: 16px; font-weight: 700; color: #2c3e50;
                padding: 0 0 8px 0; margin-bottom: 4px;
                flex-direction: column; align-items: flex-start;
                border-bottom: 2px solid #3498db !important;
            }
            td:first-child::before { display: none; }
            td:last-child {
                justify-content: center; gap: 10px; padding-top: 10px;
                flex-wrap: wrap; border-top: 1px solid #eef1f5 !important;
            }
            td:last-child::before { display: none; }
            .btn { padding: 10px 16px; font-size: 14px; }
            .btn-sm { padding: 8px 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
    <h1>Membros da Igreja</h1>
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
        <a href="https://igrejagileadetabapua.com.br" class="btn btn-secondary btn-sm">← Site</a>
        <a href="logout.php" class="btn btn-secondary btn-sm">Sair</a>
        <a href="logs_acesso.php" class="btn btn-secondary btn-sm">Logs</a>
        <a href="gerenciar_usuarios.php" class="btn btn-secondary btn-sm">Usuários</a>
    </div>
</div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($membros); ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#27ae60;"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Ativo')); ?></div>
                <div class="stat-label">Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#f39c12;"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Transferido')); ?></div>
                <div class="stat-label">Transferidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#e74c3c;"><?php echo count(array_filter($membros, fn($m) => $m['situacao'] == 'Inativo')); ?></div>
                <div class="stat-label">Inativos</div>
            </div>
        </div>

        <a href="index.php" class="btn btn-success btn-cadastro">+ Novo Cadastro</a> <a href="exportar.php" class="btn btn-secondary btn-cadastro">📥 Exportar para Excel</a>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Buscar por nome, telefone, ministério..." oninput="filtrarMembros(this.value)">
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Nascimento</th>
                        <th>Sexo</th>
                        <th>Est. Civil</th>
                        <th>Naturalidade</th>
                        <th>Batismo</th>
                        <th>Ministério</th>
                        <th>Função</th>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="membrosTableBody">
                    <?php if (empty($membros)): ?>
                        <tr><td colspan="11" class="empty">Nenhum membro cadastrado ainda.</td></tr>
                    <?php else: ?>
                        <?php foreach ($membros as $membro): ?>
                            <tr>
                                <td data-label="Nome"><?php echo htmlspecialchars($membro['nome']); ?></td>
                                <td data-label="Telefone"><?php echo formatar_telefone($membro['telefone']); ?></td>
                                <td data-label="Nascimento"><?php echo $membro['data_nascimento'] ? formatar_data($membro['data_nascimento']) : '—'; ?></td>
                                <td data-label="Sexo"><?php echo htmlspecialchars($membro['sexo'] ?: '—'); ?></td>
                                <td data-label="Est. Civil"><?php echo htmlspecialchars($membro['estado_civil'] ?: '—'); ?></td>
                                <td data-label="Naturalidade"><?php echo htmlspecialchars($membro['naturalidade'] ?: '—'); ?></td>
                                <td data-label="Batismo"><?php echo $membro['batismo_aguas'] ? formatar_data($membro['batismo_aguas']) : '—'; ?></td>
                                <td data-label="Ministério"><?php echo htmlspecialchars($membro['ministrio'] ?: '—'); ?></td>
                                <td data-label="Função"><?php echo htmlspecialchars($membro['funcao'] ?: '—'); ?></td>
                                <td data-label="Situação">
                                    <span class="status-<?php echo strtolower($membro['situacao']); ?>">
                                        <?php echo htmlspecialchars($membro['situacao']); ?>
                                    </span>
                                </td>
                                <td data-label="Ações">
                                    <a href="editar_membro.php?id=<?php echo $membro['id']; ?>" class="btn btn-secondary btn-sm">Editar</a>
                                    <a href="excluir_membro.php?id=<?php echo $membro['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este membro?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filtrarMembros(valor) {
            const termo = valor.toLowerCase();
            const linhas = document.querySelectorAll('#membrosTableBody tr');
            linhas.forEach(linha => {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(termo) ? '' : 'none';
            });
        }

        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>
</body>
</html>
