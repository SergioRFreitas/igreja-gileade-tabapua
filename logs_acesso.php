<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

// Limpar logs antigos (opcional - manter só últimos 90 dias)
// $pdo->exec("DELETE FROM logs_acesso WHERE data_hora < date('now','-90 days')");

try {
    $stmt = $pdo->query("SELECT * FROM logs_acesso ORDER BY id DESC LIMIT 200");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $logs = [];
}

$total = count($logs);
$sucessos = count(array_filter($logs, fn($l) => $l['resultado'] === 'Sucesso'));
$falhas = count(array_filter($logs, fn($l) => $l['resultado'] === 'Falha'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <title>Log de Acessos - Igreja</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 10px; background-color: #f5f5f5; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; font-size: clamp(1.1rem, 4vw, 1.6rem); }
        .stats { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-card { background: #ecf0f1; padding: 12px; border-radius: 8px; flex: 1; min-width: 100px; text-align: center; }
        .stat-number { font-size: 22px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 12px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .btn { background-color: #3498db; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; display: inline-block; font-size: 14px; border: none; cursor: pointer; }
        .btn-secondary { background-color: #95a5a6; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 500px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
        th { background-color: #2c3e50; color: white; white-space: nowrap; }
        tr:hover { background-color: #f8f9fa; }
        .sucesso { color: #27ae60; font-weight: bold; }
        .falha { color: #e74c3c; font-weight: bold; }
        .dispositivo { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; color: #7f8c8d; }
        .empty { text-align: center; color: #7f8c8d; font-style: italic; padding: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>🔒 Log de Acessos</h1>
        <a href="listar_membros.php" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total de Acessos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#27ae60;"><?php echo $sucessos; ?></div>
            <div class="stat-label">Sucessos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#e74c3c;"><?php echo $falhas; ?></div>
            <div class="stat-label">Falhas</div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>IP</th>
                    <th>Data/Hora</th>
                    <th>Resultado</th>
                    <th>Dispositivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="empty">Nenhum acesso registrado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td><?php echo htmlspecialchars($log['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip']); ?></td>
                        <td><?php echo htmlspecialchars($log['data_hora']); ?></td>
                        <td class="<?php echo strtolower($log['resultado']); ?>">
                            <?php echo $log['resultado'] === 'Sucesso' ? '✅ Sucesso' : '❌ Falha'; ?>
                        </td>
                        <td class="dispositivo" title="<?php echo htmlspecialchars($log['dispositivo']); ?>">
                            <?php echo htmlspecialchars($log['dispositivo']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>

</body>
</html>
